<?php

namespace App\Http\Controllers;

use App\Models\MeterReading;
use App\Models\Unit;
use App\Models\UtilityType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class MeterReadingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MeterReading::with(['unit', 'utilityType', 'reader']);
        
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        
        if ($request->has('utility_type_id')) {
            $query->where('utility_type_id', $request->utility_type_id);
        }
        
        if ($request->has('reading_date_from')) {
            $query->where('reading_date', '>=', $request->reading_date_from);
        }
        
        if ($request->has('reading_date_to')) {
            $query->where('reading_date', '<=', $request->reading_date_to);
        }
        
        if ($request->has('is_billed')) {
            $query->where('is_billed', $request->boolean('is_billed'));
        }
        
        if ($request->has('reading_type')) {
            $query->where('reading_type', $request->reading_type);
        }
        
        $readings = $query->orderBy('reading_date', 'desc')->paginate(20);
        return response()->json($readings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'utility_type_id' => 'required|exists:utility_types,id',
            'current_reading' => 'required|numeric|min:0',
            'previous_reading' => 'nullable|numeric|min:0',
            'reading_date' => 'required|date',
            'reading_type' => 'required|in:actual,estimated,corrected',
            'reader_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'meter_number' => 'nullable|string|max:100',
        ]);

        // Get previous reading if not provided
        if (empty($validated['previous_reading'])) {
            $previousReading = MeterReading::where('unit_id', $validated['unit_id'])
                ->where('utility_type_id', $validated['utility_type_id'])
                ->latest('reading_date')
                ->value('current_reading');
            
            $validated['previous_reading'] = $previousReading ?? 0;
        }

        // Calculate consumption
        $validated['consumption'] = $validated['current_reading'] - $validated['previous_reading'];
        
        if ($validated['consumption'] < 0) {
            return response()->json([
                'message' => 'Current reading cannot be less than previous reading'
            ], Response::HTTP_BAD_REQUEST);
        }

        $meterReading = MeterReading::create($validated);
        
        // Create specialized reading if data provided
        $this->createSpecializedReading($meterReading, $request);
        
        return response()->json($meterReading, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(MeterReading $meterReading)
    {
        $meterReading->load([
            'unit', 
            'utilityType', 
            'reader',
            'electricityReading',
            'waterReading',
            'gasReading',
            'billItems'
        ]);
        
        return response()->json($meterReading);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MeterReading $meterReading)
    {
        $validated = $request->validate([
            'unit_id' => 'sometimes|required|exists:units,id',
            'utility_type_id' => 'sometimes|required|exists:utility_types,id',
            'current_reading' => 'sometimes|required|numeric|min:0',
            'previous_reading' => 'nullable|numeric|min:0',
            'reading_date' => 'sometimes|required|date',
            'reading_type' => 'sometimes|required|in:actual,estimated,corrected',
            'reader_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'meter_number' => 'nullable|string|max:100',
            'is_billed' => 'boolean',
        ]);

        // Recalculate consumption if current reading changed
        if (isset($validated['current_reading'])) {
            $previousReading = $validated['previous_reading'] ?? $meterReading->previous_reading;
            $validated['consumption'] = $validated['current_reading'] - $previousReading;
            
            if ($validated['consumption'] < 0) {
                return response()->json([
                    'message' => 'Current reading cannot be less than previous reading'
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $meterReading->update($validated);
        
        // Update specialized reading if data provided
        $this->updateSpecializedReading($meterReading, $request);
        
        return response()->json($meterReading);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MeterReading $meterReading)
    {
        // Delete specialized readings first
        $meterReading->electricityReading()->delete();
        $meterReading->waterReading()->delete();
        $meterReading->gasReading()->delete();
        
        $meterReading->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Mark reading as billed.
     */
    public function markAsBilled(MeterReading $meterReading)
    {
        $meterReading->update(['is_billed' => true]);
        return response()->json($meterReading);
    }

    /**
     * Get readings for billing period.
     */
    public function forBillingPeriod(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'utility_type_id' => 'required|exists:utility_types,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
        ]);

        $readings = MeterReading::where('unit_id', $validated['unit_id'])
            ->where('utility_type_id', $validated['utility_type_id'])
            ->whereBetween('reading_date', [$validated['period_start'], $validated['period_end']])
            ->where('is_billed', false)
            ->orderBy('reading_date')
            ->get();

        return response()->json($readings);
    }

    /**
     * Get reading statistics.
     */
    public function statistics(Request $request)
    {
        $query = MeterReading::query();
        
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        
        if ($request->has('utility_type_id')) {
            $query->where('utility_type_id', $request->utility_type_id);
        }
        
        if ($request->has('date_from')) {
            $query->where('reading_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('reading_date', '<=', $request->date_to);
        }

        $stats = [
            'total_readings' => $query->count(),
            'total_consumption' => $query->sum('consumption'),
            'average_consumption' => $query->avg('consumption'),
            'estimated_readings' => $query->clone()->where('reading_type', 'estimated')->count(),
            'actual_readings' => $query->clone()->where('reading_type', 'actual')->count(),
            'billed_readings' => $query->clone()->where('is_billed', true)->count(),
            'unbilled_readings' => $query->clone()->where('is_billed', false)->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Import bulk meter readings.
     */
    public function import(Request $request)
    {
        $validated = $request->validate([
            'readings' => 'required|array',
            'readings.*.unit_id' => 'required|exists:units,id',
            'readings.*.utility_type_id' => 'required|exists:utility_types,id',
            'readings.*.current_reading' => 'required|numeric|min:0',
            'readings.*.reading_date' => 'required|date',
            'readings.*.reading_type' => 'required|in:actual,estimated,corrected',
            'readings.*.meter_number' => 'nullable|string|max:100',
        ]);

        $imported = [];
        $errors = [];

        DB::beginTransaction();
        
        try {
            foreach ($validated['readings'] as $index => $readingData) {
                try {
                    // Get previous reading
                    $previousReading = MeterReading::where('unit_id', $readingData['unit_id'])
                        ->where('utility_type_id', $readingData['utility_type_id'])
                        ->latest('reading_date')
                        ->value('current_reading');
                    
                    $readingData['previous_reading'] = $previousReading ?? 0;
                    $readingData['consumption'] = $readingData['current_reading'] - $readingData['previous_reading'];
                    
                    if ($readingData['consumption'] < 0) {
                        throw new \Exception('Current reading cannot be less than previous reading');
                    }
                    
                    $meterReading = MeterReading::create($readingData);
                    $imported[] = $meterReading;
                    
                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'error' => $e->getMessage(),
                        'data' => $readingData
                    ];
                }
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Import completed',
                'imported' => count($imported),
                'errors' => $errors,
                'data' => $imported
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Import failed',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create specialized reading based on utility type.
     */
    private function createSpecializedReading(MeterReading $meterReading, Request $request)
    {
        $utilityType = $meterReading->utilityType;
        
        switch (strtolower($utilityType->name)) {
            case 'electricity':
                if ($request->has('electricity_data')) {
                    $data = $request->validate([
                        'electricity_data.peak_consumption_kwh' => 'nullable|numeric|min:0',
                        'electricity_data.off_peak_consumption_kwh' => 'nullable|numeric|min:0',
                        'electricity_data.shoulder_consumption_kwh' => 'nullable|numeric|min:0',
                        'electricity_data.power_factor' => 'nullable|numeric|min:0|max:1',
                        'electricity_data.maximum_demand_kw' => 'nullable|numeric|min:0',
                        'electricity_data.time_of_peak_demand' => 'nullable|date_format:H:i',
                        'electricity_data.reactive_power_kvar' => 'nullable|numeric|min:0',
                        'electricity_data.apparent_power_kva' => 'nullable|numeric|min:0',
                        'electricity_data.voltage_readings' => 'nullable|array',
                        'electricity_data.current_readings' => 'nullable|array',
                        'electricity_data.frequency_hz' => 'nullable|numeric|min:0',
                        'electricity_data.meter_type' => 'nullable|in:smart,analog,digital',
                        'electricity_data.tariff_type' => 'nullable|in:residential,commercial,industrial',
                        'electricity_data.has_time_of_use' => 'boolean',
                        'electricity_data.sub_meter_readings' => 'nullable|array',
                        'electricity_data.consumption_kwh' => 'required|numeric|min:0',
                        'electricity_data.cost' => 'nullable|numeric|min:0',
                        'electricity_data.demand_charge' => 'nullable|numeric|min:0',
                    ]);
                    
                    $meterReading->electricityReading()->create(array_merge(
                        $data['electricity_data'],
                        ['unit_id' => $meterReading->unit_id]
                    ));
                }
                break;
                
            case 'water':
                if ($request->has('water_data')) {
                    $data = $request->validate([
                        'water_data.cold_water_gallons' => 'required|numeric|min:0',
                        'water_data.hot_water_gallons' => 'nullable|numeric|min:0',
                        'water_data.total_water_gallons' => 'required|numeric|min:0',
                        'water_data.water_pressure_psi' => 'nullable|numeric|min:0',
                        'water_data.flow_rate_gpm' => 'nullable|numeric|min:0',
                        'water_data.temperature_f' => 'nullable|numeric',
                        'water_data.water_source' => 'nullable|string',
                        'water_data.water_quality' => 'nullable|string',
                        'water_data.sub_meter_readings' => 'nullable|array',
                        'water_data.has_leak_detection' => 'boolean',
                        'water_data.leak_detected' => 'boolean',
                        'water_data.leak_rate_gph' => 'nullable|numeric|min:0',
                        'water_data.sewage_gallons' => 'nullable|numeric|min:0',
                        'water_data.stormwater_gallons' => 'nullable|numeric|min:0',
                        'water_data.recycled_water_gallons' => 'nullable|numeric|min:0',
                        'water_data.cost' => 'nullable|numeric|min:0',
                        'water_data.sewer_charge' => 'nullable|numeric|min:0',
                        'water_data.stormwater_charge' => 'nullable|numeric|min:0',
                    ]);
                    
                    $meterReading->waterReading()->create(array_merge(
                        $data['water_data'],
                        ['unit_id' => $meterReading->unit_id]
                    ));
                }
                break;
                
            case 'gas':
                if ($request->has('gas_data')) {
                    $data = $request->validate([
                        'gas_data.consumption_ccf' => 'required|numeric|min:0',
                        'gas_data.consumption_therms' => 'nullable|numeric|min:0',
                        'gas_data.pressure_psi' => 'nullable|numeric|min:0',
                        'gas_data.flow_rate_cfh' => 'nullable|numeric|min:0',
                        'gas_data.temperature_f' => 'nullable|numeric',
                        'gas_data.calorific_value_btu_per_cf' => 'nullable|numeric|min:0',
                        'gas_data.gas_type' => 'nullable|in:natural,propane,butane',
                        'gas_data.appliance_usage' => 'nullable|array',
                        'gas_data.has_leak_detection' => 'boolean',
                        'gas_data.leak_detected' => 'boolean',
                        'gas_data.carbon_monoxide_ppm' => 'nullable|numeric|min:0',
                        'gas_data.methane_percentage' => 'nullable|numeric|min:0|max:100',
                        'gas_data.cost' => 'nullable|numeric|min:0',
                        'gas_data.delivery_charge' => 'nullable|numeric|min:0',
                        'gas_data.storage_charge' => 'nullable|numeric|min:0',
                    ]);
                    
                    $meterReading->gasReading()->create(array_merge(
                        $data['gas_data'],
                        ['unit_id' => $meterReading->unit_id]
                    ));
                }
                break;
        }
    }

    /**
     * Update specialized reading.
     */
    private function updateSpecializedReading(MeterReading $meterReading, Request $request)
    {
        $utilityType = $meterReading->utilityType;
        
        switch (strtolower($utilityType->name)) {
            case 'electricity':
                if ($request->has('electricity_data')) {
                    $data = $request->validate([
                        'electricity_data.peak_consumption_kwh' => 'nullable|numeric|min:0',
                        'electricity_data.off_peak_consumption_kwh' => 'nullable|numeric|min:0',
                        'electricity_data.shoulder_consumption_kwh' => 'nullable|numeric|min:0',
                        'electricity_data.power_factor' => 'nullable|numeric|min:0|max:1',
                        'electricity_data.maximum_demand_kw' => 'nullable|numeric|min:0',
                        'electricity_data.time_of_peak_demand' => 'nullable|date_format:H:i',
                        'electricity_data.reactive_power_kvar' => 'nullable|numeric|min:0',
                        'electricity_data.apparent_power_kva' => 'nullable|numeric|min:0',
                        'electricity_data.voltage_readings' => 'nullable|array',
                        'electricity_data.current_readings' => 'nullable|array',
                        'electricity_data.frequency_hz' => 'nullable|numeric|min:0',
                        'electricity_data.meter_type' => 'nullable|in:smart,analog,digital',
                        'electricity_data.tariff_type' => 'nullable|in:residential,commercial,industrial',
                        'electricity_data.has_time_of_use' => 'boolean',
                        'electricity_data.sub_meter_readings' => 'nullable|array',
                        'electricity_data.consumption_kwh' => 'nullable|numeric|min:0',
                        'electricity_data.cost' => 'nullable|numeric|min:0',
                        'electricity_data.demand_charge' => 'nullable|numeric|min:0',
                    ]);
                    
                    if ($meterReading->electricityReading) {
                        $meterReading->electricityReading()->update($data['electricity_data']);
                    } else {
                        $meterReading->electricityReading()->create(array_merge(
                            $data['electricity_data'],
                            ['unit_id' => $meterReading->unit_id]
                        ));
                    }
                }
                break;
                
            // Similar for water and gas...
        }
    }
}