<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;

class CartCounter extends Component
{
    public $count = 0;

    protected $listeners = ['cartUpdated' => '$refresh'];

    public function mount()
    {
        $this->updateCartCount();
    }

    public function updateCartCount()
    {
        $this->count = 0;

        if (Auth::check()) { // Use a Facade Auth
            $user = Auth::user(); // Use a Facade Auth
            $cart = $user->cart;

            if ($cart) {
                $this->count = $cart->items->count();
            }
        } else {
            // Para carrinho de convidados
            if (session()->has('cart')) {
                $cartItems = session('cart');
                $this->count = count($cartItems);
            }
        }
    }

    public function render()
    {
        return view('livewire.cart-counter');
    }
}
