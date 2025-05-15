<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'inventory_id',
        'user_id', // User who made the change
        'previous_stock',
        'new_stock',
        'adjustment', // Positive or negative value
        'type', // 'stock_addition', 'stock_reduction', 'stock_update', 'stock_audit'
        'reason', // Why the stock changed
        'reference_id', // For example, order_id if stock was decreased due to an order
        'reference_type', // For example, 'order', 'purchase', 'return', 'adjustment'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'previous_stock' => 'integer',
        'new_stock' => 'integer',
        'adjustment' => 'integer',
    ];

    /**
     * Get the inventory that owns the log.
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    /**
     * Get the user who made the inventory change.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the referenced model (polymorphic).
     */
    public function reference()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to include only logs of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to include logs created within a date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to include logs for a specific product.
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->whereHas('inventory', function ($query) use ($productId) {
            $query->where('product_id', $productId);
        });
    }

    /**
     * Scope a query to include logs with positive adjustments (additions).
     */
    public function scopeAdditions($query)
    {
        return $query->where('adjustment', '>', 0);
    }

    /**
     * Scope a query to include logs with negative adjustments (reductions).
     */
    public function scopeReductions($query)
    {
        return $query->where('adjustment', '<', 0);
    }

    /**
     * Scope a query to include logs by a specific user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get the formatted adjustment with sign.
     */
    public function getFormattedAdjustmentAttribute()
    {
        $sign = $this->adjustment > 0 ? '+' : '';
        return $sign . $this->adjustment;
    }

    /**
     * Get type label for display.
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'stock_addition' => 'Adição de Estoque',
            'stock_reduction' => 'Redução de Estoque',
            'stock_update' => 'Atualização de Estoque',
            'stock_audit' => 'Auditoria de Estoque',
        ];

        return $labels[$this->type] ?? $this->type;
    }
}
