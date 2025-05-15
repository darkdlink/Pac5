<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'image',
        'status', // 'active', 'inactive'
        'display_order',
        'type', // 'product', 'service', 'both'
        'meta_title',
        'meta_description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'display_order' => 'integer',
        'parent_id' => 'integer',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = \Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = \Str::slug($category->name);
            }
        });
    }

    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get all products in this category.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all services in this category.
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Check if category has children.
     */
    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }

    /**
     * Get all product categories.
     */
    public function scopeProductCategories($query)
    {
        return $query->where('type', 'product')
            ->orWhere('type', 'both');
    }

    /**
     * Get all service categories.
     */
    public function scopeServiceCategories($query)
    {
        return $query->where('type', 'service')
            ->orWhere('type', 'both');
    }

    /**
     * Get root categories (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    /**
     * Get all active products in this category and subcategories.
     */
    public function getAllProducts()
    {
        $ids = $this->getChildrenIds();
        $ids[] = $this->id;

        return Product::whereIn('category_id', $ids)
            ->active()
            ->get();
    }

    /**
     * Get all active services in this category and subcategories.
     */
    public function getAllServices()
    {
        $ids = $this->getChildrenIds();
        $ids[] = $this->id;

        return Service::whereIn('category_id', $ids)
            ->active()
            ->get();
    }

    /**
     * Get all children category IDs (recursive).
     */
    protected function getChildrenIds($ids = [])
    {
        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = $child->getChildrenIds($ids);
        }

        return $ids;
    }

    /**
     * Get the full category path (breadcrumb).
     */
    public function getPathAttribute()
    {
        $path = [$this->name];
        $category = $this;

        while ($category->parent) {
            $category = $category->parent;
            array_unshift($path, $category->name);
        }

        return implode(' > ', $path);
    }
}
