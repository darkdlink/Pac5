<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Cria automaticamente o slug ao criar ou atualizar o nome
    public static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && !$category->isDirty('slug')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    // Escopo para categorias ativas
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Escopo para categorias principais (sem parent)
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    // Verificar se a categoria tem filhos
    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }

    // Verificar se a categoria tem produtos
    public function hasProducts()
    {
        return $this->products()->count() > 0;
    }

    // Obter todas as subcategorias recursivamente
    public function getAllChildren()
    {
        $children = collect([$this]);

        foreach ($this->children as $child) {
            $children = $children->merge($child->getAllChildren());
        }

        return $children;
    }
}
