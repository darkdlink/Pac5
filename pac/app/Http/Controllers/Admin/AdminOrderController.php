<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Order\OrderService;
use App\Repositories\OrderRepository;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderRepository;
    protected $orderService;

    /**
     * Create a new controller instance.
     *
     * @param OrderRepository $orderRepository
     * @param OrderService $orderService
     */
    public function __construct(
        OrderRepository $orderRepository,
        OrderService $orderService
    ) {
        $this->middleware('admin.access');
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of the orders.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $status = $request->get('status');
        $dateStart = $request->get('date_start');
        $dateEnd = $request->get('date_end');
        $query = $request->get('query');

        $orders = $this->orderRepository->getAllOrders(
            $status,
            $dateStart,
            $dateEnd,
            $query,
            15
        );

        // Get all possible order statuses
        $statusOptions = [
            'pending' => 'Pendente',
            'processing' => 'Processando',
            'shipped' => 'Enviado',
            'delivered' => 'Entregue',
            'canceled' => 'Cancelado'
        ];

        return view('admin.orders.index', compact(
            'orders',
            'status',
            'dateStart',
            'dateEnd',
            'query',
            'statusOptions'
        ));
    }

    /**
     * Display the specified order.
     *
     * @param Order $order
     * @return \Illuminate\View\View
     */
    public function show(Order $order)
    {
        $order->load(['items.product', 'user', 'payment']);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update the status of an order.
     *
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,canceled',
        ]);

        $previousStatus = $order->status;
        $newStatus = $request->status;

        // Update order status
        $updatedOrder = $this->orderService->updateOrderStatus($order, $newStatus);

        // Check if status change was successful
        if ($updatedOrder) {
            // Generate appropriate message
            if ($previousStatus !== $newStatus) {
                $message = "Status do pedido atualizado de '{$previousStatus}' para '{$newStatus}'.";
            } else {
                $message = "Status do pedido continua como '{$newStatus}'.";
            }
            return redirect()->route('admin.orders.show', $order->id)
                ->with('success', $message);
        }

        return redirect()->route('admin.orders.show', $order->id)
            ->with('error', 'Erro ao atualizar o status do pedido.');
    }

    /**
     * Generate invoice for an order.
     *
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function generateInvoice(Order $order)
    {
        $pdfPath = $this->orderService->generateInvoicePdf($order);

        return response()->download($pdfPath, "fatura-pedido-{$order->id}.pdf");
    }

    /**
     * Export orders to CSV.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $status = $request->get('status');
        $dateStart = $request->get('date_start');
        $dateEnd = $request->get('date_end');

        $csvPath = $this->orderService->exportOrdersToCsv($status, $dateStart, $dateEnd);

        return response()->download($csvPath, 'pedidos.csv');
    }

    /**
     * Send shipping notification to customer.
     *
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendShippingNotification(Order $order)
    {
        if ($order->status !== 'shipped') {
            return redirect()->route('admin.orders.show', $order->id)
                ->with('error', 'O pedido deve estar com status "Enviado" para enviar a notificação.');
        }

        $this->orderService->sendShippingNotification($order);

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', 'Notificação de envio enviada com sucesso.');
    }

    /**
     * Cancel an order.
     *
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Order $order)
    {
        if (in_array($order->status, ['delivered', 'canceled'])) {
            return redirect()->route('admin.orders.show', $order->id)
                ->with('error', 'Não é possível cancelar um pedido que já foi entregue ou cancelado.');
        }

        $result = $this->orderService->cancelOrder($order);

        if ($result) {
            return redirect()->route('admin.orders.show', $order->id)
                ->with('success', 'Pedido cancelado com sucesso. Estoque atualizado.');
        }

        return redirect()->route('admin.orders.show', $order->id)
            ->with('error', 'Erro ao cancelar o pedido.');
    }
}
