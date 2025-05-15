<?php

namespace App\Services\Inventory;

use App\Models\Product;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Serviço para gestão de estoque de produtos
 */
class InventoryService
{
    /**
     * Atualiza o estoque de um produto
     *
     * @param Product $product Produto a ser atualizado
     * @param int $quantity Nova quantidade em estoque
     * @param string $reason Razão da atualização
     * @param int|null $orderId ID do pedido (se aplicável)
     * @return bool
     */
    public function updateStock(Product $product, int $quantity, string $reason, ?int $orderId = null): bool
    {
        try {
            // Inicia uma transação para garantir consistência
            DB::beginTransaction();

            // Calcula a diferença em relação ao estoque atual
            $diff = $quantity - $product->stock;

            // Registra o movimento no histórico
            $inventory = new Inventory();
            $inventory->product_id = $product->id;
            $inventory->previous_quantity = $product->stock;
            $inventory->new_quantity = $quantity;
            $inventory->adjustment = $diff;
            $inventory->reason = $reason;
            $inventory->order_id = $orderId;
            $inventory->save();

            // Atualiza o estoque do produto
            $product->stock = $quantity;
            $product->save();

            // Verifica se o produto está com estoque baixo
            if ($product->stock <= $product->min_stock && $product->stock > 0) {
                // Dispara evento de estoque baixo
                event(new \App\Events\StockUpdated($product, 'low'));
            }
            // Verifica se o produto está sem estoque
            else if ($product->stock <= 0) {
                // Dispara evento de produto esgotado
                event(new \App\Events\StockUpdated($product, 'out'));
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Erro ao atualizar estoque', [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'reason' => $reason,
                'exception' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Adiciona uma quantidade ao estoque atual
     *
     * @param Product $product Produto a ter estoque aumentado
     * @param int $quantity Quantidade a adicionar
     * @param string $reason Razão do ajuste
     * @return bool
     */
    public function increaseStock(Product $product, int $quantity, string $reason): bool
    {
        $newQuantity = $product->stock + $quantity;
        return $this->updateStock($product, $newQuantity, $reason);
    }

    /**
     * Reduz uma quantidade do estoque atual
     *
     * @param Product $product Produto a ter estoque reduzido
     * @param int $quantity Quantidade a reduzir
     * @param string $reason Razão do ajuste
     * @param int|null $orderId ID do pedido (se aplicável)
     * @return bool
     */
    public function decreaseStock(Product $product, int $quantity, string $reason, ?int $orderId = null): bool
    {
        $newQuantity = $product->stock - $quantity;

        // Caso o estoque fique negativo, ajustamos para zero
        if ($newQuantity < 0) {
            Log::warning('Tentativa de reduzir estoque abaixo de zero', [
                'product_id' => $product->id,
                'current_stock' => $product->stock,
                'quantity_to_decrease' => $quantity
            ]);
            $newQuantity = 0;
        }

        return $this->updateStock($product, $newQuantity, $reason, $orderId);
    }

    /**
     * Reserva o estoque para um pedido (redução temporária)
     *
     * @param Order $order Pedido para o qual reservar estoque
     * @return bool
     */
    public function reserveStockForOrder(Order $order): bool
    {
        try {
            DB::beginTransaction();

            $allItemsAvailable = true;

            foreach ($order->items as $item) {
                $product = $item->product;

                // Verifica se há estoque suficiente
                if ($product->stock < $item->quantity) {
                    $allItemsAvailable = false;
                    break;
                }

                // Reduz o estoque temporariamente
                $this->decreaseStock(
                    $product,
                    $item->quantity,
                    "Reserva para pedido #{$order->id}",
                    $order->id
                );
            }

            if (!$allItemsAvailable) {
                DB::rollBack();
                return false;
            }

            // Atualiza o status de reserva do pedido
            $order->stock_reserved = true;
            $order->save();

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Erro ao reservar estoque para pedido', [
                'order_id' => $order->id,
                'exception' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Libera o estoque reservado para um pedido (quando cancelado)
     *
     * @param Order $order Pedido para o qual liberar estoque
     * @return bool
     */
    public function releaseStockFromOrder(Order $order): bool
    {
        try {
            DB::beginTransaction();

            // Só libera se o estoque foi reservado anteriormente
            if ($order->stock_reserved) {
                foreach ($order->items as $item) {
                    $product = $item->product;

                    // Aumenta o estoque novamente
                    $this->increaseStock(
                        $product,
                        $item->quantity,
                        "Liberação de estoque do pedido #{$order->id} cancelado"
                    );
                }

                // Atualiza o status de reserva do pedido
                $order->stock_reserved = false;
                $order->save();
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Erro ao liberar estoque reservado do pedido', [
                'order_id' => $order->id,
                'exception' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Confirma a redução de estoque para um pedido pago
     * (converte a reserva temporária em permanente)
     *
     * @param Order $order Pedido confirmado
     * @return bool
     */
    public function confirmOrderStock(Order $order): bool
    {
        try {
            DB::beginTransaction();

            // Registra a operação no histórico como confirmação
            foreach ($order->items as $item) {
                $product = $item->product;

                // Registra a movimentação como confirmada (sem alterar o estoque)
                $inventory = new Inventory();
                $inventory->product_id = $product->id;
                $inventory->previous_quantity = $product->stock;
                $inventory->new_quantity = $product->stock; // Mesmo valor, já que o estoque já foi reservado
                $inventory->adjustment = 0; // Não há ajuste real, apenas uma mudança de status
                $inventory->reason = "Confirmação de venda para pedido #{$order->id}";
                $inventory->order_id = $order->id;
                $inventory->save();
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Erro ao confirmar estoque do pedido', [
                'order_id' => $order->id,
                'exception' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Verifica produtos com estoque baixo
     *
     * @param int $threshold Limite opcional (se não informado, usa o min_stock de cada produto)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowStockProducts(?int $threshold = null)
    {
        if ($threshold !== null) {
            return Product::where('stock', '<=', $threshold)
                ->where('stock', '>', 0)
                ->orderBy('stock', 'asc')
                ->get();
        } else {
            return Product::whereRaw('stock <= min_stock')
                ->where('stock', '>', 0)
                ->orderBy('stock', 'asc')
                ->get();
        }
    }

    /**
     * Obtém produtos sem estoque
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOutOfStockProducts()
    {
        return Product::where('stock', '<=', 0)
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Obtém o histórico de movimentações de um produto
     *
     * @param Product $product Produto
     * @param int $limit Limite de registros
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProductHistory(Product $product, int $limit = 50)
    {
        return Inventory::where('product_id', $product->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Verifica se um produto tem estoque suficiente
     *
     * @param Product $product Produto a verificar
     * @param int $quantity Quantidade necessária
     * @return bool
     */
    public function hasEnoughStock(Product $product, int $quantity): bool
    {
        return $product->stock >= $quantity;
    }

    /**
     * Verifica a disponibilidade de estoque de vários produtos
     *
     * @param array $items Array de [product_id => quantity]
     * @return array [product_id => boolean]
     */
    public function checkBulkAvailability(array $items): array
    {
        $result = [];

        foreach ($items as $productId => $quantity) {
            $product = Product::find($productId);

            if ($product) {
                $result[$productId] = $this->hasEnoughStock($product, $quantity);
            } else {
                $result[$productId] = false;
            }
        }

        return $result;
    }
}
