<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin.access');
    }

    /**
     * Display a listing of customers.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = $request->get('query');

        $customers = User::where('role', 'customer')
            ->when($query, function ($q) use ($query) {
                return $q->where(function ($search) use ($query) {
                    $search->where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('phone', 'like', "%{$query}%");
                });
            })
            ->withCount('orders')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.customers.index', compact('customers', 'query'));
    }

    /**
     * Display the specified customer.
     *
     * @param User $customer
     * @return \Illuminate\View\View
     */
    public function show(User $customer)
    {
        // Make sure this is a customer
        if ($customer->role !== 'customer') {
            abort(404);
        }

        // Get customer's orders
        $orders = Order::where('user_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Get customer's reviews
        $reviews = Review::where('user_id', $customer->id)
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Calculate customer metrics
        $totalSpent = Order::where('user_id', $customer->id)
            ->whereIn('status', ['processing', 'shipped', 'delivered'])
            ->sum('total');

        $orderCount = Order::where('user_id', $customer->id)->count();
        $avgOrderValue = $orderCount > 0 ? $totalSpent / $orderCount : 0;

        return view('admin.customers.show', compact(
            'customer',
            'orders',
            'reviews',
            'totalSpent',
            'orderCount',
            'avgOrderValue'
        ));
    }

    /**
     * Show the form for editing the specified customer.
     *
     * @param User $customer
     * @return \Illuminate\View\View
     */
    public function edit(User $customer)
    {
        // Make sure this is a customer
        if ($customer->role !== 'customer') {
            abort(404);
        }

        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     *
     * @param Request $request
     * @param User $customer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $customer)
    {
        // Make sure this is a customer
        if ($customer->role !== 'customer') {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $customer->id,
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'zipcode' => 'nullable|string|max:9',
            'active' => 'boolean',
        ]);

        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->phone = $request->phone;
        $customer->address = $request->address;
        $customer->city = $request->city;
        $customer->state = $request->state;
        $customer->zipcode = $request->zipcode;
        $customer->active = $request->has('active');

        $customer->save();

        return redirect()->route('admin.customers.show', $customer->id)
            ->with('success', 'Cliente atualizado com sucesso.');
    }

    /**
     * Show form to reset customer's password.
     *
     * @param User $customer
     * @return \Illuminate\View\View
     */
    public function showResetPassword(User $customer)
    {
        // Make sure this is a customer
        if ($customer->role !== 'customer') {
            abort(404);
        }

        return view('admin.customers.reset_password', compact('customer'));
    }

    /**
     * Reset customer's password.
     *
     * @param Request $request
     * @param User $customer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPassword(Request $request, User $customer)
    {
        // Make sure this is a customer
        if ($customer->role !== 'customer') {
            abort(404);
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $customer->password = Hash::make($request->password);
        $customer->save();

        return redirect()->route('admin.customers.show', $customer->id)
            ->with('success', 'Senha redefinida com sucesso.');
    }

    /**
     * Show customer's orders.
     *
     * @param User $customer
     * @return \Illuminate\View\View
     */
    public function orders(User $customer)
    {
        // Make sure this is a customer
        if ($customer->role !== 'customer') {
            abort(404);
        }

        $orders = Order::where('user_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.customers.orders', compact('customer', 'orders'));
    }

    /**
     * Show customer's reviews.
     *
     * @param User $customer
     * @return \Illuminate\View\View
     */
    public function reviews(User $customer)
    {
        // Make sure this is a customer
        if ($customer->role !== 'customer') {
            abort(404);
        }

        $reviews = Review::where('user_id', $customer->id)
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.customers.reviews', compact('customer', 'reviews'));
    }

    /**
     * Approve or hide a review.
     *
     * @param Request $request
     * @param Review $review
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleReviewStatus(Request $request, Review $review)
    {
        $review->approved = !$review->approved;
        $review->save();

        $status = $review->approved ? 'aprovada' : 'ocultada';

        return back()->with('success', "Avaliação {$status} com sucesso.");
    }

    /**
     * Export customers to CSV.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        $customers = User::where('role', 'customer')->get();

        // Create CSV file
        $filename = 'clientes-' . date('Y-m-d') . '.csv';
        $path = storage_path('app/exports/' . $filename);

        // Make sure the directory exists
        if (!file_exists(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0755, true);
        }

        $file = fopen($path, 'w');

        // Add CSV headers
        fputcsv($file, [
            'ID',
            'Nome',
            'Email',
            'Telefone',
            'Endereço',
            'Cidade',
            'Estado',
            'CEP',
            'Status',
            'Data de Cadastro',
            'Total de Pedidos',
            'Valor Total de Compras'
        ]);

        // Add customer data
        foreach ($customers as $customer) {
            $orderCount = Order::where('user_id', $customer->id)->count();
            $totalSpent = Order::where('user_id', $customer->id)
                ->whereIn('status', ['processing', 'shipped', 'delivered'])
                ->sum('total');

            fputcsv($file, [
                $customer->id,
                $customer->name,
                $customer->email,
                $customer->phone,
                $customer->address,
                $customer->city,
                $customer->state,
                $customer->zipcode,
                $customer->active ? 'Ativo' : 'Inativo',
                $customer->created_at->format('d/m/Y H:i'),
                $orderCount,
                'R$ ' . number_format($totalSpent, 2, ',', '.')
            ]);
        }

        fclose($file);

        return response()->download($path, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
