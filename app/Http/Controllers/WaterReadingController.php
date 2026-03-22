<?php

namespace App\Http\Controllers;

use App\Models\WaterReading;
use App\Models\MeterReading;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class WaterReadingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = WaterReading::with(['meterReading', 'meterReading.utilityType', 'unit']);
        
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
        
        if ($request->has('water_source')) {
            $query->where('water_source', $request->water_source);
        }
        
        if ($request->has('leak_detected')) {
            $query->where('leak_detected', $request->boolean('leak_detected'));
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
            'cold_water_gallons' => 'required|numeric|min:0',
            'hot_water_gallons' => 'nullable|numeric|min:0',
            'total_water_gallons' => 'required|numeric|min:0',
            'water_pressure_psi' => 'nullable|numeric|min:0',
            'flow_rate_gpm' => 'nullable|numeric|min:0',
            'temperature_f' => 'nullable|numeric',
            'water_source' => 'nullable|in:municipal,well,harvested',
            'water_quality' => 'nullable|in:potable,filtered,untreated',
            'sub_meter_readings' => 'nullable|array',
            'has_leak_detection' => 'boolean',
            'leak_detected' => 'boolean',
            'leak_rate_gph' => 'nullable|numeric|min:0',
            'sewage_gallons' => 'nullable|numeric|min:0',
            'stormwater_gallons' => 'nullable|numeric|min:0',
            'recycled_water_gallons' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'sewer_charge' => 'nullable|numeric|min:0',
            'stormwater_charge' => 'nullable|numeric|min:0',
        ]);

        // Get meter reading to get unit_id
        $meterReading = MeterReading::find($validated['meter_reading_id']);
        $validated['unit_id'] = $meterReading->unit_id;

        // Validate that total matches cold + hot water
        $calculatedTotal = $validated['cold_water_gallons'] + ($validated['hot_water_gallons'] ?? 0);
        if (abs($calculatedTotal - $validated['total_water_gallons']) > 0.01) {
            return response()->json([
                'message' => 'Total water gallons must equal cold + hot water gallons',
                'calculated_total' => $calculatedTotal,
                'provided_total' => $validated['total_water_gallons']
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validate that total water gallons matches meter reading consumption
        $meterConsumption = $meterReading->consumption;
        $waterConsumption = $validated['total_water_gallons'];
        
        // Allow small differences for unit conversions
        $difference = abs($meterConsumption - $waterConsumption);
        if ($difference > 0.01) {
            return response()->json([
                'message' => 'Water consumption does not match meter reading consumption',
                'meter_consumption' => $meterConsumption,
                'water_consumption' => $waterConsumption
            ], Response::HTTP_BAD_REQUEST);
        }

        // Calculate sewage if not provided (typically 100% of water used)
        if (!isset($validated['sewage_gallons']) && isset($validated['total_water_gallons'])) {
            $validated['sewage_gallons'] = $validated['total_water_gallons'];
        }

        $waterReading = WaterReading::create($validated);
        return response()->json($waterReading, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(WaterReading $waterReading)
    {
        $waterReading->load([
            'meterReading', 
            'meterReading.utilityType', 
            'meterReading.unit',
            'unit'
        ]);
        
        return response()->json($waterReading);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WaterReading $waterReading)
    {
        $validated = $request->validate([
            'meter_reading_id' => 'sometimes|required|exists:meter_readings,id',
            'cold_water_gallons' => 'sometimes|required|numeric|min:0',
            'hot_water_gallons' => 'nullable|numeric|min:0',
            'total_water_gallons' => 'sometimes|required|numeric|min:0',
            'water_pressure_psi' => 'nullable|numeric|min:0',
            'flow_rate_gpm' => 'nullable|numeric|min:0',
            'temperature_f' => 'nullable|numeric',
            'water_source' => 'nullable|in:municipal,well,harvested',
            'water_quality' => 'nullable|in:potable,filtered,untreated',
            'sub_meter_readings' => 'nullable|array',
            'has_leak_detection' => 'boolean',
            'leak_detected' => 'boolean',
            'leak_rate_gph' => 'nullable|numeric|min:0',
            'sewage_gallons' => 'nullable|numeric|min:0',
            'stormwater_gallons' => 'nullable|numeric|min:0',
            'recycled_water_gallons' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'sewer_charge' => 'nullable|numeric|min:0',
            'stormwater_charge' => 'nullable|numeric|min:0',
        ]);

        // Update unit_id if meter_reading_id changes
        if (isset($validated['meter_reading_id'])) {
            $meterReading = MeterReading::find($validated['meter_reading_id']);
            $validated['unit_id'] = $meterReading->unit_id;
        }

        $waterReading->update($validated);
        return response()->json($waterReading);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WaterReading $waterReading)
    {
        $waterReading->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get water consumption statistics.
     */
    public function statistics(Request $request)
    {
        $query = WaterReading::query();
        
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
            'total_water_gallons' => $query->sum('total_water_gallons'),
            'cold_water_gallons' => $query->sum('cold_water_gallons'),
            'hot_water_gallons' => $query->sum('hot_water_gallons'),
            'sewage_gallons' => $query->sum('sewage_gallons'),
            'stormwater_gallons' => $query->sum('stormwater_gallons'),
            'recycled_water_gallons' => $query->sum('recycled_water_gallons'),
            'total_cost' => $query->sum('cost'),
            'sewer_charge_total' => $query->sum('sewer_charge'),
            'stormwater_charge_total' => $query->sum('stormwater_charge'),
            'leaks_detected' => $query->where('leak_detected', true)->count(),
            'average_flow_rate' => $query->avg('flow_rate_gpm'),
            'average_pressure' => $query->avg('water_pressure_psi'),
        ];

        return response()->json($stats);
    }

    /**
     * Get daily water consumption.
     */
    public function dailyConsumption(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after:date_from',
        ]);

        $dailyData = WaterReading::where('unit_id', $validated['unit_id'])
            ->whereHas('meterReading', function($q) use ($validated) {
                $q->whereBetween('reading_date', [$validated['date_from'], $validated['date_to']]);
            })
            ->select([
                DB::raw('DATE(meter_readings.reading_date) as date'),
                DB::raw('SUM(water_readings.total_water_gallons) as total_consumption'),
                DB::raw('SUM(water_readings.cold_water_gallons) as cold_water'),
                DB::raw('SUM(water_readings.hot_water_gallons) as hot_water'),
                DB::raw('AVG(water_readings.water_pressure_psi) as avg_pressure'),
                DB::raw('MAX(water_readings.flow_rate_gpm) as max_flow_rate'),
            ])
            ->join('meter_readings', 'water_readings.meter_reading_id', '=', 'meter_readings.id')
            ->groupBy(DB::raw('DATE(meter_readings.reading_date)'))
            ->orderBy('date')
            ->get();

        return response()->json($dailyData);
    }

    /**
     * Detect potential leaks.
     */
    public function detectLeaks(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'threshold_multiplier' => 'nullable|numeric|min:1|max:5',
        ]);

        $thresholdMultiplier = $validated['threshold_multiplier'] ?? 2;
        
        // Get recent water readings (last 30 days)
        $recentReadings = WaterReading::where('unit_id', $validated['unit_id'])
            ->whereHas('meterReading', function($q) {
                $q->where('reading_date', '>=', now()->subDays(30));
            })
            ->with(['meterReading'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($recentReadings->count() < 2) {
            return response()->json([
                'message' => 'Not enough data for leak detection'
            ], Response::HTTP_BAD_REQUEST);
        }

        $leaks = [];
        $previousReading = null;

        foreach ($recentReadings as $reading) {
            if ($previousReading) {
                $hoursBetween = $previousReading->meterReading->reading_date
                    ->diffInHours($reading->meterReading->reading_date);
                
                if ($hoursBetween > 0) {
                    $consumptionPerHour = $reading->total_water_gallons / $hoursBetween;
                    
                    // Check if consumption is unusually high
                    $averageConsumption = $recentReadings->avg('total_water_gallons');
                    $threshold = ($averageConsumption / 24) * $thresholdMultiplier; // Convert daily average to hourly
                    
                    if ($consumptionPerHour > $threshold) {
                        $leaks[] = [
                            'date' => $reading->meterReading->reading_date,
                            'consumption_gallons' => $reading->total_water_gallons,
                            'consumption_per_hour' => round($consumptionPerHour, 2),
                            'threshold' => round($threshold, 2),
                            'leak_probability' => 'high',
                            'estimated_leak_rate_gph' => round($consumptionPerHour - $threshold, 2),
                        ];
                    }
                }
            }
            $previousReading = $reading;
        }

        return response()->json([
            'total_readings_analyzed' => $recentReadings->count(),
            'potential_leaks_found' => count($leaks),
            'leaks' => $leaks,
            'recommendation' => count($leaks) > 0 ? 
                'Investigate potential leaks immediately' : 
                'No significant leaks detected',
        ]);
    }

    /**
     * Calculate water bill estimate.
     */
    public function calculateEstimate(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'total_water_gallons' => 'required|numeric|min:0',
            'cold_water_gallons' => 'nullable|numeric|min:0',
            'hot_water_gallons' => 'nullable|numeric|min:0',
            'water_source' => 'nullable|in:municipal,well,harvested',
            'has_sewer_service' => 'boolean',
            'has_stormwater_service' => 'boolean',
        ]);

        $consumption = $validated['total_water_gallons'];
        $waterSource = $validated['water_source'] ?? 'municipal';
        
        // Base rates (should come from database)
        $rates = [
            'municipal' => [
                'water_rate_per_gallon' => 0.005, // $0.005 per gallon
                'sewer_rate_per_gallon' => 0.004, // $0.004 per gallon
                'stormwater_rate_per_gallon' => 0.001, // $0.001 per gallon
                'service_fee' => 15,
            ],
            'well' => [
                'water_rate_per_gallon' => 0.002,
                'sewer_rate_per_gallon' => 0.004,
                'stormwater_rate_per_gallon' => 0.001,
                'service_fee' => 10,
            ],
            'harvested' => [
                'water_rate_per_gallon' => 0.001,
                'sewer_rate_per_gallon' => 0.004,
                'stormwater_rate_per_gallon' => 0.001,
                'service_fee' => 5,
            ]
        ];

        $rate = $rates[$waterSource];
        
        $waterCharge = $consumption * $rate['water_rate_per_gallon'];
        $sewerCharge = ($validated['has_sewer_service'] ?? true) ? 
            ($consumption * $rate['sewer_rate_per_gallon']) : 0;
        $stormwaterCharge = ($validated['has_stormwater_service'] ?? true) ? 
            ($consumption * $rate['stormwater_rate_per_gallon']) : 0;
        $serviceFee = $rate['service_fee'];
        
        $total = $waterCharge + $sewerCharge + $stormwaterCharge + $serviceFee;
        
        $estimate = [
            'total_water_gallons' => $consumption,
            'cold_water_gallons' => $validated['cold_water_gallons'] ?? null,
            'hot_water_gallons' => $validated['hot_water_gallons'] ?? null,
            'water_source' => $waterSource,
            'water_charge' => round($waterCharge, 2),
            'sewer_charge' => round($sewerCharge, 2),
            'stormwater_charge' => round($stormwaterCharge, 2),
            'service_fee' => round($serviceFee, 2),
            'total_estimate' => round($total, 2),
            'gallons_to_liters' => round($consumption * 3.78541, 2),
        ];

        return response()->json($estimate);
    }

    /**
     * Get water conservation tips.
     */
    public function conservationTips(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'period_days' => 'nullable|integer|min:1|max:365',
        ]);

        $periodDays = $validated['period_days'] ?? 30;
        
        // Get water consumption for the period
        $consumption = WaterReading::where('unit_id', $validated['unit_id'])
            ->whereHas('meterReading', function($q) use ($periodDays) {
                $q->where('reading_date', '>=', now()->subDays($periodDays));
            })
            ->sum('total_water_gallons');
        
        $dailyAverage = $periodDays > 0 ? $consumption / $periodDays : 0;
        
        $tips = [];
        
        if ($dailyAverage > 100) { // High consumption threshold
            $tips[] = [
                'category' => 'High Consumption',
                'tip' => 'Your daily water consumption is above average. Consider installing low-flow fixtures.',
                'potential_savings' => 'Up to 30% reduction possible',
                'priority' => 'high'
            ];
        }
        
        if ($dailyAverage > 0) {
            // General tips based on consumption
            $tips[] = [
                'category' => 'General Conservation',
                'tip' => 'Fix leaky faucets promptly - a dripping faucet can waste 20 gallons per day.',
                'potential_savings' => '20+ gallons daily',
                'priority' => 'medium'
            ];
            
            $tips[] = [
                'category' => 'Shower',
                'tip' => 'Reduce shower time by 2 minutes to save approximately 10 gallons per shower.',
                'potential_savings' => '300+ gallons monthly',
                'priority' => 'medium'
            ];
            
            $tips[] = [
                'category' => 'Lawn & Garden',
                'tip' => 'Water plants in the early morning or late evening to reduce evaporation.',
                'potential_savings' => 'Up to 25% reduction',
                'priority' => 'low'
            ];
        }

        return response()->json([
            'period_days' => $periodDays,
            'total_consumption_gallons' => $consumption,
            'daily_average_gallons' => round($dailyAverage, 2),
            'conservation_tips' => $tips,
            'total_tips' => count($tips),
        ]);
    }
}