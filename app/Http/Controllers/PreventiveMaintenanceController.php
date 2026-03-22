<?php

namespace App\Http\Controllers;

use App\Models\PreventiveMaintenance;
use App\Models\Unit;
use App\Models\MaintenanceCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;

class PreventiveMaintenanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PreventiveMaintenance::with(['unit', 'maintenanceCategory']);
        
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        
        if ($request->has('maintenance_category_id')) {
            $query->where('maintenance_category_id', $request->maintenance_category_id);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('frequency')) {
            $query->where('frequency', $request->frequency);
        }
        
        if ($request->has('overdue')) {
            $query->where('next_due_date', '<', now())
                  ->where('status', '!=', 'completed')
                  ->where('status', '!=', 'cancelled');
        }
        
        if ($request->has('upcoming')) {
            $query->where('next_due_date', '>=', now())
                  ->where('next_due_date', '<=', now()->addDays(30))
                  ->where('status', '=', 'scheduled');
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $preventiveMaintenances = $query->orderBy('next_due_date')->paginate(20);
        return response()->json($preventiveMaintenances);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'maintenance_category_id' => 'required|exists:maintenance_categories,id',
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'interval_days' => 'nullable|integer|min:1',
            'last_performed' => 'nullable|date',
            'next_due_date' => 'required|date',
            'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled,overdue',
            'estimated_duration_hours' => 'nullable|numeric|min:0',
            'estimated_cost' => 'nullable|numeric|min:0',
            'checklist' => 'nullable|array',
        ]);

        // Calculate next due date if not provided based on frequency
        if (!isset($validated['next_due_date']) && isset($validated['frequency'])) {
            $validated['next_due_date'] = $this->calculateNextDueDate(
                $validated['frequency'],
                $validated['interval_days'] ?? null,
                $validated['last_performed'] ?? null
            );
        }

        // Calculate interval days from frequency if not provided
        if (!isset($validated['interval_days']) && isset($validated['frequency'])) {
            $validated['interval_days'] = $this->getIntervalDays($validated['frequency']);
        }

        $validated['status'] = $validated['status'] ?? 'scheduled';
        
        $preventiveMaintenance = PreventiveMaintenance::create($validated);
        return response()->json($preventiveMaintenance, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(PreventiveMaintenance $preventiveMaintenance)
    {
        $preventiveMaintenance->load(['unit', 'maintenanceCategory']);
        return response()->json($preventiveMaintenance);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PreventiveMaintenance $preventiveMaintenance)
    {
        $validated = $request->validate([
            'unit_id' => 'sometimes|required|exists:units,id',
            'maintenance_category_id' => 'sometimes|required|exists:maintenance_categories,id',
            'title' => 'sometimes|required|string|max:200',
            'description' => 'sometimes|required|string',
            'frequency' => 'sometimes|required|in:daily,weekly,monthly,quarterly,yearly',
            'interval_days' => 'nullable|integer|min:1',
            'last_performed' => 'nullable|date',
            'next_due_date' => 'sometimes|required|date',
            'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled,overdue',
            'estimated_duration_hours' => 'nullable|numeric|min:0',
            'estimated_cost' => 'nullable|numeric|min:0',
            'checklist' => 'nullable|array',
        ]);

        $oldStatus = $preventiveMaintenance->status;
        $preventiveMaintenance->update($validated);
        
        // Handle status changes
        if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
            $this->handleStatusChange($preventiveMaintenance, $oldStatus, $validated['status']);
        }
        
        return response()->json($preventiveMaintenance);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PreventiveMaintenance $preventiveMaintenance)
    {
        $preventiveMaintenance->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Mark as completed.
     */
    public function markCompleted(Request $request, PreventiveMaintenance $preventiveMaintenance)
    {
        $validated = $request->validate([
            'actual_duration_hours' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'performed_by' => 'required|string|max:200',
            'notes' => 'nullable|string',
            'next_due_date' => 'nullable|date',
        ]);

        $updates = [
            'status' => 'completed',
            'last_performed' => now(),
        ];

        if (isset($validated['actual_duration_hours'])) {
            $updates['estimated_duration_hours'] = $validated['actual_duration_hours'];
        }

        if (isset($validated['actual_cost'])) {
            $updates['estimated_cost'] = $validated['actual_cost'];
        }

        // Calculate next due date
        $nextDueDate = $validated['next_due_date'] ?? $this->calculateNextDueDate(
            $preventiveMaintenance->frequency,
            $preventiveMaintenance->interval_days,
            now()
        );
        $updates['next_due_date'] = $nextDueDate;

        $preventiveMaintenance->update($updates);
        
        // Log completion
        if (isset($validated['notes'])) {
            // Create activity log or maintenance request
        }
        
        return response()->json($preventiveMaintenance);
    }

    /**
     * Reschedule preventive maintenance.
     */
    public function reschedule(Request $request, PreventiveMaintenance $preventiveMaintenance)
    {
        $validated = $request->validate([
            'new_due_date' => 'required|date',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $preventiveMaintenance->update([
            'next_due_date' => $validated['new_due_date'],
            'status' => 'scheduled',
        ]);
        
        // Log rescheduling
        // ActivityLog::create([...])
        
        return response()->json($preventiveMaintenance);
    }

    /**
     * Get overdue preventive maintenances.
     */
    public function overdue(Request $request)
    {
        $overdue = PreventiveMaintenance::with(['unit', 'maintenanceCategory'])
            ->where('next_due_date', '<', now())
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->orderBy('next_due_date')
            ->paginate(20);
            
        return response()->json($overdue);
    }

    /**
     * Get upcoming preventive maintenances.
     */
    public function upcoming(Request $request)
    {
        $days = $request->get('days', 30);
        
        $upcoming = PreventiveMaintenance::with(['unit', 'maintenanceCategory'])
            ->where('next_due_date', '>=', now())
            ->where('next_due_date', '<=', now()->addDays($days))
            ->where('status', '=', 'scheduled')
            ->orderBy('next_due_date')
            ->paginate(20);
            
        return response()->json($upcoming);
    }

    /**
     * Get preventive maintenance statistics.
     */
    public function statistics(Request $request)
    {
        $query = PreventiveMaintenance::query();
        
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        
        if ($request->has('date_from')) {
            $query->where('next_due_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('next_due_date', '<=', $request->date_to);
        }

        $stats = [
            'total_scheduled' => $query->clone()->where('status', 'scheduled')->count(),
            'total_completed' => $query->clone()->where('status', 'completed')->count(),
            'total_overdue' => $query->clone()
                ->where('next_due_date', '<', now())
                ->where('status', '!=', 'completed')
                ->where('status', '!=', 'cancelled')
                ->count(),
            'total_cancelled' => $query->clone()->where('status', 'cancelled')->count(),
            'completion_rate' => $this->calculateCompletionRate($query),
            'average_interval_days' => $query->clone()->avg('interval_days'),
            'total_estimated_cost' => $query->clone()->sum('estimated_cost'),
            'by_frequency' => $query->clone()
                ->select('frequency', \DB::raw('count(*) as count'))
                ->groupBy('frequency')
                ->get()
                ->pluck('count', 'frequency'),
            'by_category' => $query->clone()
                ->with('maintenanceCategory')
                ->select('maintenance_category_id', \DB::raw('count(*) as count'))
                ->groupBy('maintenance_category_id')
                ->get()
                ->mapWithKeys(function($item) {
                    return [$item->maintenanceCategory->name ?? 'Unknown' => $item->count];
                }),
        ];

        return response()->json($stats);
    }

    /**
     * Generate preventive maintenance schedule.
     */
    public function generateSchedule(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'maintenance_category_id' => 'nullable|exists:maintenance_categories,id',
        ]);

        $schedule = [];
        $currentDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        // Get all preventive maintenances for the unit
        $query = PreventiveMaintenance::where('unit_id', $validated['unit_id'])
            ->where('status', '!=', 'cancelled');
            
        if (isset($validated['maintenance_category_id'])) {
            $query->where('maintenance_category_id', $validated['maintenance_category_id']);
        }

        $preventiveMaintenances = $query->get();

        while ($currentDate <= $endDate) {
            $daySchedule = [
                'date' => $currentDate->toDateString(),
                'maintenances' => [],
            ];

            foreach ($preventiveMaintenances as $pm) {
                if ($this->isDueOnDate($pm, $currentDate)) {
                    $daySchedule['maintenances'][] = [
                        'id' => $pm->id,
                        'title' => $pm->title,
                        'category' => $pm->maintenanceCategory->name,
                        'status' => $pm->status,
                        'estimated_duration_hours' => $pm->estimated_duration_hours,
                        'estimated_cost' => $pm->estimated_cost,
                    ];
                }
            }

            if (!empty($daySchedule['maintenances'])) {
                $schedule[] = $daySchedule;
            }

            $currentDate->addDay();
        }

        return response()->json([
            'unit_id' => $validated['unit_id'],
            'period' => [
                'start' => $validated['start_date'],
                'end' => $validated['end_date'],
                'days' => Carbon::parse($validated['start_date'])->diffInDays($validated['end_date']) + 1,
            ],
            'schedule' => $schedule,
            'total_scheduled_maintenances' => collect($schedule)->pluck('maintenances')->flatten()->count(),
        ]);
    }

    /**
     * Calculate next due date.
     */
    private function calculateNextDueDate($frequency, $intervalDays = null, $lastPerformed = null)
    {
        $baseDate = $lastPerformed ? Carbon::parse($lastPerformed) : now();
        
        if ($intervalDays) {
            return $baseDate->addDays($intervalDays);
        }

        switch ($frequency) {
            case 'daily':
                return $baseDate->addDay();
            case 'weekly':
                return $baseDate->addWeek();
            case 'monthly':
                return $baseDate->addMonth();
            case 'quarterly':
                return $baseDate->addMonths(3);
            case 'yearly':
                return $baseDate->addYear();
            default:
                return $baseDate->addMonth();
        }
    }

    /**
     * Get interval days from frequency.
     */
    private function getIntervalDays($frequency)
    {
        switch ($frequency) {
            case 'daily':
                return 1;
            case 'weekly':
                return 7;
            case 'monthly':
                return 30;
            case 'quarterly':
                return 90;
            case 'yearly':
                return 365;
            default:
                return 30;
        }
    }

    /**
     * Handle status changes.
     */
    private function handleStatusChange($preventiveMaintenance, $oldStatus, $newStatus)
    {
        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            // Update last performed date
            $preventiveMaintenance->update(['last_performed' => now()]);
            
            // Calculate next due date
            $nextDueDate = $this->calculateNextDueDate(
                $preventiveMaintenance->frequency,
                $preventiveMaintenance->interval_days,
                now()
            );
            $preventiveMaintenance->update(['next_due_date' => $nextDueDate]);
            
            // Create maintenance request or activity log
            $this->createMaintenanceRequestFromCompleted($preventiveMaintenance);
        }
    }

    /**
     * Create maintenance request from completed preventive maintenance.
     */
    private function createMaintenanceRequestFromCompleted($preventiveMaintenance)
    {
        // This could create a maintenance request record
        // or an activity log entry
        
        // Example:
        // MaintenanceRequest::create([
        //     'unit_id' => $preventiveMaintenance->unit_id,
        //     'maintenance_category_id' => $preventiveMaintenance->maintenance_category_id,
        //     'title' => 'Preventive Maintenance: ' . $preventiveMaintenance->title,
        //     'description' => 'Completed preventive maintenance as scheduled.',
        //     'status' => 'completed',
        //     'request_date' => now(),
        //     'completion_date' => now(),
        // ]);
    }

    /**
     * Calculate completion rate.
     */
    private function calculateCompletionRate($query)
    {
        $total = $query->clone()->count();
        $completed = $query->clone()->where('status', 'completed')->count();
        
        return $total > 0 ? ($completed / $total) * 100 : 0;
    }

    /**
     * Check if preventive maintenance is due on date.
     */
    private function isDueOnDate($preventiveMaintenance, $date)
    {
        if ($preventiveMaintenance->status === 'completed' || 
            $preventiveMaintenance->status === 'cancelled') {
            return false;
        }

        $checkDate = Carbon::parse($date);
        $dueDate = Carbon::parse($preventiveMaintenance->next_due_date);
        
        return $checkDate->isSameDay($dueDate);
    }
}