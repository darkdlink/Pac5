<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'service_id',
        'rating', // 1-5 stars
        'title',
        'comment',
        'status', // 'pending', 'approved', 'rejected'
        'is_verified_purchase',
        'admin_response',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'integer',
        'is_verified_purchase' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($review) {
            // Set default status if not provided
            if (empty($review->status)) {
                $review->status = 'pending';
            }

            // Verify if this user has purchased this product/service
            if (!isset($review->is_verified_purchase)) {
                $review->is_verified_purchase = $review->verifyPurchase();
            }
        });
    }

    /**
     * Get the user who wrote the review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product being reviewed (optional).
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the service being reviewed (optional).
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Check if the review is about a product.
     */
    public function isProductReview()
    {
        return !is_null($this->product_id);
    }

    /**
     * Check if the review is about a service.
     */
    public function isServiceReview()
    {
        return !is_null($this->service_id);
    }

    /**
     * Get the item type (product or service).
     */
    public function getTypeAttribute()
    {
        return $this->isProductReview() ? 'product' : 'service';
    }

    /**
     * Get related item (product or service).
     */
    public function getRelatedItemAttribute()
    {
        return $this->isProductReview() ? $this->product : $this->service;
    }

    /**
     * Check if the reviewer has actually purchased the product/service.
     */
    protected function verifyPurchase()
    {
        if (!$this->user_id) {
            return false;
        }

        // Check if user has any completed orders containing this product/service
        $query = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.user_id', $this->user_id)
            ->where('orders.status', 'completed');

        if ($this->isProductReview()) {
            $query->where('order_items.product_id', $this->product_id);
        } else {
            $query->where('order_items.service_id', $this->service_id);
        }

        return $query->exists();
    }

    /**
     * Approve the review.
     */
    public function approve()
    {
        $this->status = 'approved';
        $this->save();

        return $this;
    }

    /**
     * Reject the review.
     */
    public function reject($reason = null)
    {
        $this->status = 'rejected';

        if ($reason) {
            $this->admin_response = $reason;
        }

        $this->save();

        return $this;
    }

    /**
     * Add admin response to the review.
     */
    public function addResponse($response)
    {
        $this->admin_response = $response;
        $this->save();

        return $this;
    }

    /**
     * Scope a query to include only approved reviews.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to include only pending reviews.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to include only rejected reviews.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to include only product reviews.
     */
    public function scopeForProducts($query)
    {
        return $query->whereNotNull('product_id');
    }

    /**
     * Scope a query to include only service reviews.
     */
    public function scopeForServices($query)
    {
        return $query->whereNotNull('service_id');
    }

    /**
     * Scope a query to include reviews for a specific product.
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to include reviews for a specific service.
     */
    public function scopeForService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    /**
     * Scope a query to include reviews with a minimum rating.
     */
    public function scopeWithMinRating($query, $rating)
    {
        return $query->where('rating', '>=', $rating);
    }

    /**
     * Scope a query to include only verified purchase reviews.
     */
    public function scopeVerifiedPurchases($query)
    {
        return $query->where('is_verified_purchase', true);
    }
}
