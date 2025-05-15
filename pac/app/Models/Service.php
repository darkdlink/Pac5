<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'sale_price',
        'duration', // em minutos
        'category_id',
        'status', // 'active', 'inactive'
        'featured',
        'image',
        'meta_title',
        'meta_description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'duration' => 'integer',
        'featured' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'current_price',
        'discount_percentage',
        'formatted_duration',
        'average_rating',
    ];

    /**
     * Get the category that owns the service.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all reviews for the service.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the order items associated with this service.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the current price of the service (sale price or regular price).
     */
    public function getCurrentPriceAttribute()
    {
        return $this->sale_price && $this->sale_price < $this->price
            ? $this->sale_price
            : $this->price;
    }

    /**
     * Get the discount percentage if the service is on sale.
     */
    public function getDiscountPercentageAttribute()
    {
        if (!$this->sale_price || $this->sale_price >= $this->price) {
            return 0;
        }

        return round((($this->price - $this->sale_price) / $this->price) * 100);
    }

    /**
     * Format the duration into hours and minutes.
     */
    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;

        $result = '';

        if ($hours > 0) {
            $result .= $hours . 'h';
        }

        if ($minutes > 0) {
            $result .= ($hours > 0 ? ' ' : '') . $minutes . 'min';
        }

        return $result;
    }

    /**
     * Get the average rating for this service.
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    /**
     * Scope a query to only include active services.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include featured services.
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope a query to only include services on sale.
     */
    public function scopeOnSale($query)
    {
        return $query->whereNotNull('sale_price')
            ->whereRaw('sale_price < price');
    }

    /**
     * Scope a query to include services in a price range.
     */
    public function scopePriceRange($query, $min, $max)
    {
        return $query->where(function ($query) use ($min, $max) {
            $query->whereBetween('price', [$min, $max])
                ->orWhereBetween('sale_price', [$min, $max]);
        });
    }

    /**
     * Scope a query to include services in a duration range (in minutes).
     */
    public function scopeDurationRange($query, $min, $max)
    {
        return $query->whereBetween('duration', [$min, $max]);
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        return 'R$ ' . number_format($this->price, 2, ',', '.');
    }

    /**
     * Get formatted sale price.
     */
    public function getFormattedSalePriceAttribute()
    {
        return $this->sale_price ? 'R$ ' . number_format($this->sale_price, 2, ',', '.') : null;
    }
}
