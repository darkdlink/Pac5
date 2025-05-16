<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'sale_price',
        'sku',
        'quantity',
        'is_featured',
        'is_active',
        'category_id',
        'thumbnail',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Cria automaticamente o slug ao criar ou atualizar o nome
    public static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && !$product->isDirty('slug')) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function wishlistItems()
    {
        return $this->hasMany(WishlistItem::class);
    }

    // Escopo para produtos ativos
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Escopo para produtos em destaque
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Retorna o preço atual (considerando preço de venda se existir)
    public function getCurrentPrice()
    {
        return $this->sale_price && $this->sale_price < $this->price
            ? $this->sale_price
            : $this->price;
    }

    // Verifica se o produto está em promoção
    public function isOnSale()
    {
        return $this->sale_price && $this->sale_price < $this->price;
    }

    // Verifica se o produto está em estoque
    public function inStock()
    {
        return $this->quantity > 0;
    }
}
