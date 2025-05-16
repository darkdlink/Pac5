<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'service_id',
        'quantity',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Calcular o subtotal deste item
    public function getSubtotalAttribute()
    {
        if ($this->product) {
            return $this->product->getCurrentPrice() * $this->quantity;
        } elseif ($this->service) {
            return $this->service->price * $this->quantity;
        }

        return 0;
    }

    // Nome do item (produto ou serviço)
    public function getNameAttribute()
    {
        if ($this->product) {
            return $this->product->name;
        } elseif ($this->service) {
            return $this->service->name;
        }

        return 'Item desconhecido';
    }

    // Preço unitário do item
    public function getUnitPriceAttribute()
    {
        if ($this->product) {
            return $this->product->getCurrentPrice();
        } elseif ($this->service) {
            return $this->service->price;
        }

        return 0;
    }
}
