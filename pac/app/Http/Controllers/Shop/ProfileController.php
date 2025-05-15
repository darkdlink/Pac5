<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected $orderService;

    /**
     * Create a new controller instance.
     *
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Handle payment gateway webhook
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handlePaymentWebhook(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-Signature');

        Log::info('Payment webhook received', ['payload' => $payload]);

        // Verify webhook signature
        if (!$this->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Invalid webhook signature', ['signature' => $signature]);
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
        }

        $eventType = $payload['event'] ?? null;
        $paymentId = $payload['data']['id'] ?? null;
        $status = $payload['data']['status'] ?? null;

        if (!$eventType || !$paymentId || !$status) {
            Log::warning('Missing required webhook data', ['payload' => $payload]);
            return response()->json(['status' => 'error', 'message' => 'Missing required fields'], 400);
        }

        // Find the payment by transaction ID
        $payment = Payment::where('transaction_id', $paymentId)->first();

        if (!$payment) {
            Log::warning('Payment not found for webhook', ['transaction_id' => $paymentId]);
            return response()->json(['status' => 'error', 'message' => 'Payment not found'], 404);
        }

        $order = Order::find($payment->order_id);

        if (!$order) {
            Log::warning('Order not found for payment', ['payment_id' => $payment->id]);
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }

        // Process webhook based on event type
        switch ($eventType) {
            case 'payment.status_updated':
                return $this->handlePaymentStatusUpdate($order, $payment, $status, $payload);

            case 'payment.refunded':
                return $this->handlePaymentRefund($order, $payment, $payload);

            case 'payment.chargeback':
                return $this->handlePaymentChargeback($order, $payment, $payload);

            default:
                Log::info('Unhandled webhook event', ['event' => $eventType]);
                return response()->json(['status' => 'success', 'message' => 'Event acknowledged but not processed']);
        }
    }

    /**
     * Handle payment status update webhook event
     *
     * @param Order $order
     * @param Payment $payment
     * @param string $status
     * @param array $payload
     * @return \Illuminate\Http\Response
     */
    private function handlePaymentStatusUpdate(Order $order, Payment $payment, $status, $payload)
    {
        // Update payment status
        $payment->status = $status;
        $payment->save();

        Log::info('Payment status updated', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'status' => $status
        ]);

        // Update order status based on payment status
        switch ($status) {
            case 'approved':
            case 'paid':
                $this->orderService->updateOrderStatus($order, 'processing');
                break;

            case 'canceled':
            case 'refused':
                $this->orderService->updateOrderStatus($order, 'payment_failed');
                break;

            case 'refunded':
                $this->orderService->updateOrderStatus($order, 'refunded');
                break;

            case 'chargeback':
                $this->orderService->updateOrderStatus($order, 'chargeback');
                break;
        }

        return response()->json(['status' => 'success', 'message' => 'Payment status updated']);
    }

    /**
     * Handle payment refund webhook event
     *
     * @param Order $order
     * @param Payment $payment
     * @param array $payload
     * @return \Illuminate\Http\Response
     */
    private function handlePaymentRefund(Order $order, Payment $payment, $payload)
    {
        $payment->status = 'refunded';
        $payment->save();

        // Update order status
        $this->orderService->updateOrderStatus($order, 'refunded');

        Log::info('Payment refunded', [
            'order_id' => $order->id,
            'payment_id' => $payment->id
        ]);

        return response()->json(['status' => 'success', 'message' => 'Payment refund processed']);
    }

    /**
     * Handle payment chargeback webhook event
     *
     * @param Order $order
     * @param Payment $payment
     * @param array $payload
     * @return \Illuminate\Http\Response
     */
    private function handlePaymentChargeback(Order $order, Payment $payment, $payload)
    {
        $payment->status = 'chargeback';
        $payment->save();

        // Update order status
        $this->orderService->updateOrderStatus($order, 'chargeback');

        Log::info('Payment chargeback', [
            'order_id' => $order->id,
            'payment_id' => $payment->id
        ]);

        return response()->json(['status' => 'success', 'message' => 'Payment chargeback processed']);
    }

    /**
     * Handle Instagram webhook
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handleInstagramWebhook(Request $request)
    {
        $payload = $request->all();

        // Verify Instagram webhook challenge if present
        if ($request->has('hub_mode') && $request->input('hub_mode') === 'subscribe') {
            $challenge = $request->input('hub_challenge');
            $verifyToken = $request->input('hub_verify_token');

            if ($verifyToken === config('instagram.verify_token')) {
                return response($challenge);
            }

            return response()->json(['status' => 'error', 'message' => 'Invalid verify token'], 401);
        }

        Log::info('Instagram webhook received', ['payload' => $payload]);

        // Process the Instagram webhook notification
        // This could update a cache of recent posts or trigger other updates

        try {
            $instagramService = app('App\Services\Instagram\InstagramService');
            $instagramService->refreshPostsCache();

            return response()->json(['status' => 'success', 'message' => 'Instagram webhook processed']);
        } catch (\Exception $e) {
            Log::error('Error processing Instagram webhook', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Error processing webhook'], 500);
        }
    }

    /**
     * Verify webhook signature
     *
     * @param array $payload
     * @param string $signature
     * @return bool
     */
    private function verifyWebhookSignature($payload, $signature)
    {
        // Skip verification in local environment for easier testing
        if (app()->environment('local')) {
            return true;
        }

        if (empty($signature)) {
            return false;
        }

        $secret = config('payment.webhook_secret');

        $payloadJson = json_encode($payload);
        $expectedSignature = hash_hmac('sha256', $payloadJson, $secret);

        return hash_equals($expectedSignature, $signature);
    }
}
