<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'order_number',
        'status', // 'pending', 'processing', 'completed', 'cancelled', 'refunded'
        'total_amount',
        'discount_amount',
        'shipping_amount',
        'tax_amount',
        'grand_total',
        'payment_method', // 'credit_card', 'pix', 'boleto'
        'payment_status', // 'pending', 'paid', 'failed'
        'shipping_name',
        'shipping_email',
        'shipping_phone',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_zip_code',
        'shipping_method',
        'shipping_tracking_number',
        'notes',
        'completed_at',
        'cancelled_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($order) {
            // Generate a unique order number if not provided
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
            }
        });
    }

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for the order.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the payment associated with the order.
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Check if order has products.
     */
    public function hasProducts()
    {
        return $this->items()->whereNotNull('product_id')->exists();
    }

    /**
     * Check if order has services.
     */
    public function hasServices()
    {
        return $this->items()->whereNotNull('service_id')->exists();
    }

    /**
     * Check if payment is complete.
     */
    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if order is complete.
     */
    public function isComplete()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if order is cancelled.
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Scope a query to include orders with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to include orders with a specific payment status.
     */
    public function scopeWithPaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope a query to include orders created within a date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to include orders from a specific user.
     */
    public function scopeFromUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get total number of items.
     */
    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    /**
     * Get formatted total amount.
     */
    public function getFormattedTotalAttribute()
    {
        return 'R$ ' . number_format($this->total_amount, 2, ',', '.');
    }

    /**
     * Get formatted grand total.
     */
    public function getFormattedGrandTotalAttribute()
    {
        return 'R$ ' . number_format($this->grand_total, 2, ',', '.');
    }

    /**
     * Complete the order.
     */
    public function complete()
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();

        // Event could be triggered here
        // event(new OrderCompleted($this));

        return $this;
    }

    /**
     * Cancel the order.
     */
    public function cancel($reason = null)
    {
        $this->status = 'cancelled';
        $this->cancelled_at = now();

        if ($reason) {
            $this->notes = $reason;
        }

        $this->save();

        // Restore stock for each product in the order
        foreach ($this->items as $item) {
            if ($item->product_id) {
                $item->product->increaseStock($item->quantity);
            }
        }

        // Event could be triggered here
        // event(new OrderCancelled($this));

        return $this;
    }

    /**
     * Mark order as paid.
     */
    public function markAsPaid()
    {
        $this->payment_status = 'paid';

        if ($this->status === 'pending') {
            $this->status = 'processing';
        }

        $this->save();

        // Event could be triggered here
        // event(new PaymentReceived($this));

        return $this;
    }

    /**
     * Calculate order totals.
     */
    public function calculateTotals()
    {
        // Calculate total from items
        $this->total_amount = $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // Apply tax, shipping, etc.
        $this->grand_total = $this->total_amount + $this->shipping_amount + $this->tax_amount - $this->discount_amount;

        $this->save();

        return $this;
    }
}
