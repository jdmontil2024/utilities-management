<?php

namespace App\Http\Controllers;

use App\Models\UtilityType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UtilityTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $utilityTypes = UtilityType::all();
        return response()->json($utilityTypes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:utility_types,name',
            'unit' => 'required|string|max:50',
            'meter_type' => 'required|in:digital,analog,smart',
            'is_billable' => 'boolean',
            'color_code' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:100',
        ]);

        $utilityType = UtilityType::create($validated);
        return response()->json($utilityType, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(UtilityType $utilityType)
    {
        $utilityType->load(['meterReadings', 'rateSchedules', 'billItems']);
        return response()->json($utilityType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UtilityType $utilityType)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100|unique:utility_types,name,' . $utilityType->id,
            'unit' => 'sometimes|required|string|max:50',
            'meter_type' => 'sometimes|required|in:digital,analog,smart',
            'is_billable' => 'boolean',
            'color_code' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:100',
        ]);

        $utilityType->update($validated);
        return response()->json($utilityType);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UtilityType $utilityType)
    {
        $utilityType->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get statistics for utility type.
     */
    public function statistics(UtilityType $utilityType)
    {
        $stats = [
            'total_meter_readings' => $utilityType->meterReadings()->count(),
            'total_bill_items' => $utilityType->billItems()->count(),
            'rate_schedules' => $utilityType->rateSchedules()->count(),
            'average_consumption' => $utilityType->meterReadings()->avg('consumption'),
        ];

        return response()->json($stats);
    }
}