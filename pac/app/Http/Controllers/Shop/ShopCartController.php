<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Display the shopping cart.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $cart = $this->getCart();

        // Calculate cart totals
        $subtotal = 0;
        $items = [];

        if (count($cart) > 0) {
            // Get all products in cart
            $productIds = array_keys($cart);
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            // Build cart items with product details
            foreach ($cart as $productId => $quantity) {
                if (isset($products[$productId])) {
                    $product = $products[$productId];

                    // Check stock availability
                    $availableQuantity = min($quantity, $product->stock);

                    // Update cart if requested quantity exceeds stock
                    if ($availableQuantity < $quantity) {
                        $this->updateCart($productId, $availableQuantity);
                        $quantity = $availableQuantity;
                    }

                    $itemTotal = $product->price * $quantity;
                    $subtotal += $itemTotal;

                    $items[] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => $product->price,
                        'quantity' => $quantity,
                        'image' => $product->image,
                        'total' => $itemTotal,
                        'stock' => $product->stock,
                        'url' => route('shop.products.show', $product->id)
                    ];
                }
            }
        }

        return view('shop.cart.index', compact('items', 'subtotal'));
    }

    /**
     * Add a product to the cart.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $productId = $request->product_id;
        $quantity = $request->quantity;

        // Get the product
        $product = Product::findOrFail($productId);

        // Check if product is active
        if (!$product->active) {
            return response()->json([
                'success' => false,
                'message' => 'Este produto não está disponível.'
            ], 422);
        }

        // Check stock availability
        if ($product->stock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Quantidade solicitada indisponível. Estoque atual: ' . $product->stock
            ], 422);
        }

        // Add to cart
        $cart = $this->getCart();

        // If product already in cart, update quantity
        if (isset($cart[$productId])) {
            $newQty = $cart[$productId] + $quantity;

            // Check if new quantity exceeds stock
            if ($newQty > $product->stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quantidade excede o estoque disponível. Estoque: ' . $product->stock
                ], 422);
            }

            $cart[$productId] = $newQty;
        } else {
            $cart[$productId] = $quantity;
        }

        // Save updated cart
        Session::put('cart', $cart);

        // Get cart count
        $cartCount = $this->getCartCount();

        return response()->json([
            'success' => true,
            'message' => 'Produto adicionado ao carrinho.',
            'cart_count' => $cartCount
        ]);
    }

    /**
     * Update cart item quantity.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $productId = $request->product_id;
        $quantity = $request->quantity;

        // Get the product
        $product = Product::findOrFail($productId);

        // Check stock availability
        if ($product->stock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Quantidade solicitada indisponível. Estoque atual: ' . $product->stock
            ], 422);
        }

        // Update cart
        $this->updateCart($productId, $quantity);

        // Recalculate cart totals
        $cart = $this->getCart();
        $subtotal = 0;

        foreach ($cart as $pid => $qty) {
            $p = Product::find($pid);
            if ($p) {
                $subtotal += $p->price * $qty;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Carrinho atualizado.',
            'item_total' => $product->price * $quantity,
            'subtotal' => $subtotal,
            'cart_count' => $this->getCartCount()
        ]);
    }

    /**
     * Remove an item from the cart.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $productId = $request->product_id;

        // Get cart
        $cart = $this->getCart();

        // Remove item
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            Session::put('cart', $cart);
        }

        // Recalculate subtotal
        $subtotal = 0;
        foreach ($cart as $pid => $qty) {
            $p = Product::find($pid);
            if ($p) {
                $subtotal += $p->price * $qty;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Item removido do carrinho.',
            'subtotal' => $subtotal,
            'cart_count' => $this->getCartCount()
        ]);
    }

    /**
     * Clear the cart.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clear()
    {
        Session::forget('cart');

        return redirect()->route('shop.cart.index')
            ->with('success', 'Carrinho esvaziado com sucesso.');
    }

    /**
     * Get cart from session.
     *
     * @return array
     */
    private function getCart()
    {
        return Session::get('cart', []);
    }

    /**
     * Update cart with new quantity.
     *
     * @param int $productId
     * @param int $quantity
     * @return void
     */
    private function updateCart($productId, $quantity)
    {
        $cart = $this->getCart();
        $cart[$productId] = $quantity;
        Session::put('cart', $cart);
    }

    /**
     * Get total number of items in cart.
     *
     * @return int
     */
    private function getCartCount()
    {
        $cart = $this->getCart();
        $count = 0;

        foreach ($cart as $quantity) {
            $count += $quantity;
        }

        return $count;
    }
}
