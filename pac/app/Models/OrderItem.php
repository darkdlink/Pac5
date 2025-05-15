<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'service_id',
        'name',
        'quantity',
        'price',
        'subtotal',
        'options', // JSON field for product/service options (size, color, etc.)
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantity' => 'integer',
        'options' => 'array',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($item) {
            // Auto-calculate subtotal if not provided
            if (empty($item->subtotal)) {
                $item->subtotal = $item->price * $item->quantity;
            }
        });

        static::updating(function ($item) {
            // Recalculate subtotal if price or quantity changed
            if ($item->isDirty('price') || $item->isDirty('quantity')) {
                $item->subtotal = $item->price * $item->quantity;
            }
        });
    }

    /**
     * Get the order that owns the item.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product associated with the item (optional).
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the service associated with the item (optional).
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Check if the item is a product.
     */
    public function isProduct()
    {
        return !is_null($this->product_id);
    }

    /**
     * Check if the item is a service.
     */
    public function isService()
    {
        return !is_null($this->service_id);
    }

    /**
     * Get the item type (product or service).
     */
    public function getTypeAttribute()
    {
        return $this->isProduct() ? 'product' : 'service';
    }

    /**
     * Get related item (product or service).
     */
    public function getRelatedItemAttribute()
    {
        return $this->isProduct() ? $this->product : $this->service;
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        return 'R$ ' . number_format($this->price, 2, ',', '.');
    }

    /**
     * Get formatted subtotal.
     */
    public function getFormattedSubtotalAttribute()
    {
        return 'R$ ' . number_format($this->subtotal, 2, ',', '.');
    }

    /**
     * Update the subtotal based on price and quantity.
     */
    public function updateSubtotal()
    {
        $this->subtotal = $this->price * $this->quantity;
        $this->save();

        // Update order totals
        $this->order->calculateTotals();

        return $this;
    }
}
