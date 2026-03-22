<?php

namespace App\Http\Controllers;

use App\Models\ElectricityReading;
use App\Models\MeterReading;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ElectricityReadingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ElectricityReading::with(['meterReading', 'meterReading.utilityType', 'unit']);
        
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        
        if ($request->has('meter_reading_id')) {
            $query->where('meter_reading_id', $request->meter_reading_id);
        }
        
        if ($request->has('date_from')) {
            $query->whereHas('meterReading', function($q) use ($request) {
                $q->where('reading_date', '>=', $request->date_from);
            });
        }
        
        if ($request->has('date_to')) {
            $query->whereHas('meterReading', function($q) use ($request) {
                $q->where('reading_date', '<=', $request->date_to);
            });
        }
        
        if ($request->has('tariff_type')) {
            $query->where('tariff_type', $request->tariff_type);
        }
        
        if ($request->has('has_time_of_use')) {
            $query->where('has_time_of_use', $request->boolean('has_time_of_use'));
        }
        
        $readings = $query->orderBy('created_at', 'desc')->paginate(20);
        return response()->json($readings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'meter_reading_id' => 'required|exists:meter_readings,id',
            'peak_consumption_kwh' => 'nullable|numeric|min:0',
            'off_peak_consumption_kwh' => 'nullable|numeric|min:0',
            'shoulder_consumption_kwh' => 'nullable|numeric|min:0',
            'power_factor' => 'nullable|numeric|min:0|max:1',
            'maximum_demand_kw' => 'nullable|numeric|min:0',
            'time_of_peak_demand' => 'nullable|date_format:H:i',
            'reactive_power_kvar' => 'nullable|numeric|min:0',
            'apparent_power_kva' => 'nullable|numeric|min:0',
            'voltage_readings' => 'nullable|array',
            'voltage_readings.phase1' => 'nullable|numeric|min:0',
            'voltage_readings.phase2' => 'nullable|numeric|min:0',
            'voltage_readings.phase3' => 'nullable|numeric|min:0',
            'current_readings' => 'nullable|array',
            'current_readings.phase1' => 'nullable|numeric|min:0',
            'current_readings.phase2' => 'nullable|numeric|min:0',
            'current_readings.phase3' => 'nullable|numeric|min:0',
            'frequency_hz' => 'nullable|numeric|min:0',
            'meter_type' => 'nullable|in:smart,analog,digital',
            'tariff_type' => 'nullable|in:residential,commercial,industrial',
            'has_time_of_use' => 'boolean',
            'sub_meter_readings' => 'nullable|array',
            'consumption_kwh' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'demand_charge' => 'nullable|numeric|min:0',
        ]);

        // Get meter reading to get unit_id
        $meterReading = MeterReading::find($validated['meter_reading_id']);
        $validated['unit_id'] = $meterReading->unit_id;

        // Validate that consumption_kwh matches meter reading consumption
        $meterConsumption = $meterReading->consumption;
        $electricityConsumption = $validated['consumption_kwh'];
        
        // Allow small differences for conversion factors
        $difference = abs($meterConsumption - $electricityConsumption);
        if ($difference > 0.01) {
            return response()->json([
                'message' => 'Electricity consumption does not match meter reading consumption',
                'meter_consumption' => $meterConsumption,
                'electricity_consumption' => $electricityConsumption
            ], Response::HTTP_BAD_REQUEST);
        }

        // Calculate total from time-of-use components if provided
        if (isset($validated['peak_consumption_kwh']) || 
            isset($validated['off_peak_consumption_kwh']) || 
            isset($validated['shoulder_consumption_kwh'])) {
            
            $totalComponents = 
                ($validated['peak_consumption_kwh'] ?? 0) + 
                ($validated['off_peak_consumption_kwh'] ?? 0) + 
                ($validated['shoulder_consumption_kwh'] ?? 0);
            
            if (abs($totalComponents - $validated['consumption_kwh']) > 0.01) {
                return response()->json([
                    'message' => 'Time-of-use components do not sum to total consumption'
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $electricityReading = ElectricityReading::create($validated);
        return response()->json($electricityReading, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(ElectricityReading $electricityReading)
    {
        $electricityReading->load([
            'meterReading', 
            'meterReading.utilityType', 
            'meterReading.unit',
            'unit'
        ]);
        
        return response()->json($electricityReading);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ElectricityReading $electricityReading)
    {
        $validated = $request->validate([
            'meter_reading_id' => 'sometimes|required|exists:meter_readings,id',
            'peak_consumption_kwh' => 'nullable|numeric|min:0',
            'off_peak_consumption_kwh' => 'nullable|numeric|min:0',
            'shoulder_consumption_kwh' => 'nullable|numeric|min:0',
            'power_factor' => 'nullable|numeric|min:0|max:1',
            'maximum_demand_kw' => 'nullable|numeric|min:0',
            'time_of_peak_demand' => 'nullable|date_format:H:i',
            'reactive_power_kvar' => 'nullable|numeric|min:0',
            'apparent_power_kva' => 'nullable|numeric|min:0',
            'voltage_readings' => 'nullable|array',
            'voltage_readings.phase1' => 'nullable|numeric|min:0',
            'voltage_readings.phase2' => 'nullable|numeric|min:0',
            'voltage_readings.phase3' => 'nullable|numeric|min:0',
            'current_readings' => 'nullable|array',
            'current_readings.phase1' => 'nullable|numeric|min:0',
            'current_readings.phase2' => 'nullable|numeric|min:0',
            'current_readings.phase3' => 'nullable|numeric|min:0',
            'frequency_hz' => 'nullable|numeric|min:0',
            'meter_type' => 'nullable|in:smart,analog,digital',
            'tariff_type' => 'nullable|in:residential,commercial,industrial',
            'has_time_of_use' => 'boolean',
            'sub_meter_readings' => 'nullable|array',
            'consumption_kwh' => 'sometimes|required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'demand_charge' => 'nullable|numeric|min:0',
        ]);

        // Update unit_id if meter_reading_id changes
        if (isset($validated['meter_reading_id'])) {
            $meterReading = MeterReading::find($validated['meter_reading_id']);
            $validated['unit_id'] = $meterReading->unit_id;
        }

        $electricityReading->update($validated);
        return response()->json($electricityReading);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ElectricityReading $electricityReading)
    {
        $electricityReading->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get electricity consumption statistics.
     */
    public function statistics(Request $request)
    {
        $query = ElectricityReading::query();
        
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        
        if ($request->has('date_from')) {
            $query->whereHas('meterReading', function($q) use ($request) {
                $q->where('reading_date', '>=', $request->date_from);
            });
        }
        
        if ($request->has('date_to')) {
            $query->whereHas('meterReading', function($q) use ($request) {
                $q->where('reading_date', '<=', $request->date_to);
            });
        }

        $stats = [
            'total_consumption_kwh' => $query->sum('consumption_kwh'),
            'average_consumption_kwh' => $query->avg('consumption_kwh'),
            'total_cost' => $query->sum('cost'),
            'average_power_factor' => $query->avg('power_factor'),
            'peak_demand' => $query->max('maximum_demand_kw'),
            'total_demand_charge' => $query->sum('demand_charge'),
            'time_of_use_breakdown' => [
                'peak' => $query->sum('peak_consumption_kwh'),
                'off_peak' => $query->sum('off_peak_consumption_kwh'),
                'shoulder' => $query->sum('shoulder_consumption_kwh'),
            ],
            'carbon_emissions_kg' => $query->sum('consumption_kwh') * 0.92, // Average emissions factor
        ];

        return response()->json($stats);
    }

    /**
     * Get daily electricity consumption.
     */
    public function dailyConsumption(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after:date_from',
        ]);

        $dailyData = ElectricityReading::where('unit_id', $validated['unit_id'])
            ->whereHas('meterReading', function($q) use ($validated) {
                $q->whereBetween('reading_date', [$validated['date_from'], $validated['date_to']]);
            })
            ->select([
                DB::raw('DATE(meter_readings.reading_date) as date'),
                DB::raw('SUM(electricity_readings.consumption_kwh) as total_consumption'),
                DB::raw('AVG(electricity_readings.power_factor) as avg_power_factor'),
                DB::raw('MAX(electricity_readings.maximum_demand_kw) as peak_demand'),
            ])
            ->join('meter_readings', 'electricity_readings.meter_reading_id', '=', 'meter_readings.id')
            ->groupBy(DB::raw('DATE(meter_readings.reading_date)'))
            ->orderBy('date')
            ->get();

        return response()->json($dailyData);
    }

    /**
     * Get time-of-use analysis.
     */
    public function timeOfUseAnalysis(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after:date_from',
        ]);

        $analysis = ElectricityReading::where('unit_id', $validated['unit_id'])
            ->where('has_time_of_use', true)
            ->whereHas('meterReading', function($q) use ($validated) {
                $q->whereBetween('reading_date', [$validated['date_from'], $validated['date_to']]);
            })
            ->select([
                DB::raw('SUM(peak_consumption_kwh) as peak_consumption'),
                DB::raw('SUM(off_peak_consumption_kwh) as off_peak_consumption'),
                DB::raw('SUM(shoulder_consumption_kwh) as shoulder_consumption'),
                DB::raw('COUNT(*) as readings_count'),
            ])
            ->first();

        if (!$analysis) {
            return response()->json([
                'message' => 'No time-of-use data available for the specified period'
            ], Response::HTTP_NOT_FOUND);
        }

        $total = $analysis->peak_consumption + $analysis->off_peak_consumption + $analysis->shoulder_consumption;
        
        $result = [
            'total_consumption_kwh' => $total,
            'peak_percentage' => $total > 0 ? ($analysis->peak_consumption / $total) * 100 : 0,
            'off_peak_percentage' => $total > 0 ? ($analysis->off_peak_consumption / $total) * 100 : 0,
            'shoulder_percentage' => $total > 0 ? ($analysis->shoulder_consumption / $total) * 100 : 0,
            'readings_count' => $analysis->readings_count,
        ];

        return response()->json($result);
    }

    /**
     * Get power quality metrics.
     */
    public function powerQuality(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after:date_from',
        ]);

        $qualityMetrics = ElectricityReading::where('unit_id', $validated['unit_id'])
            ->whereHas('meterReading', function($q) use ($validated) {
                $q->whereBetween('reading_date', [$validated['date_from'], $validated['date_to']]);
            })
            ->select([
                DB::raw('AVG(power_factor) as avg_power_factor'),
                DB::raw('MIN(power_factor) as min_power_factor'),
                DB::raw('MAX(power_factor) as max_power_factor'),
                DB::raw('AVG(frequency_hz) as avg_frequency'),
                DB::raw('MIN(frequency_hz) as min_frequency'),
                DB::raw('MAX(frequency_hz) as max_frequency'),
                DB::raw('COUNT(*) as readings_count'),
            ])
            ->first();

        return response()->json($qualityMetrics);
    }

    /**
     * Calculate electricity bill estimate.
     */
    public function calculateEstimate(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'consumption_kwh' => 'required|numeric|min:0',
            'tariff_type' => 'required|in:residential,commercial,industrial',
            'has_time_of_use' => 'boolean',
            'peak_percentage' => 'nullable|numeric|min:0|max:100',
            'off_peak_percentage' => 'nullable|numeric|min:0|max:100',
            'shoulder_percentage' => 'nullable|numeric|min:0|max:100',
            'maximum_demand_kw' => 'nullable|numeric|min:0',
        ]);

        // This is a simplified calculation
        // In production, you would query your rates table
        
        $consumption = $validated['consumption_kwh'];
        $tariffType = $validated['tariff_type'];
        
        // Base rates (should come from database)
        $rates = [
            'residential' => [
                'base_rate' => 0.15, // $0.15 per kWh
                'demand_charge' => 0,
                'service_charge' => 10,
            ],
            'commercial' => [
                'base_rate' => 0.12,
                'demand_charge' => 15, // $15 per kW
                'service_charge' => 25,
            ],
            'industrial' => [
                'base_rate' => 0.10,
                'demand_charge' => 12,
                'service_charge' => 50,
            ]
        ];

        $rate = $rates[$tariffType];
        
        $energyCharge = $consumption * $rate['base_rate'];
        $demandCharge = isset($validated['maximum_demand_kw']) ? 
            $validated['maximum_demand_kw'] * $rate['demand_charge'] : 0;
        $serviceCharge = $rate['service_charge'];
        
        $total = $energyCharge + $demandCharge + $serviceCharge;
        
        // Apply time-of-use if specified
        if ($validated['has_time_of_use'] ?? false) {
            $peakRate = $rate['base_rate'] * 1.5; // Peak is 50% more
            $offPeakRate = $rate['base_rate'] * 0.7; // Off-peak is 30% less
            $shoulderRate = $rate['base_rate']; // Shoulder is same as base
            
            $peakPercentage = $validated['peak_percentage'] ?? 30;
            $offPeakPercentage = $validated['off_peak_percentage'] ?? 50;
            $shoulderPercentage = $validated['shoulder_percentage'] ?? 20;
            
            // Normalize percentages
            $totalPercentage = $peakPercentage + $offPeakPercentage + $shoulderPercentage;
            if (abs($totalPercentage - 100) > 0.01) {
                $peakPercentage = ($peakPercentage / $totalPercentage) * 100;
                $offPeakPercentage = ($offPeakPercentage / $totalPercentage) * 100;
                $shoulderPercentage = ($shoulderPercentage / $totalPercentage) * 100;
            }
            
            $energyCharge = 
                ($consumption * $peakPercentage / 100 * $peakRate) +
                ($consumption * $offPeakPercentage / 100 * $offPeakRate) +
                ($consumption * $shoulderPercentage / 100 * $shoulderRate);
                
            $total = $energyCharge + $demandCharge + $serviceCharge;
        }

        $estimate = [
            'consumption_kwh' => $consumption,
            'energy_charge' => round($energyCharge, 2),
            'demand_charge' => round($demandCharge, 2),
            'service_charge' => round($serviceCharge, 2),
            'total_estimate' => round($total, 2),
            'tariff_type' => $tariffType,
            'has_time_of_use' => $validated['has_time_of_use'] ?? false,
            'carbon_emissions_kg' => round($consumption * 0.92, 2),
        ];

        return response()->json($estimate);
    }
}