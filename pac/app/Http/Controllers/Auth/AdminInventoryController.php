<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Inventory;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    protected $inventoryService;

    /**
     * Create a new controller instance.
     *
     * @param InventoryService $inventoryService
     */
    public function __construct(InventoryService $inventoryService)
    {
        $this->middleware('admin.access');
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display inventory overview.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = $request->get('query');
        $category = $request->get('category');
        $stockFilter = $request->get('stock', 'all');

        // Get products with stock information
        $products = $this->inventoryService->getProductsWithStock(
            $query,
            $category,
            $stockFilter,
            15
        );

        // Get categories for filter
        $categories = DB::table('categories')
            ->where('type', 'product')
            ->get();

        // Get total products, low stock count, and out of stock count
        $totalProducts = Product::count();
        $lowStockCount = $this->inventoryService->getLowStockCount();
        $outOfStockCount = $this->inventoryService->getOutOfStockCount();

        return view('admin.inventory.index', compact(
            'products',
            'categories',
            'query',
            'category',
            'stockFilter',
            'totalProducts',
            'lowStockCount',
            'outOfStockCount'
        ));
    }

    /**
     * Show the form for editing product stock.
     *
     * @param Product $product
     * @return \Illuminate\View\View
     */
    public function edit(Product $product)
    {
        // Get stock history for this product
        $stockHistory = $this->inventoryService->getProductStockHistory($product->id, 10);

        return view('admin.inventory.edit', compact('product', 'stockHistory'));
    }

    /**
     * Update product stock.
     *
     * @param Request $request
     * @param Product $product
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'stock' => 'required|integer|min:0',
            'adjustment_type' => 'required|in:set,add,subtract',
            'notes' => 'nullable|string|max:255',
        ]);

        $type = $request->adjustment_type;
        $quantity = $request->stock;
        $notes = $request->notes;

        // Update stock based on adjustment type
        switch ($type) {
            case 'set':
                $result = $this->inventoryService->setProductStock($product->id, $quantity, $notes);
                break;
            case 'add':
                $result = $this->inventoryService->addProductStock($product->id, $quantity, $notes);
                break;
            case 'subtract':
                $result = $this->inventoryService->subtractProductStock($product->id, $quantity, $notes);
                break;
            default:
                return redirect()->back()->with('error', 'Tipo de ajuste inválido.');
        }

        if ($result) {
            return redirect()->route('admin.inventory.index')
                ->with('success', 'Estoque atualizado com sucesso.');
        }

        return redirect()->back()->with('error', 'Erro ao atualizar o estoque.');
    }

    /**
     * Display product stock history.
     *
     * @param Product $product
     * @return \Illuminate\View\View
     */
    public function history(Product $product)
    {
        // Get full stock history for this product
        $stockHistory = $this->inventoryService->getProductStockHistory($product->id);

        return view('admin.inventory.history', compact('product', 'stockHistory'));
    }

    /**
     * Display low stock products.
     *
     * @return \Illuminate\View\View
     */
    public function lowStock()
    {
        $products = $this->inventoryService->getLowStockProducts();

        return view('admin.inventory.low_stock', compact('products'));
    }

    /**
     * Display out of stock products.
     *
     * @return \Illuminate\View\View
     */
    public function outOfStock()
    {
        $products = $this->inventoryService->getOutOfStockProducts();

        return view('admin.inventory.out_of_stock', compact('products'));
    }

    /**
     * Bulk import stock from CSV file.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        $request->validate([
            'stock_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        try {
            $file = $request->file('stock_file');
            $result = $this->inventoryService->importStockFromCsv($file);

            if ($result['success']) {
                return redirect()->route('admin.inventory.index')
                    ->with('success', "Importação concluída. {$result['updated']} produtos atualizados.");
            }

            return redirect()->route('admin.inventory.index')
                ->with('error', "Erro na importação: {$result['message']}");
        } catch (\Exception $e) {
            return redirect()->route('admin.inventory.index')
                ->with('error', 'Erro ao processar o arquivo: ' . $e->getMessage());
        }
    }

    /**
     * Export inventory to CSV.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        try {
            $filePath = $this->inventoryService->exportInventoryToCsv();

            return response()->download($filePath, 'inventario-' . now()->format('Y-m-d') . '.csv');
        } catch (\Exception $e) {
            return redirect()->route('admin.inventory.index')
                ->with('error', 'Erro ao gerar o arquivo CSV: ' . $e->getMessage());
        }
    }

    /**
     * Generate inventory adjustment report.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function adjustments(Request $request)
    {
        $startDate = $request->get('start_date') ? \Carbon\Carbon::parse($request->start_date) : now()->subDays(30);
        $endDate = $request->get('end_date') ? \Carbon\Carbon::parse($request->end_date) : now();
        $adjustmentType = $request->get('type');

        // Get inventory adjustments
        $adjustments = $this->inventoryService->getInventoryAdjustments(
            $startDate,
            $endDate,
            $adjustmentType,
            15
        );

        // Get adjustment types for filter
        $adjustmentTypes = [
            'add' => 'Adição',
            'subtract' => 'Subtração',
            'set' => 'Definição',
            'order' => 'Pedido',
            'return' => 'Devolução',
            'import' => 'Importação'
        ];

        return view('admin.inventory.adjustments', compact(
            'adjustments',
            'startDate',
            'endDate',
            'adjustmentType',
            'adjustmentTypes'
        ));
    }
}
