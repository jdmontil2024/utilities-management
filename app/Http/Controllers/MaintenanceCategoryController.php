<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MaintenanceCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = MaintenanceCategory::all();
        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:maintenance_categories,name',
            'color_code' => 'nullable|string|max:7',
            'sla_hours' => 'nullable|integer|min:0',
            'average_cost' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $category = MaintenanceCategory::create($validated);
        return response()->json($category, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(MaintenanceCategory $maintenanceCategory)
    {
        $maintenanceCategory->load(['maintenanceRequests', 'preventiveMaintenances']);
        return response()->json($maintenanceCategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MaintenanceCategory $maintenanceCategory)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100|unique:maintenance_categories,name,' . $maintenanceCategory->id,
            'color_code' => 'nullable|string|max:7',
            'sla_hours' => 'nullable|integer|min:0',
            'average_cost' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $maintenanceCategory->update($validated);
        return response()->json($maintenanceCategory);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MaintenanceCategory $maintenanceCategory)
    {
        $maintenanceCategory->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get maintenance requests for this category.
     */
    public function maintenanceRequests(MaintenanceCategory $maintenanceCategory)
    {
        $requests = $maintenanceCategory->maintenanceRequests()
            ->with(['unit', 'tenant', 'assignedVendor'])
            ->paginate(20);
        
        return response()->json($requests);
    }

    /**
     * Get preventive maintenances for this category.
     */
    public function preventiveMaintenances(MaintenanceCategory $maintenanceCategory)
    {
        $preventive = $maintenanceCategory->preventiveMaintenances()
            ->with(['unit'])
            ->paginate(20);
        
        return response()->json($preventive);
    }

    /**
     * Get statistics for this category.
     */
    public function statistics(MaintenanceCategory $maintenanceCategory)
    {
        $stats = [
            'total_requests' => $maintenanceCategory->maintenanceRequests()->count(),
            'open_requests' => $maintenanceCategory->maintenanceRequests()
                ->where('status', '!=', 'completed')
                ->where('status', '!=', 'cancelled')
                ->count(),
            'average_completion_time' => $this->calculateAverageCompletionTime($maintenanceCategory),
            'preventive_maintenances' => $maintenanceCategory->preventiveMaintenances()->count(),
        ];

        return response()->json($stats);
    }

    private function calculateAverageCompletionTime($category)
    {
        $completedRequests = $category->maintenanceRequests()
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
}