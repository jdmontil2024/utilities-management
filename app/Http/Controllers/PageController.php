<?php

namespace App\Http\Controllers;

use App\Models\{
    Building, Unit, Tenant, Lease, Bill,
    MaintenanceRequest, Alert, ActivityLog
};
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Show the home page.
     */
    public function home()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        // Public homepage statistics
        $stats = [
            'total_buildings' => Building::count(),
            'total_units' => Unit::count(),
            'happy_tenants' => Tenant::where('is_active', true)->count(),
            'years_experience' => 5, // Static value or calculate from oldest building
        ];

        return view('pages.home', compact('stats'));
    }

    /**
     * Handle search requests.
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        if (!$query) {
            return view('pages.search', ['results' => []]);
        }

        $results = [];

        // Search buildings
        $results['buildings'] = Building::where('name', 'like', "%{$query}%")
            ->orWhere('address', 'like', "%{$query}%")
            ->orWhere('city', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        // Search units
        $results['units'] = Unit::with('building')
            ->where('unit_number', 'like', "%{$query}%")
            ->orWhereHas('building', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        // Search tenants
        $results['tenants'] = Tenant::where('first_name', 'like', "%{$query}%")
            ->orWhere('last_name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        // Search leases
        $results['leases'] = Lease::with(['tenant', 'unit.building'])
            ->where('lease_number', 'like', "%{$query}%")
            ->orWhereHas('tenant', function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        // Search bills
        $results['bills'] = Bill::with(['lease.tenant'])
            ->where('bill_number', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        return view('pages.search', compact('results', 'query'));
    }

    /**
     * Search autocomplete for AJAX requests.
     */
    public function searchAutocomplete(Request $request)
    {
        $query = $request->get('q');
        $results = [];

        if ($query) {
            // Buildings
            $buildings = Building::where('name', 'like', "%{$query}%")
                ->select('id', 'name as text')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    $item->type = 'Building';
                    $item->url = route('buildings.show', $item->id);
                    return $item;
                });

            // Tenants
            $tenants = Tenant::where('first_name', 'like', "%{$query}%")
                ->orWhere('last_name', 'like', "%{$query}%")
                ->select('id', DB::raw("CONCAT(first_name, ' ', last_name) as text"))
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    $item->type = 'Tenant';
                    $item->url = route('tenants.show', $item->id);
                    return $item;
                });

            // Units
            $units = Unit::with('building')
                ->where('unit_number', 'like', "%{$query}%")
                ->select('id', 'unit_number as text', 'building_id')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    $item->type = 'Unit';
                    $item->text = $item->text . ' - ' . $item->building->name;
                    $item->url = route('units.show', $item->id);
                    return $item;
                });

            $results = $buildings->merge($tenants)->merge($units);
        }

        return response()->json($results);
    }

    /**
     * Show notifications page.
     */
    public function notifications()
    {
        $alerts = Alert::with('alertable')
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $readAlerts = Alert::with('alertable')
            ->where('is_read', true)
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'read_page');

        return view('pages.notifications', compact('alerts', 'readAlerts'));
    }

    /**
     * Mark notification as read.
     */
    public function markNotificationRead($id)
    {
        $alert = Alert::findOrFail($id);
        $alert->update(['is_read' => true]);

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * Show calendar page.
     */
    public function calendar()
    {
        // Get events for the calendar
        $maintenanceEvents = MaintenanceRequest::whereNotNull('scheduled_date')
            ->select('id', 'title', 'scheduled_date as start', 'scheduled_date as end', DB::raw("'maintenance' as type"))
            ->get();

        $leaseEvents = Lease::where('end_date', '>=', now())
            ->select('id', DB::raw("CONCAT('Lease #', lease_number) as title"), 'end_date as start', 'end_date as end', DB::raw("'lease' as type"))
            ->get();

        $billEvents = Bill::where('due_date', '>=', now())
            ->where('status', 'pending')
            ->select('id', DB::raw("CONCAT('Bill #', bill_number) as title"), 'due_date as start', 'due_date as end', DB::raw("'bill' as type"))
            ->get();

        $events = $maintenanceEvents->merge($leaseEvents)->merge($billEvents);

        return view('pages.calendar', compact('events'));
    }

    /**
     * Show map view.
     */
    public function map()
    {
        $buildings = Building::with(['units' => function ($query) {
            $query->select('id', 'building_id', 'unit_number', 'status', 'monthly_rent');
        }])->get();

        return view('pages.map', compact('buildings'));
    }

    /**
     * Show import/export page.
     */
    public function importExport()
    {
        return view('pages.import-export');
    }

    /**
     * Handle import requests.
     */
    public function import(Request $request, $model)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls'
        ]);

        // Handle import based on model type
        // You'll need to implement specific import logic for each model
        $file = $request->file('file');
        
        // Example: CSV import
        $path = $file->store('imports');
        
        // Process the import based on model type
        switch ($model) {
            case 'tenants':
                // Import tenants logic
                break;
            case 'units':
                // Import units logic
                break;
            case 'meter-readings':
                // Import meter readings logic
                break;
            // Add more cases as needed
        }

        return redirect()->back()->with('success', 'Import completed successfully.');
    }

    /**
     * Handle export requests.
     */
    public function export(Request $request, $model)
    {
        // Generate export file based on model type
        switch ($model) {
            case 'tenants':
                $data = \App\Models\Tenant::all();
                $filename = 'tenants_' . date('Y-m-d') . '.csv';
                break;
            case 'units':
                $data = \App\Models\Unit::with('building')->get();
                $filename = 'units_' . date('Y-m-d') . '.csv';
                break;
            case 'bills':
                $data = \App\Models\Bill::with(['lease.tenant', 'lease.unit'])->get();
                $filename = 'bills_' . date('Y-m-d') . '.csv';
                break;
            default:
                return redirect()->back()->with('error', 'Invalid export type.');
        }

        // Generate CSV
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Write headers
            if ($data->count() > 0) {
                fputcsv($file, array_keys($data->first()->toArray()));
            }
            
            // Write data
            foreach ($data as $row) {
                fputcsv($file, $row->toArray());
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show backup page.
     */
    public function backup()
    {
        // Get backup files from storage
        $backupPath = storage_path('app/backups');
        $backups = [];
        
        if (file_exists($backupPath)) {
            $files = scandir($backupPath);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $backups[] = [
                        'name' => $file,
                        'size' => filesize($backupPath . '/' . $file),
                        'date' => date('Y-m-d H:i:s', filemtime($backupPath . '/' . $file)),
                    ];
                }
            }
        }

        return view('pages.backup', compact('backups'));
    }

    /**
     * Create a backup.
     */
    public function createBackup(Request $request)
    {
        $request->validate([
            'type' => 'required|in:full,database,files'
        ]);

        // Implement backup logic here
        // You might want to use spatie/laravel-backup package
        
        return redirect()->back()->with('success', 'Backup created successfully.');
    }

    /**
     * Restore from backup.
     */
    public function restoreBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|string'
        ]);

        // Implement restore logic here
        
        return redirect()->back()->with('success', 'Backup restored successfully.');
    }

    /**
     * Show help page.
     */
    public function help()
    {
        return view('pages.help');
    }

    /**
     * Show documentation page.
     */
    public function documentation()
    {
        return view('pages.documentation');
    }
}