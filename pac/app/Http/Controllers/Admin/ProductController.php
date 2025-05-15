<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Report\ReportService;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $reportService;
    protected $inventoryService;

    /**
     * Create a new controller instance.
     *
     * @param ReportService $reportService
     * @param InventoryService $inventoryService
     */
    public function __construct(
        ReportService $reportService,
        InventoryService $inventoryService
    ) {
        $this->middleware('admin.access');
        $this->reportService = $reportService;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display admin dashboard.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Set default period to last 30 days if not specified
        $period = $request->get('period', '30days');

        // Calculate start date based on period
        $startDate = $this->calculateStartDate($period);

        // Key metrics
        $totalSales = $this->reportService->getTotalSalesAmount($startDate);
        $totalOrders = $this->reportService->getTotalOrdersCount($startDate);
        $newCustomers = $this->reportService->getNewCustomersCount($startDate);
        $avgOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        // Charts data
        $salesChart = $this->reportService->getSalesChartData($startDate);
        $topProducts = $this->reportService->getTopSellingProducts($startDate, 5);

        // Latest orders
        $latestOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Low stock alerts
        $lowStockProducts = $this->inventoryService->getLowStockProducts(5);

        // Pending orders
        $pendingOrders = Order::where('status', 'pending')->count();

        return view('admin.dashboard', compact(
            'totalSales',
            'totalOrders',
            'newCustomers',
            'avgOrderValue',
            'salesChart',
            'topProducts',
            'latestOrders',
            'lowStockProducts',
            'pendingOrders',
            'period'
        ));
    }

    /**
     * Calculate start date based on selected period.
     *
     * @param string $period
     * @return \Illuminate\Support\Carbon
     */
    private function calculateStartDate(string $period): Carbon
    {
        $now = Carbon::now();

        switch ($period) {
            case '7days':
                return $now->copy()->subDays(7)->startOfDay();
            case '30days':
                return $now->copy()->subDays(30)->startOfDay();
            case '90days':
                return $now->copy()->subDays(90)->startOfDay();
            case 'year':
                return $now->copy()->subYear()->startOfDay();
            case 'month':
                return $now->copy()->startOfMonth();
            default:
                return $now->copy()->subDays(30)->startOfDay();
        }
    }

    /**
     * Display sales analytics.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function salesAnalytics(Request $request)
    {
        // Set default period to last 30 days if not specified
        $period = $request->get('period', '30days');
        $startDate = $this->calculateStartDate($period);

        // Get detailed sales data
        $salesByDay = $this->reportService->getSalesByDay($startDate);
        $salesByCategory = $this->reportService->getSalesByCategory($startDate);
        $salesByPaymentMethod = $this->reportService->getSalesByPaymentMethod($startDate);

        // Compare with previous period
        $previousStartDate = $this->calculatePreviousPeriod($startDate, $period);
        $previousPeriodSales = $this->reportService->getTotalSalesAmount($previousStartDate, $startDate);
        $currentPeriodSales = $this->reportService->getTotalSalesAmount($startDate);

        $salesGrowth = $previousPeriodSales > 0
            ? (($currentPeriodSales - $previousPeriodSales) / $previousPeriodSales) * 100
            : 0;

        return view('admin.analytics.sales', compact(
            'salesByDay',
            'salesByCategory',
            'salesByPaymentMethod',
            'salesGrowth',
            'currentPeriodSales',
            'previousPeriodSales',
            'period'
        ));
    }

    /**
     * Calculate the start date for the previous period.
     *
     * @param Carbon $startDate
     * @param string $period
     * @return Carbon
     */
    private function calculatePreviousPeriod(Carbon $startDate, string $period): Carbon
    {
        $now = Carbon::now();
        $diff = $now->diffInDays($startDate);

        return $startDate->copy()->subDays($diff);
    }

    /**
     * Display customer analytics.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function customerAnalytics(Request $request)
    {
        // Set default period to last 30 days if not specified
        $period = $request->get('period', '30days');
        $startDate = $this->calculateStartDate($period);

        // Top customers by revenue
        $topCustomers = $this->reportService->getTopCustomersByRevenue($startDate, 10);

        // New vs returning customers
        $newVsReturning = $this->reportService->getNewVsReturningCustomers($startDate);

        // Customer retention rate
        $retentionRate = $this->reportService->getCustomerRetentionRate($startDate);

        // Average orders per customer
        $avgOrdersPerCustomer = $this->reportService->getAverageOrdersPerCustomer($startDate);

        return view('admin.analytics.customers', compact(
            'topCustomers',
            'newVsReturning',
            'retentionRate',
            'avgOrdersPerCustomer',
            'period'
        ));
    }

    /**
     * Display product analytics.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function productAnalytics(Request $request)
    {
        // Set default period to last 30 days if not specified
        $period = $request->get('period', '30days');
        $startDate = $this->calculateStartDate($period);

        // Top selling products
        $topSellingProducts = $this->reportService->getTopSellingProducts($startDate, 10);

        // Products with highest revenue
        $topRevenueProducts = $this->reportService->getProductsByRevenue($startDate, 10);

        // Least selling products
        $leastSellingProducts = $this->reportService->getLeastSellingProducts($startDate, 10);

        // Product category distribution
        $productCategoryDistribution = $this->reportService->getProductCategoryDistribution($startDate);

        return view('admin.analytics.products', compact(
            'topSellingProducts',
            'topRevenueProducts',
            'leastSellingProducts',
            'productCategoryDistribution',
            'period'
        ));
    }
}
