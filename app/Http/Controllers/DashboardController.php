<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Building;
use App\Models\Unit;
use App\Models\Tenant;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Bill;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(Request $request)
    {
        // Get all buildings count
        $totalBuildings = Building::count();
        
        // Get total units count
        $totalUnits = Unit::count();
        
        // Get occupied units (units with active lease)
        $occupiedUnits = Unit::whereHas('leases', function($q) {
            $q->where('lease_status', 'active');
        })->count();
        
        // Calculate occupancy rate
        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100) : 0;
        
        // Get total active tenants
        $totalTenants = Tenant::where('is_active', 1)->count();
        
        // Get monthly revenue from active leases
        $monthlyRevenue = Lease::where('lease_status', 'active')->sum('monthly_rent');
        
        // Calculate revenue growth (compare with previous month)
        $currentMonthRevenue = Lease::where('lease_status', 'active')
            ->whereMonth('start_date', now()->month)
            ->whereYear('start_date', now()->year)
            ->sum('monthly_rent');
        
        $previousMonthRevenue = Lease::where('lease_status', 'active')
            ->whereMonth('start_date', now()->subMonth()->month)
            ->whereYear('start_date', now()->subMonth()->year)
            ->sum('monthly_rent');
        
        $revenueGrowth = $previousMonthRevenue > 0 
            ? round((($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100) 
            : ($currentMonthRevenue > 0 ? 100 : 0);
        
        // Get open maintenance requests count
        $openMaintenanceCount = MaintenanceRequest::whereNotIn('status', ['completed', 'cancelled'])->count();
        
        // Get recent maintenance requests
        $recentMaintenanceRequests = MaintenanceRequest::with(['unit', 'unit.building'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Get emergency maintenance count
        $emergencyMaintenanceCount = MaintenanceRequest::where('priority', 'emergency')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();
        
        // Get expiring leases count (next 30 days)
        $expiringLeasesCount = Lease::where('lease_status', 'active')
            ->whereDate('end_date', '>=', now())
            ->whereDate('end_date', '<=', now()->addDays(30))
            ->count();
        
        // Get expiring leases details
        $expiringLeases = Lease::with(['tenant', 'unit.building'])
            ->where('lease_status', 'active')
            ->whereDate('end_date', '>=', now())
            ->whereDate('end_date', '<=', now()->addDays(30))
            ->orderBy('end_date')
            ->limit(5)
            ->get();
        
        // Get overdue bills count
        $overdueBillsCount = Bill::where('status', 'pending')
            ->where('due_date', '<', now())
            ->count();
        
        // Get recent tenants
        $recentTenants = Tenant::with(['building', 'unit'])
            ->latest()
            ->limit(5)
            ->get();
        
        // Chart data - Get monthly revenue from units through leases
        $chartMonths = [];
        $chartRevenueData = [];
        
        // Query to get monthly revenue from leases grouped by month
        // This shows revenue for each month based on lease start dates
        $revenueData = Lease::where('lease_status', 'active')
            ->select(
                DB::raw("TO_CHAR(DATE_TRUNC('month', start_date), 'Mon YYYY') as month_label"),
                DB::raw("TO_CHAR(DATE_TRUNC('month', start_date), 'YYYY-MM') as month_sort"),
                DB::raw("SUM(monthly_rent) as total_revenue")
            )
            ->groupBy('month_sort', 'month_label')
            ->orderBy('month_sort', 'asc')
            ->get();
        
        foreach ($revenueData as $data) {
            $chartMonths[] = $data->month_label;
            $chartRevenueData[] = (float) $data->total_revenue;
        }
        
        // If no data yet, add placeholder
        if (empty($chartMonths)) {
            $chartMonths = ['No Data'];
            $chartRevenueData = [0];
        }
        
        // Prepare stats array for view
        $stats = [
            'total_buildings' => $totalBuildings,
            'total_units' => $totalUnits,
            'occupied_units' => $occupiedUnits,
            'vacant_units' => $totalUnits - $occupiedUnits,
            'total_tenants' => $totalTenants,
            'active_leases' => Lease::where('lease_status', 'active')->count(),
            'pending_maintenance' => $openMaintenanceCount,
            'overdue_bills' => $overdueBillsCount,
            'monthly_revenue' => $monthlyRevenue,
            'occupancy_rate' => $occupancyRate,
        ];
        
        return view('dashboard', compact(
            'stats',
            'recentTenants',
            'recentMaintenanceRequests',
            'expiringLeases',
            'totalBuildings',
            'totalUnits',
            'totalTenants',
            'occupancyRate',
            'monthlyRevenue',
            'revenueGrowth',
            'openMaintenanceCount',
            'emergencyMaintenanceCount',
            'expiringLeasesCount',
            'overdueBillsCount',
            'chartMonths',
            'chartRevenueData',
            'expiringLeases',
            'recentTenants'
        ));
    }

    /**
     * Get dashboard statistics for AJAX.
     */
    public function stats()
    {
        $totalUnits = Unit::count();
        $occupiedUnits = Unit::whereHas('leases', function($q) {
            $q->where('lease_status', 'active');
        })->count();
        
        $stats = [
            'total_buildings' => Building::count(),
            'total_units' => $totalUnits,
            'occupied_units' => $occupiedUnits,
            'vacant_units' => $totalUnits - $occupiedUnits,
            'total_tenants' => Tenant::where('is_active', 1)->count(),
            'active_leases' => Lease::where('lease_status', 'active')->count(),
            'pending_maintenance' => MaintenanceRequest::whereNotIn('status', ['completed', 'cancelled'])->count(),
            'overdue_bills' => Bill::where('status', 'pending')
                ->where('due_date', '<', now())
                ->count(),
            'monthly_revenue' => Lease::where('lease_status', 'active')->sum('monthly_rent'),
            'occupancy_rate' => $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 1) : 0,
        ];

        return response()->json($stats);
    }

    /**
     * Get chart data for dashboard.
     */
    public function charts()
    {
        // Revenue by month from leases using start_date
        $revenue_by_month = Lease::where('lease_status', 'active')
            ->select(
                DB::raw("TO_CHAR(DATE_TRUNC('month', start_date), 'Mon YYYY') as month"),
                DB::raw("SUM(monthly_rent) as total")
            )
            ->groupBy(DB::raw("DATE_TRUNC('month', start_date)"))
            ->orderBy(DB::raw("DATE_TRUNC('month', start_date)"), 'asc')
            ->get()
            ->map(function($item) {
                return [
                    'month' => $item->month,
                    'total' => (float) $item->total
                ];
            });

        // Units by status
        $units_by_status = Unit::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function($item) {
                return [
                    'status' => $item->status,
                    'count' => $item->count
                ];
            });

        // Maintenance by category
        $maintenance_by_category = MaintenanceRequest::select(
                'maintenance_category_id',
                DB::raw('count(*) as count')
            )
            ->with('maintenanceCategory')
            ->groupBy('maintenance_category_id')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'category' => $item->maintenanceCategory->name ?? 'Uncategorized',
                    'count' => $item->count
                ];
            });

        return response()->json([
            'revenue_by_month' => $revenue_by_month,
            'units_by_status' => $units_by_status,
            'maintenance_by_category' => $maintenance_by_category,
        ]);
    }

    /**
     * Calculate overall occupancy rate.
     */
    private function calculateOccupancyRate()
    {
        $total_units = Unit::count();
        if ($total_units === 0) {
            return 0;
        }

        $occupied_units = Unit::where('status', 'occupied')->count();
        return round(($occupied_units / $total_units) * 100, 1);
    }
}