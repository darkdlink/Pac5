<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'sku',
        'current_stock',
        'low_stock_threshold',
        'reorder_point',
        'location',
        'last_checked_at',
        'status', // 'in_stock', 'low_stock', 'out_of_stock', 'discontinued'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'current_stock' => 'integer',
        'low_stock_threshold' => 'integer',
        'reorder_point' => 'integer',
        'last_checked_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saving(function ($inventory) {
            // Update status based on current stock
            if ($inventory->current_stock <= 0) {
                $inventory->status = 'out_of_stock';
            } elseif ($inventory->current_stock <= $inventory->low_stock_threshold) {
                $inventory->status = 'low_stock';
            } else {
                $inventory->status = 'in_stock';
            }
        });

        static::updated(function ($inventory) {
            // Create an inventory log entry for stock changes
            if ($inventory->wasChanged('current_stock')) {
                $inventory->logs()->create([
                    'previous_stock' => $inventory->getOriginal('current_stock'),
                    'new_stock' => $inventory->current_stock,
                    'adjustment' => $inventory->current_stock - $inventory->getOriginal('current_stock'),
                    'type' => 'stock_update',
                    'reason' => 'Manual adjustment', // This should be parameterized in real implementation
                    'user_id' => auth()->id(), // Logged-in user who made the change
                ]);

                // Check if we need to alert about low stock
                if (
                    $inventory->status === 'low_stock' &&
                    $inventory->getOriginal('status') !== 'low_stock'
                ) {
                    // Event could be triggered here
                    // event(new LowStockAlert($inventory));
                }
            }
        });
    }

    /**
     * Get the product that owns the inventory.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the inventory logs for this inventory.
     */
    public function logs()
    {
        return $this->hasMany(InventoryLog::class);
    }

    /**
     * Check if inventory is low on stock.
     */
    public function isLowStock()
    {
        return $this->current_stock <= $this->low_stock_threshold;
    }

    /**
     * Check if inventory is out of stock.
     */
    public function isOutOfStock()
    {
        return $this->current_stock <= 0;
    }

    /**
     * Check if inventory needs to be reordered.
     */
    public function needsReorder()
    {
        return $this->current_stock <= $this->reorder_point;
    }

    /**
     * Increase stock by a given quantity.
     */
    public function increaseStock($quantity, $reason = 'Stock addition', $userId = null)
    {
        if ($quantity <= 0) {
            return false;
        }

        $previousStock = $this->current_stock;
        $this->current_stock += $quantity;
        $this->last_checked_at = now();
        $this->save();

        // Create a log entry
        $this->logs()->create([
            'previous_stock' => $previousStock,
            'new_stock' => $this->current_stock,
            'adjustment' => $quantity,
            'type' => 'stock_addition',
            'reason' => $reason,
            'user_id' => $userId ?? auth()->id(),
        ]);

        return true;
    }

    /**
     * Decrease stock by a given quantity.
     */
    public function decreaseStock($quantity, $reason = 'Stock reduction', $userId = null)
    {
        if ($quantity <= 0 || $this->current_stock < $quantity) {
            return false;
        }

        $previousStock = $this->current_stock;
        $this->current_stock -= $quantity;
        $this->last_checked_at = now();
        $this->save();

        // Create a log entry
        $this->logs()->create([
            'previous_stock' => $previousStock,
            'new_stock' => $this->current_stock,
            'adjustment' => -$quantity,
            'type' => 'stock_reduction',
            'reason' => $reason,
            'user_id' => $userId ?? auth()->id(),
        ]);

        return true;
    }

    /**
     * Scope a query to include only in-stock inventory.
     */
    public function scopeInStock($query)
    {
        return $query->where('status', 'in_stock');
    }

    /**
     * Scope a query to include only low-stock inventory.
     */
    public function scopeLowStock($query)
    {
        return $query->where('status', 'low_stock');
    }

    /**
     * Scope a query to include only out-of-stock inventory.
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('status', 'out_of_stock');
    }

    /**
     * Scope a query to include only inventory that needs to be reordered.
     */
    public function scopeNeedsReorder($query)
    {
        return $query->whereRaw('current_stock <= reorder_point');
    }

    /**
     * Get status label for display.
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'in_stock' => 'Em Estoque',
            'low_stock' => 'Estoque Baixo',
            'out_of_stock' => 'Sem Estoque',
            'discontinued' => 'Descontinuado',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get the stock percentage based on original stock level.
     */
    public function getStockPercentageAttribute()
    {
        if ($this->low_stock_threshold <= 0) {
            return 100;
        }

        $maxStock = $this->low_stock_threshold * 4; // Assuming 4x low threshold is "full stock"
        $percentage = ($this->current_stock / $maxStock) * 100;

        return min(100, max(0, round($percentage)));
    }
}
