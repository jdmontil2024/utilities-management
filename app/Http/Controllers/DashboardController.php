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
    public function index()
    {
        $stats = [
            'total_buildings' => Building::count(),
            'total_units' => Unit::count(),
            'occupied_units' => Unit::where('status', 'occupied')->count(),
            'vacant_units' => Unit::where('status', 'vacant')->count(),
            'total_tenants' => Tenant::where('is_active', 1)->count(),
            'active_leases' => Lease::where('lease_status', 'active')->count(),
            'pending_maintenance' => MaintenanceRequest::whereNotIn('status', ['completed', 'cancelled'])->count(),
            'overdue_bills' => Bill::where('status', 'pending')
                ->where('due_date', '<', now())
                ->count(),
            'monthly_revenue' => Lease::where('lease_status', 'active')->sum('monthly_rent'),
        ];

        $recent_tenants = Tenant::with(['building', 'unit'])
            ->latest()
            ->limit(5)
            ->get();

        $recent_maintenance = MaintenanceRequest::with(['unit.building', 'tenant'])
            ->latest()
            ->limit(5)
            ->get();

        $expiring_leases = Lease::with(['tenant', 'unit.building'])
            ->where('lease_status', 'active')
            ->whereDate('end_date', '>=', now())
            ->whereDate('end_date', '<=', now()->addDays(30))
            ->orderBy('end_date')
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'recent_tenants', 'recent_maintenance', 'expiring_leases'));
    }

    /**
     * Get dashboard statistics for AJAX.
     */
    public function stats()
    {
        $stats = [
            'total_buildings' => Building::count(),
            'total_units' => Unit::count(),
            'occupied_units' => Unit::where('status', 'occupied')->count(),
            'vacant_units' => Unit::where('status', 'vacant')->count(),
            'total_tenants' => Tenant::where('is_active', 1)->count(),
            'active_leases' => Lease::where('lease_status', 'active')->count(),
            'pending_maintenance' => MaintenanceRequest::whereNotIn('status', ['completed', 'cancelled'])->count(),
            'overdue_bills' => Bill::where('status', 'pending')
                ->where('due_date', '<', now())
                ->count(),
            'monthly_revenue' => Lease::where('lease_status', 'active')->sum('monthly_rent'),
            'occupancy_rate' => $this->calculateOccupancyRate(),
        ];

        return response()->json($stats);
    }

    /**
     * Get chart data for dashboard.
     */
    public function charts()
    {
        // Revenue by month from leases table
        $revenue_by_month = Lease::where('lease_status', 'active')
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(monthly_rent) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();

        // Units by status
        $units_by_status = Unit::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // Maintenance by category
        $maintenance_by_category = MaintenanceRequest::select(
                'maintenance_category_id',
                DB::raw('count(*) as count')
            )
            ->with('category')
            ->groupBy('maintenance_category_id')
            ->limit(5)
            ->get();

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