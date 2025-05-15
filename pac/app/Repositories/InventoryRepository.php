<?php

namespace App\Repositories;

use App\Models\Inventory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class InventoryRepository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    public function model(): string
    {
        return Inventory::class;
    }

    /**
     * Get inventory with product details
     *
     * @return Collection
     */
    public function getWithProducts(): Collection
    {
        return $this->model->with('product')->get();
    }

    /**
     * Get inventory item with product details
     *
     * @param int $id
     * @return Inventory
     */
    public function findWithProduct(int $id)
    {
        return $this->model->with('product')->findOrFail($id);
    }

    /**
     * Get inventory by product ID
     *
     * @param int $productId
     * @return Inventory|null
     */
    public function getByProduct(int $productId)
    {
        return $this->model->where('product_id', $productId)->first();
    }

    /**
     * Get low stock items
     *
     * @param int $threshold
     * @return Collection
     */
    public function getLowStock(int $threshold = 5): Collection
    {
        return $this->model->with('product')
                          ->where('quantity', '>', 0)
                          ->where('quantity', '<=', $threshold)
                          ->get();
    }

    /**
     * Get out of stock items
     *
     * @return Collection
     */
    public function getOutOfStock(): Collection
    {
        return $this->model->with('product')
                          ->where('quantity', 0)
                          ->get();
    }

    /**
     * Update inventory quantity
     *
     * @param int $productId
     * @param int $quantity
     * @param string $action
     * @return Inventory
     */
    public function updateQuantity(int $productId, int $quantity, string $action = 'set'): Inventory
    {
        $inventory = $this->model->where('product_id', $productId)->first();

        if (!$inventory) {
            // Create new inventory record if it doesn't exist
            $inventory = $this->create([
                'product_id' => $productId,
                'quantity' => 0,
                'last_updated' => Carbon::now()
            ]);
        }

        switch ($action) {
            case 'add':
                $inventory->quantity += $quantity;
                break;
            case 'subtract':
                $inventory->quantity = max(0, $inventory->quantity - $quantity);
                break;
            case 'set':
            default:
                $inventory->quantity = max(0, $quantity);
                break;
        }

        $inventory->last_updated = Carbon::now();
        $inventory->save();

        // Log inventory change
        $this->logInventoryChange($inventory->id, $productId, $quantity, $action);

        return $inventory;
    }

    /**
     * Log inventory change
     *
     * @param int $inventoryId
     * @param int $productId
     * @param int $quantity
     * @param string $action
     * @param string|null $notes
     * @return void
     */
    protected function logInventoryChange(int $inventoryId, int $productId, int $quantity, string $action, ?string $notes = null): void
    {
        DB::table('inventory_logs')->insert([
            'inventory_id' => $inventoryId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'action' => $action,
            'notes' => $notes,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }

    /**
     * Get inventory logs
     *
     * @param int|null $productId
     * @param int $limit
     * @return array
     */
    public function getInventoryLogs(?int $productId = null, int $limit = 50): array
    {
        $query = DB::table('inventory_logs')
                   ->select(
                       'inventory_logs.*',
                       'products.name as product_name',
                       'products.sku as product_sku'
                   )
                   ->join('products', 'inventory_logs.product_id', '=', 'products.id')
                   ->orderBy('inventory_logs.created_at', 'desc');

        if ($productId) {
            $query->where('inventory_logs.product_id', $productId);
        }

        return $query->limit($limit)->get()->toArray();
    }

    /**
     * Get inventory summary
     *
     * @return array
     */
    public function getInventorySummary(): array
    {
        $totalProducts = $this->model->count();
        $totalQuantity = $this->model->sum('quantity');
        $lowStockCount = $this->model->where('quantity', '>', 0)
                                    ->where('quantity', '<=', 5)
                                    ->count();
        $outOfStockCount = $this->model->where('quantity', 0)->count();

        return [
            'total_products' => $totalProducts,
            'total_quantity' => $totalQuantity,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount
        ];
    }

    /**
     * Check if product has sufficient stock
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function hasSufficientStock(int $productId, int $quantity): bool
    {
        $inventory = $this->getByProduct($productId);

        if (!$inventory) {
            return false;
        }

        return $inventory->quantity >= $quantity;
    }

    /**
     * Reserve stock for an order
     *
     * @param int $productId
     * @param int $quantity
     * @param int $orderId
     * @return bool
     */
    public function reserveStock(int $productId, int $quantity, int $orderId): bool
    {
        if (!$this->hasSufficientStock($productId, $quantity)) {
            return false;
        }

        $inventory = $this->updateQuantity($productId, $quantity, 'subtract');

        // Log the reservation
        $this->logInventoryChange(
            $inventory->id,
            $productId,
            $quantity,
            'reserve',
            "Reserved for Order #{$orderId}"
        );

        return true;
    }

    /**
     * Return stock to inventory (e.g., for canceled orders)
     *
     * @param int $productId
     * @param int $quantity
     * @param int $orderId
     * @return Inventory
     */
    public function returnStock(int $productId, int $quantity, int $orderId): Inventory
    {
        $inventory = $this->updateQuantity($productId, $quantity, 'add');

        // Log the return
        $this->logInventoryChange(
            $inventory->id,
            $productId,
            $quantity,
            'return',
            "Returned from Order #{$orderId}"
        );

        return $inventory;
    }

    /**
     * Add stock from supplier
     *
     * @param int $productId
     * @param int $quantity
     * @param string|null $supplierInfo
     * @return Inventory
     */
    public function addStockFromSupplier(int $productId, int $quantity, ?string $supplierInfo = null): Inventory
    {
        $inventory = $this->updateQuantity($productId, $quantity, 'add');

        // Log the resupply
        $this->logInventoryChange(
            $inventory->id,
            $productId,
            $quantity,
            'resupply',
            $supplierInfo ? "Received from: {$supplierInfo}" : "Stock resupply"
        );

        return $inventory;
    }
}
