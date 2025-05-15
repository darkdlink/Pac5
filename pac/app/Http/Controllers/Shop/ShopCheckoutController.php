<?php

namespace App\Http\Controllers\Shop;

use App\Events\OrderPlaced;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\Order\OrderService;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
{
    protected $orderService;

    /**
     * Create a new controller instance.
     *
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->middleware('auth');
        $this->orderService = $orderService;
    }

    /**
     * Display the checkout page.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $cart = Session::get('cart', []);

        // Redirect to cart if empty
        if (empty($cart)) {
            return redirect()->route('shop.cart.index')
                ->with('error', 'Seu carrinho está vazio. Adicione produtos antes de prosseguir para o checkout.');
        }

        // Get products and calculate totals
        $productIds = array_keys($cart);
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $items = [];
        $subtotal = 0;
        $outOfStock = false;

        foreach ($cart as $productId => $quantity) {
            if (isset($products[$productId])) {
                $product = $products[$productId];

                // Check if product is still in stock
                if ($product->stock < $quantity) {
                    $outOfStock = true;
                    // Update cart with available quantity
                    $cart[$productId] = $product->stock;
                    Session::put('cart', $cart);

                    if ($product->stock == 0) {
                        return redirect()->route('shop.cart.index')
                            ->with('error', "O produto '{$product->name}' não está mais disponível e foi removido do carrinho.");
                    } else {
                        return redirect()->route('shop.cart.index')
                            ->with('warning', "A quantidade do produto '{$product->name}' foi ajustada para {$product->stock} (estoque disponível).");
                    }
                }

                $itemTotal = $product->price * $quantity;
                $subtotal += $itemTotal;

                $items[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $quantity,
                    'total' => $itemTotal,
                ];
            }
        }

        // Get user's previous addresses if any
        $user = auth()->user();
        $previousAddresses = $user->orders()
            ->select('shipping_address', 'shipping_city', 'shipping_state', 'shipping_zipcode')
            ->distinct()
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        // Get available payment methods
        $paymentMethods = config('payment.available_methods', [
            'credit_card' => 'Cartão de Crédito',
            'pix' => 'PIX',
            'boleto' => 'Boleto Bancário'
        ]);

        return view('shop.checkout.index', compact(
            'items',
            'subtotal',
            'paymentMethods',
            'previousAddresses'
        ));
    }

    /**
     * Process the checkout.
     *
     * @param Request $request
     * @param PaymentGatewayInterface $paymentGateway
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process(Request $request, PaymentGatewayInterface $paymentGateway)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:255',
            'shipping_number' => 'required|string|max:20',
            'shipping_complement' => 'nullable|string|max:255',
            'shipping_neighborhood' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:255',
            'shipping_state' => 'required|string|max:2',
            'shipping_zipcode' => 'required|string|max:9',
            'payment_method' => 'required|in:credit_card,pix,boleto',

            // Credit card fields (conditional)
            'card_number' => 'required_if:payment_method,credit_card',
            'card_holder' => 'required_if:payment_method,credit_card',
            'card_expiry' => 'required_if:payment_method,credit_card',
            'card_cvv' => 'required_if:payment_method,credit_card',
        ]);

        $cart = Session::get('cart', []);

        // Validate cart not empty
        if (empty($cart)) {
            return redirect()->route('shop.cart.index')
                ->with('error', 'Seu carrinho está vazio.');
        }

        // Check stock and get items
        $items = $this->validateCartItems($cart);

        if (!$items) {
            return redirect()->route('shop.cart.index')
                ->with('error', 'Alguns produtos em seu carrinho não estão mais disponíveis.');
        }

        // Create order
        $userId = auth()->id();
        $orderData = $request->only([
            'name', 'email', 'phone',
            'shipping_address', 'shipping_number', 'shipping_complement',
            'shipping_neighborhood', 'shipping_city', 'shipping_state', 'shipping_zipcode'
        ]);

        $order = $this->orderService->createOrder($userId, $items, $orderData);

        if (!$order) {
            return redirect()->route('shop.checkout.index')
                ->with('error', 'Erro ao criar o pedido. Por favor, tente novamente.');
        }

        // Process payment
        $paymentMethod = $request->payment_method;
        $paymentData = $this->getPaymentData($request, $order);

        try {
            $paymentResult = $paymentGateway->processPayment($order, $paymentMethod, $paymentData);

            if ($paymentResult['success']) {
                // Update order with payment info
                $this->orderService->updateOrderPayment(
                    $order,
                    $paymentMethod,
                    $paymentResult['transaction_id'],
                    $paymentResult['status']
                );

                // Clear cart after successful order
                Session::forget('cart');

                // Dispatch order placed event
                event(new OrderPlaced($order));

                return redirect()->route('shop.checkout.success', ['order' => $order->id]);
            } else {
                // Payment failed
                $order->update(['status' => 'payment_failed']);

                return redirect()->route('shop.checkout.index')
                    ->with('error', 'Erro no pagamento: ' . $paymentResult['message']);
            }
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Payment processing error: ' . $e->getMessage());

            // Update order status
            $order->update(['status' => 'payment_error']);

            return redirect()->route('shop.checkout.index')
                ->with('error', 'Ocorreu um erro ao processar seu pagamento. Por favor, tente novamente.');
        }
    }

    /**
     * Display checkout success page.
     *
     * @param Order $order
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function success(Order $order)
    {
        // Security check: only owner can see order
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // Load order details
        $order->load('items.product', 'payment');

        return view('shop.checkout.success', compact('order'));
    }

    /**
     * Validate cart items and check stock.
     *
     * @param array $cart
     * @return array|false
     */
    private function validateCartItems($cart)
    {
        $productIds = array_keys($cart);
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $items = [];
        $valid = true;

        foreach ($cart as $productId => $quantity) {
            // Check if product exists and is active
            if (!isset($products[$productId]) || !$products[$productId]->active) {
                $valid = false;
                break;
            }

            $product = $products[$productId];

            // Check stock
            if ($product->stock < $quantity) {
                $valid = false;
                break;
            }

            $items[] = [
                'product_id' => $product->id,
                'price' => $product->price,
                'quantity' => $quantity,
                'name' => $product->name
            ];
        }

        return $valid ? $items : false;
    }

    /**
     * Get payment data based on payment method.
     *
     * @param Request $request
     * @param Order $order
     * @return array
     */
    private function getPaymentData(Request $request, Order $order)
    {
        $paymentMethod = $request->payment_method;
        $data = [
            'order_id' => $order->id,
            'amount' => $order->total,
            'customer' => [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone
            ]
        ];

        if ($paymentMethod === 'credit_card') {
            $data['card'] = [
                'number' => $request->card_number,
                'holder_name' => $request->card_holder,
                'expiration_date' => $request->card_expiry,
                'cvv' => $request->card_cvv
            ];
        } elseif ($paymentMethod === 'pix') {
            $data['pix'] = [
                'expiration_date' => now()->addHours(24)->format('Y-m-d H:i:s')
            ];
        } elseif ($paymentMethod === 'boleto') {
            $data['boleto'] = [
                'due_date' => now()->addDays(3)->format('Y-m-d'),
                'instructions' => 'Pagar até a data de vencimento'
            ];
        }

        return $data;
    }
}
