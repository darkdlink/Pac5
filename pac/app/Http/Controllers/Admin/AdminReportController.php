<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Report\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    protected $reportService;

    /**
     * Create a new controller instance.
     *
     * @param ReportService $reportService
     */
    public function __construct(ReportService $reportService)
    {
        $this->middleware('admin.access');
        $this->reportService = $reportService;
    }

    /**
     * Display sales report.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function sales(Request $request)
    {
        // Set default period to last 30 days if not specified
        $period = $request->get('period', '30days');
        $startDate = $this->calculateStartDate($period);
        $endDate = Carbon::now();

        // Get sales data
        $salesData = $this->reportService->getSalesReport($startDate, $endDate);

        // Get data for charts
        $salesByDay = $this->reportService->getSalesByDay($startDate, $endDate);
        $salesByCategory = $this->reportService->getSalesByCategory($startDate, $endDate);
        $salesByPaymentMethod = $this->reportService->getSalesByPaymentMethod($startDate, $endDate);

        return view('admin.reports.sales', compact(
            'salesData',
            'salesByDay',
            'salesByCategory',
            'salesByPaymentMethod',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display inventory report.
     *
     * @return \Illuminate\View\View
     */
    public function inventory()
    {
        // Get inventory data
        $inventoryData = $this->reportService->getInventoryReport();

        // Get low stock products
        $lowStockProducts = $this->reportService->getLowStockProducts();

        // Get out of stock products
        $outOfStockProducts = $this->reportService->getOutOfStockProducts();

        // Get inventory movement
        $inventoryMovement = $this->reportService->getInventoryMovement();

        return view('admin.reports.inventory', compact(
            'inventoryData',
            'lowStockProducts',
            'outOfStockProducts',
            'inventoryMovement'
        ));
    }

    /**
     * Display customers report.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function customers(Request $request)
    {
        // Set default period to last 30 days if not specified
        $period = $request->get('period', '30days');
        $startDate = $this->calculateStartDate($period);
        $endDate = Carbon::now();

        // Get customers data
        $customersData = $this->reportService->getCustomersReport($startDate, $endDate);

        // Get top customers
        $topCustomers = $this->reportService->getTopCustomers($startDate, $endDate, 10);

        // Get new customers
        $newCustomers = $this->reportService->getNewCustomers($startDate, $endDate);

        // Get customer retention rate
        $retentionRate = $this->reportService->getCustomerRetentionRate($startDate, $endDate);

        return view('admin.reports.customers', compact(
            'customersData',
            'topCustomers',
            'newCustomers',
            'retentionRate',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export report to CSV.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:sales,inventory,customers',
            'period' => 'required|in:7days,30days,90days,year,custom',
            'start_date' => 'required_if:period,custom|date',
            'end_date' => 'required_if:period,custom|date|after_or_equal:start_date',
        ]);

        $reportType = $request->report_type;
        $period = $request->period;

        // Calculate dates
        if ($period === 'custom') {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
        } else {
            $startDate = $this->calculateStartDate($period);
            $endDate = Carbon::now();
        }

        // Generate CSV file based on report type
        switch ($reportType) {
            case 'sales':
                $filePath = $this->reportService->exportSalesReport($startDate, $endDate);
                $fileName = 'relatorio-vendas-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.csv';
                break;

            case 'inventory':
                $filePath = $this->reportService->exportInventoryReport();
                $fileName = 'relatorio-estoque-' . Carbon::now()->format('Y-m-d') . '.csv';
                break;

            case 'customers':
                $filePath = $this->reportService->exportCustomersReport($startDate, $endDate);
                $fileName = 'relatorio-clientes-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.csv';
                break;

            default:
                abort(400, 'Invalid report type');
        }

        return response()->download($filePath, $fileName);
    }

    /**
     * Display the engagement report.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function engagement(Request $request)
    {
        // Set default period to last 30 days if not specified
        $period = $request->get('period', '30days');
        $startDate = $this->calculateStartDate($period);
        $endDate = Carbon::now();

        // Get engagement data (e.g., product page views, cart activity)
        $engagementData = $this->reportService->getEngagementReport($startDate, $endDate);

        // Get product page views
        $productViews = $this->reportService->getProductViews($startDate, $endDate);

        // Get cart activity (adds, removes, abandons)
        $cartActivity = $this->reportService->getCartActivity($startDate, $endDate);

        // Get review activity
        $reviewActivity = $this->reportService->getReviewActivity($startDate, $endDate);

        return view('admin.reports.engagement', compact(
            'engagementData',
            'productViews',
            'cartActivity',
            'reviewActivity',
            'period',
            'startDate',
            'endDate'
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
}
