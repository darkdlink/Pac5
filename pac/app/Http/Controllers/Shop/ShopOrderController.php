<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $paymentGateway;

    /**
     * Create a new controller instance.
     *
     * @param PaymentGatewayInterface $paymentGateway
     */
    public function __construct(PaymentGatewayInterface $paymentGateway)
    {
        $this->middleware('auth');
        $this->paymentGateway = $paymentGateway;
    }

    /**
     * Display a listing of the user's orders.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('shop.orders.index', compact('orders'));
    }

    /**
     * Display the specified order.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $order = Order::where('user_id', auth()->id())
            ->where('id', $id)
            ->with(['items.product', 'payment'])
            ->firstOrFail();

        return view('shop.orders.show', compact('order'));
    }

    /**
     * Check order payment status.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPaymentStatus($id)
    {
        $order = Order::where('user_id', auth()->id())
            ->where('id', $id)
            ->with('payment')
            ->firstOrFail();

        // Only check status for pending payments
        if (!in_array($order->status, ['pending', 'payment_processing'])) {
            return response()->json([
                'status' => $order->status,
                'payment_status' => $order->payment ? $order->payment->status : null,
                'message' => 'Pedido não está aguardando pagamento'
            ]);
        }

        // Check payment status with gateway
        try {
            $paymentStatus = $this->paymentGateway->checkPaymentStatus($order->payment->transaction_id);

            // Update payment status if changed
            if ($paymentStatus['status'] !== $order->payment->status) {
                $order->payment->update(['status' => $paymentStatus['status']]);

                // Update order status based on payment status
                if ($paymentStatus['status'] === 'approved') {
                    $order->update(['status' => 'processing']);
                } elseif ($paymentStatus['status'] === 'canceled' || $paymentStatus['status'] === 'refused') {
                    $order->update(['status' => 'payment_failed']);
                }
            }

            return response()->json([
                'status' => $order->status,
                'payment_status' => $paymentStatus['status'],
                'payment_details' => $paymentStatus['details'] ?? null,
                'updated' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => $order->status,
                'payment_status' => $order->payment->status,
                'error' => 'Não foi possível verificar o status do pagamento',
                'updated' => false
            ]);
        }
    }

    /**
     * Get payment instructions for the order (boleto/PIX).
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function paymentInstructions($id)
    {
        $order = Order::where('user_id', auth()->id())
            ->where('id', $id)
            ->with('payment')
            ->firstOrFail();

        // Verify payment method
        if (!$order->payment || !in_array($order->payment->method, ['pix', 'boleto'])) {
            return redirect()->route('shop.orders.show', $order->id)
                ->with('error', 'Instruções de pagamento indisponíveis para este método de pagamento.');
        }

        // Get payment instructions from gateway
        try {
            $paymentInfo = $this->paymentGateway->getPaymentInstructions(
                $order->payment->transaction_id,
                $order->payment->method
            );

            return view('shop.orders.payment_instructions', compact('order', 'paymentInfo'));
        } catch (\Exception $e) {
            return redirect()->route('shop.orders.show', $order->id)
                ->with('error', 'Não foi possível obter as instruções de pagamento. Por favor, tente novamente.');
        }
    }

    /**
     * Cancel an order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel($id)
    {
        $order = Order::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        // Check if order can be canceled
        if (!in_array($order->status, ['pending', 'payment_processing', 'payment_failed'])) {
            return redirect()->route('shop.orders.show', $order->id)
                ->with('error', 'Este pedido não pode ser cancelado. Entre em contato com o atendimento.');
        }

        // Cancel payment with gateway if needed
        if ($order->payment && in_array($order->payment->status, ['pending', 'processing'])) {
            try {
                $this->paymentGateway->cancelPayment($order->payment->transaction_id);
                $order->payment->update(['status' => 'canceled']);
            } catch (\Exception $e) {
                // Log error but continue with order cancellation
                \Log::error('Error canceling payment: ' . $e->getMessage());
            }
        }

        // Update order status
        $order->update(['status' => 'canceled']);

        return redirect()->route('shop.orders.show', $order->id)
            ->with('success', 'Pedido cancelado com sucesso.');
    }

    /**
     * Download invoice for an order.
     *
     * @param  int  $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function downloadInvoice($id)
    {
        $order = Order::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        // Check if invoice is available (order must be paid)
        if (!in_array($order->status, ['processing', 'shipped', 'delivered'])) {
            return redirect()->route('shop.orders.show', $order->id)
                ->with('error', 'Fatura indisponível para este pedido.');
        }

        // Generate invoice PDF
        try {
            $invoicePath = storage_path('app/invoices/invoice-' . $order->id . '.pdf');

            if (!file_exists($invoicePath)) {
                // Generate invoice
                $invoiceService = app('App\Services\Order\OrderService');
                $invoicePath = $invoiceService->generateInvoicePdf($order);
            }

            return response()->download($invoicePath, 'fatura-pedido-' . $order->id . '.pdf');
        } catch (\Exception $e) {
            return redirect()->route('shop.orders.show', $order->id)
                ->with('error', 'Não foi possível gerar a fatura. Por favor, tente novamente.');
        }
    }

    /**
     * Get tracking information for a shipped order.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function tracking($id)
    {
        $order = Order::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        // Check if tracking is available
        if (!in_array($order->status, ['shipped', 'delivered']) || !$order->tracking_code) {
            return redirect()->route('shop.orders.show', $order->id)
                ->with('error', 'Informações de rastreamento indisponíveis para este pedido.');
        }

        // Get tracking details
        try {
            // Here you might integrate with a shipping API to get real-time tracking
            // For now, we'll just show a simple tracking page with the code
            return view('shop.orders.tracking', compact('order'));
        } catch (\Exception $e) {
            return redirect()->route('shop.orders.show', $order->id)
                ->with('error', 'Não foi possível obter as informações de rastreamento.');
        }
    }
}
