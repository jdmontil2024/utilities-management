<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\Unit;
use App\Models\Tenant;
use App\Models\Building;
use App\Models\MaintenanceCategory;
use App\Models\Vendor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaintenanceRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MaintenanceRequest::with([
            'unit', 
            'unit.building',
            'tenant', 
            'maintenanceCategory', 
            'assignedVendor', 
            'assignedStaff'
        ]);
        
        // Filter by building if specified
        if ($request->has('building_id') && $request->building_id) {
            $query->whereHas('unit', function($q) use ($request) {
                $q->where('building_id', $request->building_id);
            });
        }
        
        // Filter by unit
        if ($request->has('unit_id') && $request->unit_id) {
            $query->where('unit_id', $request->unit_id);
        }
        
        // Filter by tenant - THIS NOW SHOWS HISTORICAL DATA, NOT CURRENT
        if ($request->has('tenant_id') && $request->tenant_id) {
            $query->where('tenant_id', $request->tenant_id);
        }
        
        // Filter by status (supporting comma-separated list)
        if ($request->has('status') && $request->status) {
            $statuses = explode(',', $request->status);
            $query->whereIn('status', $statuses);
        }
        
        // Filter by priority
        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }
        
        // Filter by category
        if ($request->has('maintenance_category_id') && $request->maintenance_category_id) {
            $query->where('maintenance_category_id', $request->maintenance_category_id);
        }
        
        // Filter by vendor
        if ($request->has('assigned_vendor_id') && $request->assigned_vendor_id) {
            $query->where('assigned_vendor_id', $request->assigned_vendor_id);
        }
        
        // Date range filters
        if ($request->has('date_from') && $request->date_from) {
            $query->where('request_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->where('request_date', '<=', $request->date_to);
        }
        
        // Search in title and description
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }
        
        // Special filters
        if ($request->has('filter')) {
            switch ($request->filter) {
                case 'pending':
                    $query->whereNotIn('status', ['completed', 'cancelled']);
                    break;
                case 'overdue':
                    $query->whereNotIn('status', ['completed', 'cancelled'])
                          ->whereHas('maintenanceCategory', function($q) {
                              $q->whereNotNull('sla_hours');
                          });
                    break;
                case 'emergency':
                    $query->where('priority', 'emergency')
                          ->whereNotIn('status', ['completed', 'cancelled']);
                    break;
            }
        }
        
        $requests = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // If it's an API request, return JSON
        if ($request->wantsJson()) {
            return response()->json($requests);
        }
        
        // For web, return view
        return view('maintenance-requests.index', compact('requests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $buildings = Building::where('status', 'active')->orderBy('name')->get();
        $categories = MaintenanceCategory::orderBy('name')->get();
        $vendors = Vendor::orderBy('company_name')->get();
        $staff = User::where('is_active', true)->orderBy('name')->get();
        
        // Pre-select building if provided
        $selectedBuilding = null;
        if ($request->has('building_id')) {
            $selectedBuilding = Building::find($request->building_id);
        }
        
        // Pre-select unit if provided
        $selectedUnit = null;
        if ($request->has('unit_id')) {
            $selectedUnit = Unit::with('building')->find($request->unit_id);
        }
        
        // Get current tenant for the pre-selected unit (for display only)
        $currentTenant = null;
        if ($selectedUnit) {
            $currentTenant = $selectedUnit->currentTenant;
        }
        
        return view('maintenance-requests.create', compact(
            'buildings', 
            'categories', 
            'vendors', 
            'staff',
            'selectedBuilding',
            'selectedUnit',
            'currentTenant'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'tenant_id' => 'nullable|exists:tenants,id',
            'maintenance_category_id' => 'required|exists:maintenance_categories,id',
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,emergency',
            'status' => 'sometimes|in:submitted,assigned,in_progress,completed,cancelled',
            'request_date' => 'required|date',
            'scheduled_date' => 'nullable|date',
            'estimated_cost' => 'nullable|numeric|min:0',
            'assigned_vendor_id' => 'nullable|exists:vendors,id',
            'assigned_staff_id' => 'nullable|exists:users,id',
            'access_instructions' => 'nullable|string',
            'internal_notes' => 'nullable|string',
        ]);

        $validated['status'] = $validated['status'] ?? 'submitted';
        
        // CRITICAL FIX: If tenant_id is not provided, try to get the current tenant of the unit
        // This is for historical reference only - the request is tied to the unit, not the tenant
        if (empty($validated['tenant_id'])) {
            $unit = Unit::with('currentTenant')->find($validated['unit_id']);
            if ($unit && $unit->currentTenant) {
                $validated['tenant_id'] = $unit->currentTenant->id;
            }
        }
        
        $maintenanceRequest = MaintenanceRequest::create($validated);
        
        // Log the creation
        Log::info('Maintenance request created', [
            'id' => $maintenanceRequest->id,
            'unit_id' => $maintenanceRequest->unit_id,
            'tenant_id' => $maintenanceRequest->tenant_id
        ]);
        
        // Trigger alert for new maintenance request
        $this->triggerNewRequestAlert($maintenanceRequest);
        
        if ($request->wantsJson()) {
            return response()->json($maintenanceRequest, Response::HTTP_CREATED);
        }
        
        return redirect()->route('maintenance-requests.show', $maintenanceRequest)
                         ->with('success', 'Maintenance request created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->load([
            'unit', 
            'unit.building',
            'unit.currentTenant', // Load current tenant for display
            'tenant', // Historical tenant who requested
            'maintenanceCategory', 
            'assignedVendor', 
            'assignedStaff',
            'repairs'
        ]);
        
        // Get timeline events
        $timeline = $this->getTimeline($maintenanceRequest);
        
        return view('maintenance-requests.show', compact('maintenanceRequest', 'timeline'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->load(['unit', 'unit.building', 'unit.currentTenant', 'tenant']);
        
        $buildings = Building::where('status', 'active')->orderBy('name')->get();
        $categories = MaintenanceCategory::orderBy('name')->get();
        $vendors = Vendor::orderBy('company_name')->get();
        $staff = User::where('is_active', true)->orderBy('name')->get();
        
        return view('maintenance-requests.edit', compact(
            'maintenanceRequest',
            'buildings', 
            'categories', 
            'vendors', 
            'staff'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $validated = $request->validate([
            'unit_id' => 'sometimes|required|exists:units,id',
            'tenant_id' => 'nullable|exists:tenants,id',
            'maintenance_category_id' => 'sometimes|required|exists:maintenance_categories,id',
            'title' => 'sometimes|required|string|max:200',
            'description' => 'sometimes|required|string',
            'priority' => 'sometimes|required|in:low,medium,high,emergency',
            'status' => 'sometimes|in:submitted,assigned,in_progress,completed,cancelled',
            'request_date' => 'sometimes|required|date',
            'scheduled_date' => 'nullable|date',
            'completion_date' => 'nullable|date',
            'estimated_cost' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'assigned_vendor_id' => 'nullable|exists:vendors,id',
            'assigned_staff_id' => 'nullable|exists:users,id',
            'resolution_notes' => 'nullable|string',
            'tenant_rating' => 'nullable|integer|min:1|max:5',
            'tenant_feedback' => 'nullable|string',
            'access_instructions' => 'nullable|string',
            'internal_notes' => 'nullable|string',
        ]);

        // Set completion date if status changed to completed
        if (isset($validated['status']) && $validated['status'] === 'completed' && !$maintenanceRequest->completion_date) {
            $validated['completion_date'] = now();
        }

        $oldStatus = $maintenanceRequest->status;
        $oldVendor = $maintenanceRequest->assigned_vendor_id;
        
        // CRITICAL FIX: If unit_id is being updated, we should NOT change tenant_id
        // The tenant_id is historical and should remain as the original requester
        // This ensures the request stays with the unit, not the tenant
        
        $maintenanceRequest->update($validated);
        
        // Log the update
        Log::info('Maintenance request updated', [
            'id' => $maintenanceRequest->id,
            'unit_id' => $maintenanceRequest->unit_id
        ]);
        
        // Trigger alerts for status changes
        if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
            $this->triggerStatusChangeAlert($maintenanceRequest, $oldStatus);
        }
        
        // Trigger alert for vendor assignment
        if (isset($validated['assigned_vendor_id']) && $validated['assigned_vendor_id'] !== $oldVendor) {
            $this->triggerAssignmentAlert($maintenanceRequest);
        }
        
        if ($request->wantsJson()) {
            return response()->json($maintenanceRequest);
        }
        
        return redirect()->route('maintenance-requests.show', $maintenanceRequest)
                         ->with('success', 'Maintenance request updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->delete();
        
        Log::info('Maintenance request deleted', ['id' => $maintenanceRequest->id]);
        
        if (request()->wantsJson()) {
            return response()->json(null, Response::HTTP_NO_CONTENT);
        }
        
        return redirect()->route('maintenance-requests.index')
                         ->with('success', 'Maintenance request deleted successfully.');
    }

    /**
     * Restore soft deleted maintenance request.
     */
    public function restore($id)
    {
        $request = MaintenanceRequest::withTrashed()->findOrFail($id);
        $request->restore();
        
        Log::info('Maintenance request restored', ['id' => $request->id]);
        
        if (request()->wantsJson()) {
            return response()->json($request);
        }
        
        return redirect()->route('maintenance-requests.show', $request)
                         ->with('success', 'Maintenance request restored successfully.');
    }

    /**
     * Show form to assign vendor.
     */
    public function assignForm(MaintenanceRequest $maintenanceRequest)
    {
        $vendors = Vendor::orderBy('company_name')->get();
        return view('maintenance-requests.assign', compact('maintenanceRequest', 'vendors'));
    }

    /**
     * Assign vendor to maintenance request.
     */
    public function assignVendor(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $validated = $request->validate([
            'assigned_vendor_id' => 'required|exists:vendors,id',
            'scheduled_date' => 'nullable|date',
            'estimated_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $updates = [
            'assigned_vendor_id' => $validated['assigned_vendor_id'],
            'status' => 'assigned',
        ];

        if (isset($validated['scheduled_date'])) {
            $updates['scheduled_date'] = $validated['scheduled_date'];
        }

        if (isset($validated['estimated_cost'])) {
            $updates['estimated_cost'] = $validated['estimated_cost'];
        }

        $maintenanceRequest->update($updates);
        
        // Log assignment
        Log::info('Vendor assigned to maintenance request', [
            'request_id' => $maintenanceRequest->id,
            'vendor_id' => $validated['assigned_vendor_id']
        ]);
        
        // Trigger assignment alert
        $this->triggerAssignmentAlert($maintenanceRequest);
        
        if ($request->wantsJson()) {
            return response()->json($maintenanceRequest);
        }
        
        return redirect()->route('maintenance-requests.show', $maintenanceRequest)
                         ->with('success', 'Vendor assigned successfully.');
    }

    /**
     * Show form to complete request.
     */
    public function completeForm(MaintenanceRequest $maintenanceRequest)
    {
        return view('maintenance-requests.complete', compact('maintenanceRequest'));
    }

    /**
     * Complete the maintenance request.
     */
    public function complete(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $validated = $request->validate([
            'actual_cost' => 'nullable|numeric|min:0',
            'resolution_notes' => 'required|string',
            'completion_date' => 'nullable|date',
        ]);

        $updates = [
            'status' => 'completed',
            'resolution_notes' => $validated['resolution_notes'],
            'completion_date' => $validated['completion_date'] ?? now(),
        ];

        if (isset($validated['actual_cost'])) {
            $updates['actual_cost'] = $validated['actual_cost'];
        }

        $maintenanceRequest->update($updates);
        
        Log::info('Maintenance request completed', ['id' => $maintenanceRequest->id]);
        
        if ($request->wantsJson()) {
            return response()->json($maintenanceRequest);
        }
        
        return redirect()->route('maintenance-requests.show', $maintenanceRequest)
                         ->with('success', 'Maintenance request marked as completed.');
    }

    /**
     * Cancel the maintenance request.
     */
    public function cancel(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string',
        ]);

        $maintenanceRequest->update([
            'status' => 'cancelled',
            'internal_notes' => $maintenanceRequest->internal_notes . "\n\nCancelled: " . $validated['cancellation_reason'],
        ]);
        
        Log::info('Maintenance request cancelled', ['id' => $maintenanceRequest->id]);
        
        if ($request->wantsJson()) {
            return response()->json($maintenanceRequest);
        }
        
        return redirect()->route('maintenance-requests.show', $maintenanceRequest)
                         ->with('success', 'Maintenance request cancelled.');
    }

    /**
     * Show feedback form.
     */
    public function feedback(MaintenanceRequest $maintenanceRequest)
    {
        return view('maintenance-requests.feedback', compact('maintenanceRequest'));
    }

    /**
     * Add tenant feedback and rating.
     */
    public function addFeedback(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $validated = $request->validate([
            'tenant_rating' => 'required|integer|min:1|max:5',
            'tenant_feedback' => 'required|string',
        ]);

        // Only allow feedback for completed requests
        if ($maintenanceRequest->status !== 'completed') {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Feedback can only be added for completed requests'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            return back()->with('error', 'Feedback can only be added for completed requests');
        }

        $maintenanceRequest->update([
            'tenant_rating' => $validated['tenant_rating'],
            'tenant_feedback' => $validated['tenant_feedback'],
            'feedback_date' => now(),
        ]);
        
        // Update vendor rating based on feedback
        if ($maintenanceRequest->assigned_vendor_id) {
            $this->updateVendorRating($maintenanceRequest->assignedVendor);
        }
        
        Log::info('Feedback added to maintenance request', [
            'request_id' => $maintenanceRequest->id,
            'rating' => $validated['tenant_rating']
        ]);
        
        if ($request->wantsJson()) {
            return response()->json($maintenanceRequest);
        }
        
        return redirect()->route('maintenance-requests.show', $maintenanceRequest)
                         ->with('success', 'Thank you for your feedback!');
    }

    /**
     * Update status of maintenance request (API/quick action).
     */
    public function updateStatus(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:submitted,assigned,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
            'actual_cost' => 'nullable|required_if:status,completed|numeric|min:0',
            'resolution_notes' => 'nullable|required_if:status,completed|string',
        ]);

        $oldStatus = $maintenanceRequest->status;
        $updates = ['status' => $validated['status']];

        // Set completion date if completing
        if ($validated['status'] === 'completed' && !$maintenanceRequest->completion_date) {
            $updates['completion_date'] = now();
        }

        // Set actual cost if provided
        if (isset($validated['actual_cost'])) {
            $updates['actual_cost'] = $validated['actual_cost'];
        }

        // Set resolution notes if provided
        if (isset($validated['resolution_notes'])) {
            $updates['resolution_notes'] = $validated['resolution_notes'];
        }

        $maintenanceRequest->update($updates);
        
        // Add note if provided
        if (isset($validated['notes'])) {
            // You could add to a timeline/notes table here
        }
        
        // Trigger status change alert
        $this->triggerStatusChangeAlert($maintenanceRequest, $oldStatus);
        
        if ($request->wantsJson()) {
            return response()->json($maintenanceRequest);
        }
        
        return redirect()->route('maintenance-requests.show', $maintenanceRequest)
                         ->with('success', 'Status updated successfully.');
    }

    /**
     * Get open maintenance requests.
     */
    public function open(Request $request)
    {
        $requests = MaintenanceRequest::with(['unit', 'unit.building', 'maintenanceCategory'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('priority', 'desc')
            ->orderBy('request_date', 'asc')
            ->paginate(15);
            
        return view('maintenance-requests.index', compact('requests'))
            ->with('filter', 'open');
    }

    /**
     * Get assigned maintenance requests.
     */
    public function assigned(Request $request)
    {
        $requests = MaintenanceRequest::with(['unit', 'unit.building', 'maintenanceCategory', 'assignedVendor'])
            ->where('status', 'assigned')
            ->orderBy('scheduled_date', 'asc')
            ->paginate(15);
            
        return view('maintenance-requests.index', compact('requests'))
            ->with('filter', 'assigned');
    }

    /**
     * Get in progress maintenance requests.
     */
    public function inProgress(Request $request)
    {
        $requests = MaintenanceRequest::with(['unit', 'unit.building', 'maintenanceCategory', 'assignedVendor'])
            ->where('status', 'in_progress')
            ->orderBy('updated_at', 'desc')
            ->paginate(15);
            
        return view('maintenance-requests.index', compact('requests'))
            ->with('filter', 'in_progress');
    }

    /**
     * Get completed maintenance requests.
     */
    public function completed(Request $request)
    {
        $requests = MaintenanceRequest::with(['unit', 'unit.building', 'maintenanceCategory', 'assignedVendor'])
            ->where('status', 'completed')
            ->orderBy('completion_date', 'desc')
            ->paginate(15);
            
        return view('maintenance-requests.index', compact('requests'))
            ->with('filter', 'completed');
    }

    /**
     * Get maintenance request calendar view.
     */
    public function calendar(Request $request)
    {
        $requests = MaintenanceRequest::with(['unit', 'unit.building'])
            ->whereNotNull('scheduled_date')
            ->orderBy('scheduled_date')
            ->get();
            
        return view('maintenance-requests.calendar', compact('requests'));
    }

    /**
     * Export maintenance requests.
     */
    public function export(Request $request)
    {
        $query = MaintenanceRequest::with(['unit', 'unit.building', 'tenant', 'maintenanceCategory', 'assignedVendor']);
        
        // Apply filters same as index
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('date_from')) {
            $query->where('request_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('request_date', '<=', $request->date_to);
        }
        
        $requests = $query->orderBy('request_date', 'desc')->get();
        
        // Generate CSV
        $filename = 'maintenance-requests-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($requests) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // Headers
            fputcsv($file, [
                'ID',
                'Title',
                'Building',
                'Unit',
                'Category',
                'Priority',
                'Status',
                'Request Date',
                'Scheduled Date',
                'Completion Date',
                'Estimated Cost',
                'Actual Cost',
                'Vendor',
                'Tenant Rating'
            ]);
            
            // Rows
            foreach ($requests as $request) {
                fputcsv($file, [
                    $request->id,
                    $request->title,
                    $request->unit->building->name ?? 'N/A',
                    $request->unit->unit_number ?? 'N/A',
                    $request->maintenanceCategory->name ?? 'N/A',
                    $request->priority,
                    $request->status,
                    $request->request_date->format('Y-m-d'),
                    $request->scheduled_date ? $request->scheduled_date->format('Y-m-d') : '',
                    $request->completion_date ? $request->completion_date->format('Y-m-d') : '',
                    $request->estimated_cost,
                    $request->actual_cost,
                    $request->assignedVendor->company_name ?? '',
                    $request->tenant_rating,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get maintenance request statistics.
     */
    public function statistics(Request $request)
    {
        $query = MaintenanceRequest::query();
        
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        
        if ($request->has('building_id')) {
            $query->whereHas('unit', function($q) use ($request) {
                $q->where('building_id', $request->building_id);
            });
        }
        
        if ($request->has('date_from')) {
            $query->where('request_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('request_date', '<=', $request->date_to);
        }

        // Get counts by status
        $byStatus = $query->clone()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Get counts by priority
        $byPriority = $query->clone()
            ->select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->get()
            ->pluck('count', 'priority');

        // Get counts by category
        $byCategory = $query->clone()
            ->with('maintenanceCategory')
            ->select('maintenance_category_id', DB::raw('count(*) as count'))
            ->groupBy('maintenance_category_id')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->maintenanceCategory->name ?? 'Unknown' => $item->count];
            });

        // Calculate average completion time
        $completedRequests = $query->clone()
            ->where('status', 'completed')
            ->whereNotNull('completion_date')
            ->whereNotNull('request_date')
            ->get();

        $avgCompletionTime = null;
        if ($completedRequests->isNotEmpty()) {
            $totalHours = 0;
            foreach ($completedRequests as $req) {
                $hours = $req->request_date->diffInHours($req->completion_date);
                $totalHours += $hours;
            }
            $avgCompletionTime = round($totalHours / $completedRequests->count(), 2);
        }

        $stats = [
            'total_requests' => $query->count(),
            'open_requests' => $query->clone()->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'completed_requests' => $query->clone()->where('status', 'completed')->count(),
            'cancelled_requests' => $query->clone()->where('status', 'cancelled')->count(),
            'average_completion_time' => $avgCompletionTime,
            'total_estimated_cost' => $query->clone()->sum('estimated_cost') ?? 0,
            'total_actual_cost' => $query->clone()->whereNotNull('actual_cost')->sum('actual_cost') ?? 0,
            'by_status' => $byStatus,
            'by_priority' => $byPriority,
            'by_category' => $byCategory,
            'average_rating' => $query->clone()->whereNotNull('tenant_rating')->avg('tenant_rating'),
        ];

        if ($request->wantsJson()) {
            return response()->json($stats);
        }

        return view('maintenance-requests.statistics', compact('stats'));
    }

    /**
     * Get overdue maintenance requests.
     */
    public function overdue(Request $request)
    {
        $requests = MaintenanceRequest::with(['unit', 'unit.building', 'maintenanceCategory'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereHas('maintenanceCategory', function($q) {
                $q->whereNotNull('sla_hours');
            })
            ->get()
            ->filter(function($request) {
                $slaHours = $request->maintenanceCategory->sla_hours;
                $hoursSinceRequest = $request->request_date->diffInHours(now());
                return $hoursSinceRequest > $slaHours;
            });

        if ($request->wantsJson()) {
            return response()->json($requests->values());
        }

        // Paginate the filtered collection
        $page = $request->get('page', 1);
        $perPage = 15;
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $requests->forPage($page, $perPage)->values(),
            $requests->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('maintenance-requests.index', ['requests' => $paginated])
            ->with('filter', 'overdue');
    }

    /**
     * Add note to maintenance request.
     */
    public function addNote(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $validated = $request->validate([
            'note' => 'required|string',
        ]);

        // Here you would typically create a Note model or add to a timeline
        // For now, we'll append to internal notes
        $maintenanceRequest->update([
            'internal_notes' => $maintenanceRequest->internal_notes . "\n\n[" . now() . "] " . $validated['note'],
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Note added successfully']);
        }

        return back()->with('success', 'Note added successfully.');
    }

    /**
     * Get timeline for maintenance request.
     */
    private function getTimeline(MaintenanceRequest $request)
    {
        // This should be replaced with actual timeline/activity log data
        // For now, return a basic timeline based on available data
        $timeline = collect([]);
        
        if ($request->created_at) {
            $timeline->push((object)[
                'created_at' => $request->created_at,
                'status' => 'created',
                'notes' => 'Request created'
            ]);
        }
        
        if ($request->assigned_date) {
            $timeline->push((object)[
                'created_at' => $request->assigned_date,
                'status' => 'assigned',
                'notes' => 'Assigned to ' . ($request->assignedVendor->company_name ?? 'vendor')
            ]);
        }
        
        if ($request->completion_date) {
            $timeline->push((object)[
                'created_at' => $request->completion_date,
                'status' => 'completed',
                'notes' => $request->resolution_notes ?? 'Request completed'
            ]);
        }
        
        return $timeline->sortByDesc('created_at');
    }

    /**
     * Calculate average completion time.
     */
    private function calculateAverageCompletionTime($query)
    {
        $completedRequests = $query->clone()
            ->where('status', 'completed')
            ->whereNotNull('completion_date')
            ->whereNotNull('request_date')
            ->get();

        if ($completedRequests->isEmpty()) {
            return null;
        }

        $totalHours = 0;
        foreach ($completedRequests as $request) {
            $hours = $request->request_date->diffInHours($request->completion_date);
            $totalHours += $hours;
        }

        return round($totalHours / $completedRequests->count(), 2);
    }

    /**
     * Update vendor rating based on feedback.
     */
    private function updateVendorRating($vendor)
    {
        if (!$vendor) return;
        
        $averageRating = MaintenanceRequest::where('assigned_vendor_id', $vendor->id)
            ->whereNotNull('tenant_rating')
            ->avg('tenant_rating');
            
        if ($averageRating) {
            $vendor->update(['rating' => round($averageRating, 2)]);
        }
    }

    /**
     * Trigger alert for new maintenance request.
     */
    private function triggerNewRequestAlert($maintenanceRequest)
    {
        // Implement alert logic here
        // This could create an Alert record or send notifications
        Log::info('New maintenance request alert triggered', ['id' => $maintenanceRequest->id]);
    }

    /**
     * Trigger alert for status change.
     */
    private function triggerStatusChangeAlert($maintenanceRequest, $oldStatus)
    {
        // Implement alert logic for status changes
        Log::info('Status change alert triggered', [
            'id' => $maintenanceRequest->id,
            'old_status' => $oldStatus,
            'new_status' => $maintenanceRequest->status
        ]);
    }

    /**
     * Trigger alert for vendor assignment.
     */
    private function triggerAssignmentAlert($maintenanceRequest)
    {
        // Implement alert logic for vendor assignment
        Log::info('Vendor assignment alert triggered', [
            'id' => $maintenanceRequest->id,
            'vendor_id' => $maintenanceRequest->assigned_vendor_id
        ]);
    }
}