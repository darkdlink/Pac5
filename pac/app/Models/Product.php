<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
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
        'sku',
        'category_id',
        'stock_quantity',
        'status', // 'active', 'inactive', 'out_of_stock'
        'featured',
        'image',
        'weight',
        'dimensions',
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
        'stock_quantity' => 'integer',
        'featured' => 'boolean',
        'dimensions' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'current_price',
        'discount_percentage',
        'average_rating',
        'is_in_stock',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all reviews for the product.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the inventory for this product.
     */
    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    /**
     * Get the order items associated with this product.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the current price of the product (sale price or regular price).
     */
    public function getCurrentPriceAttribute()
    {
        return $this->sale_price && $this->sale_price < $this->price
            ? $this->sale_price
            : $this->price;
    }

    /**
     * Get the discount percentage if the product is on sale.
     */
    public function getDiscountPercentageAttribute()
    {
        if (!$this->sale_price || $this->sale_price >= $this->price) {
            return 0;
        }

        return round((($this->price - $this->sale_price) / $this->price) * 100);
    }

    /**
     * Get the average rating for this product.
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    /**
     * Check if product is in stock.
     */
    public function getIsInStockAttribute()
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include featured products.
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope a query to only include products on sale.
     */
    public function scopeOnSale($query)
    {
        return $query->whereNotNull('sale_price')
            ->whereRaw('sale_price < price');
    }

    /**
     * Scope a query to only include products in stock.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Scope a query to include products in a price range.
     */
    public function scopePriceRange($query, $min, $max)
    {
        return $query->where(function ($query) use ($min, $max) {
            $query->whereBetween('price', [$min, $max])
                ->orWhereBetween('sale_price', [$min, $max]);
        });
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

    /**
     * Decrease stock quantity.
     */
    public function decreaseStock($quantity = 1)
    {
        if ($this->stock_quantity >= $quantity) {
            $this->stock_quantity -= $quantity;
            $this->save();

            // Update inventory record
            if ($this->inventory) {
                $this->inventory->update([
                    'current_stock' => $this->stock_quantity
                ]);
            }

            // Dispatch event
            if ($this->stock_quantity <= 5) {
                event(new \App\Events\StockUpdated($this));
            }

            return true;
        }

        return false;
    }

    /**
     * Increase stock quantity.
     */
    public function increaseStock($quantity = 1)
    {
        $this->stock_quantity += $quantity;
        $this->save();

        // Update inventory record
        if ($this->inventory) {
            $this->inventory->update([
                'current_stock' => $this->stock_quantity
            ]);
        }

        return true;
    }
}
