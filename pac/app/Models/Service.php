<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'duration',
        'is_featured',
        'is_active',
        'category_id',
        'thumbnail',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Cria automaticamente o slug ao criar ou atualizar o nome
    public static function boot()
    {
        parent::boot();

        static::creating(function ($service) {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->name);
            }
        });

        static::updating(function ($service) {
            if ($service->isDirty('name') && !$service->isDirty('slug')) {
                $service->slug = Str::slug($service->name);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ServiceImage::class);
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

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function wishlistItems()
    {
        return $this->hasMany(WishlistItem::class);
    }

    // Escopo para serviços ativos
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Escopo para serviços em destaque
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Formatar a duração do serviço
    public function getDurationFormatted()
    {
        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;

        $formatted = '';

        if ($hours > 0) {
            $formatted .= $hours . 'h';
        }

        if ($minutes > 0) {
            $formatted .= ($hours > 0 ? ' ' : '') . $minutes . 'min';
        }

        return $formatted;
    }
}
