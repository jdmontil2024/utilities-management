<?php

namespace App\Http\Controllers;

use App\Models\FloorPlan;
use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FloorPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = FloorPlan::with('building');
        
        if ($request->has('building_id')) {
            $query->where('building_id', $request->building_id);
        }
        
        $floorPlans = $query->paginate(20);
        return response()->json($floorPlans);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'name' => 'required|string|max:255',
            'floor_number' => 'required|integer|min:0',
            'total_area' => 'required|numeric|min:0',
            'total_units' => 'required|integer|min:0',
            'layout_data' => 'nullable|array',
            'image_path' => 'nullable|string|max:500',
            'description' => 'nullable|string',
        ]);

        $floorPlan = FloorPlan::create($validated);
        return response()->json($floorPlan, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(FloorPlan $floorPlan)
    {
        $floorPlan->load(['building', 'units']);
        return response()->json($floorPlan);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FloorPlan $floorPlan)
    {
        $validated = $request->validate([
            'building_id' => 'sometimes|required|exists:buildings,id',
            'name' => 'sometimes|required|string|max:255',
            'floor_number' => 'sometimes|required|integer|min:0',
            'total_area' => 'sometimes|required|numeric|min:0',
            'total_units' => 'sometimes|required|integer|min:0',
            'layout_data' => 'nullable|array',
            'image_path' => 'nullable|string|max:500',
            'description' => 'nullable|string',
        ]);

        $floorPlan->update($validated);
        return response()->json($floorPlan);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FloorPlan $floorPlan)
    {
        $floorPlan->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get units for a specific floor plan.
     */
    public function units(FloorPlan $floorPlan)
    {
        $units = $floorPlan->units()->with(['leases', 'maintenanceRequests'])->paginate(20);
        return response()->json($units);
    }
}