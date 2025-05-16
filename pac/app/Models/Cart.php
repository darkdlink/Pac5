<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    // Calcular subtotal do carrinho
    public function getSubtotalAttribute()
    {
        return $this->items->sum(function ($item) {
            if ($item->product) {
                return $item->product->getCurrentPrice() * $item->quantity;
            } elseif ($item->service) {
                return $item->service->price * $item->quantity;
            }
            return 0;
        });
    }

    // Número total de itens no carrinho
    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    // Adicionar produto ao carrinho
    public function addProduct($productId, $quantity = 1)
    {
        $product = Product::findOrFail($productId);

        // Verificar se o produto já está no carrinho
        $cartItem = $this->items()
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            // Atualizar quantidade se o produto já estiver no carrinho
            $cartItem->update([
                'quantity' => $cartItem->quantity + $quantity
            ]);
        } else {
            // Adicionar novo item ao carrinho
            $this->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity
            ]);
        }

        return $this;
    }

    // Adicionar serviço ao carrinho
    public function addService($serviceId, $quantity = 1)
    {
        $service = Service::findOrFail($serviceId);

        $cartItem = $this->items()
            ->where('service_id', $serviceId)
            ->first();

        if ($cartItem) {
            $cartItem->update([
                'quantity' => $cartItem->quantity + $quantity
            ]);
        } else {
            $this->items()->create([
                'service_id' => $serviceId,
                'quantity' => $quantity
            ]);
        }

        return $this;
    }

    // Remover item do carrinho
    public function removeItem($cartItemId)
    {
        $this->items()->where('id', $cartItemId)->delete();
        return $this;
    }

    // Atualizar quantidade de um item
    public function updateItemQuantity($cartItemId, $quantity)
    {
        if ($quantity <= 0) {
            return $this->removeItem($cartItemId);
        }

        $this->items()->where('id', $cartItemId)->update(['quantity' => $quantity]);
        return $this;
    }

    // Limpar o carrinho
    public function clear()
    {
        $this->items()->delete();
        return $this;
    }
}
