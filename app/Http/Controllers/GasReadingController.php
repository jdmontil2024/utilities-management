<?php

namespace App\Http\Controllers;

use App\Models\GasReading;
use App\Models\MeterReading;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class GasReadingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = GasReading::with(['meterReading', 'meterReading.utilityType', 'unit']);
        
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
        
        if ($request->has('gas_type')) {
            $query->where('gas_type', $request->gas_type);
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
            'consumption_ccf' => 'required|numeric|min:0',
            'consumption_therms' => 'nullable|numeric|min:0',
            'pressure_psi' => 'nullable|numeric|min:0',
            'flow_rate_cfh' => 'nullable|numeric|min:0',
            'temperature_f' => 'nullable|numeric',
            'calorific_value_btu_per_cf' => 'nullable|numeric|min:0',
            'gas_type' => 'nullable|in:natural,propane,butane',
            'appliance_usage' => 'nullable|array',
            'has_leak_detection' => 'boolean',
            'leak_detected' => 'boolean',
            'carbon_monoxide_ppm' => 'nullable|numeric|min:0',
            'methane_percentage' => 'nullable|numeric|min:0|max:100',
            'cost' => 'nullable|numeric|min:0',
            'delivery_charge' => 'nullable|numeric|min:0',
            'storage_charge' => 'nullable|numeric|min:0',
        ]);

        // Get meter reading to get unit_id
        $meterReading = MeterReading::find($validated['meter_reading_id']);
        $validated['unit_id'] = $meterReading->unit_id;

        // Validate that consumption_ccf matches meter reading consumption
        $meterConsumption = $meterReading->consumption;
        $gasConsumption = $validated['consumption_ccf'];
        
        // Allow small differences for unit conversions
        $difference = abs($meterConsumption - $gasConsumption);
        if ($difference > 0.01) {
            return response()->json([
                'message' => 'Gas consumption does not match meter reading consumption',
                'meter_consumption' => $meterConsumption,
                'gas_consumption' => $gasConsumption
            ], Response::HTTP_BAD_REQUEST);
        }

        // Calculate therms if not provided but calorific value is available
        if (!isset($validated['consumption_therms']) && isset($validated['calorific_value_btu_per_cf'])) {
            // 1 therm = 100,000 BTU
            // BTU = CCF * calorific_value_btu_per_cf
            $btu = $validated['consumption_ccf'] * $validated['calorific_value_btu_per_cf'];
            $validated['consumption_therms'] = $btu / 100000;
        }

        // Safety check for carbon monoxide
        if (isset($validated['carbon_monoxide_ppm']) && $validated['carbon_monoxide_ppm'] > 9) {
            // CO levels above 9 ppm are concerning
            $validated['safety_alert'] = true;
        } else {
            $validated['safety_alert'] = false;
        }

        $gasReading = GasReading::create($validated);
        
        // Trigger safety alert if needed
        if ($validated['safety_alert'] ?? false) {
            $this->triggerSafetyAlert($gasReading);
        }
        
        return response()->json($gasReading, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(GasReading $gasReading)
    {
        $gasReading->load([
            'meterReading', 
            'meterReading.utilityType', 
            'meterReading.unit',
            'unit'
        ]);
        
        return response()->json($gasReading);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GasReading $gasReading)
    {
        $validated = $request->validate([
            'meter_reading_id' => 'sometimes|required|exists:meter_readings,id',
            'consumption_ccf' => 'sometimes|required|numeric|min:0',
            'consumption_therms' => 'nullable|numeric|min:0',
            'pressure_psi' => 'nullable|numeric|min:0',
            'flow_rate_cfh' => 'nullable|numeric|min:0',
            'temperature_f' => 'nullable|numeric',
            'calorific_value_btu_per_cf' => 'nullable|numeric|min:0',
            'gas_type' => 'nullable|in:natural,propane,butane',
            'appliance_usage' => 'nullable|array',
            'has_leak_detection' => 'boolean',
            'leak_detected' => 'boolean',
            'carbon_monoxide_ppm' => 'nullable|numeric|min:0',
            'methane_percentage' => 'nullable|numeric|min:0|max:100',
            'cost' => 'nullable|numeric|min:0',
            'delivery_charge' => 'nullable|numeric|min:0',
            'storage_charge' => 'nullable|numeric|min:0',
        ]);

        // Update unit_id if meter_reading_id changes
        if (isset($validated['meter_reading_id'])) {
            $meterReading = MeterReading::find($validated['meter_reading_id']);
            $validated['unit_id'] = $meterReading->unit_id;
        }

        // Safety check for carbon monoxide
        if (isset($validated['carbon_monoxide_ppm'])) {
            $validated['safety_alert'] = $validated['carbon_monoxide_ppm'] > 9;
        }

        $oldSafetyAlert = $gasReading->safety_alert;
        $gasReading->update($validated);
        
        // Trigger safety alert if newly detected
        if (($validated['safety_alert'] ?? false) && !$oldSafetyAlert) {
            $this->triggerSafetyAlert($gasReading);
        }
        
        return response()->json($gasReading);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GasReading $gasReading)
    {
        $gasReading->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get gas consumption statistics.
     */
    public function statistics(Request $request)
    {
        $query = GasReading::query();
        
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
            'total_consumption_ccf' => $query->sum('consumption_ccf'),
            'total_consumption_therms' => $query->sum('consumption_therms'),
            'total_cost' => $query->sum('cost'),
            'total_delivery_charge' => $query->sum('delivery_charge'),
            'total_storage_charge' => $query->sum('storage_charge'),
            'leaks_detected' => $query->where('leak_detected', true)->count(),
            'safety_alerts' => $query->where('safety_alert', true)->count(),
            'average_pressure' => $query->avg('pressure_psi'),
            'average_flow_rate' => $query->avg('flow_rate_cfh'),
            'average_calorific_value' => $query->avg('calorific_value_btu_per_cf'),
            'carbon_emissions_tons' => $query->sum('consumption_ccf') * 0.053 / 1000, // Convert kg to tons
        ];

        return response()->json($stats);
    }

    /**
     * Get daily gas consumption.
     */
    public function dailyConsumption(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after:date_from',
        ]);

        $dailyData = GasReading::where('unit_id', $validated['unit_id'])
            ->whereHas('meterReading', function($q) use ($validated) {
                $q->whereBetween('reading_date', [$validated['date_from'], $validated['date_to']]);
            })
            ->select([
                DB::raw('DATE(meter_readings.reading_date) as date'),
                DB::raw('SUM(gas_readings.consumption_ccf) as total_ccf'),
                DB::raw('SUM(gas_readings.consumption_therms) as total_therms'),
                DB::raw('AVG(gas_readings.pressure_psi) as avg_pressure'),
                DB::raw('MAX(gas_readings.flow_rate_cfh) as max_flow_rate'),
                DB::raw('MAX(gas_readings.carbon_monoxide_ppm) as max_co_level'),
            ])
            ->join('meter_readings', 'gas_readings.meter_reading_id', '=', 'meter_readings.id')
            ->groupBy(DB::raw('DATE(meter_readings.reading_date)'))
            ->orderBy('date')
            ->get();

        return response()->json($dailyData);
    }

    /**
     * Get appliance usage breakdown.
     */
    public function applianceUsage(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after:date_from',
        ]);

        $readings = GasReading::where('unit_id', $validated['unit_id'])
            ->whereHas('meterReading', function($q) use ($validated) {
                $q->whereBetween('reading_date', [$validated['date_from'], $validated['date_to']]);
            })
            ->whereNotNull('appliance_usage')
            ->get();

        $applianceUsage = [];
        $totalCcf = 0;

        foreach ($readings as $reading) {
            if (is_array($reading->appliance_usage)) {
                foreach ($reading->appliance_usage as $appliance => $usage) {
                    if (!isset($applianceUsage[$appliance])) {
                        $applianceUsage[$appliance] = 0;
                    }
                    $applianceUsage[$appliance] += $usage;
                    $totalCcf += $usage;
                }
            }
        }

        // Calculate percentages
        $breakdown = [];
        foreach ($applianceUsage as $appliance => $usage) {
            $percentage = $totalCcf > 0 ? ($usage / $totalCcf) * 100 : 0;
            $breakdown[] = [
                'appliance' => $appliance,
                'consumption_ccf' => round($usage, 2),
                'percentage' => round($percentage, 1),
                'estimated_cost' => round($usage * 1.50, 2), // Assuming $1.50 per CCF
            ];
        }

        // Sort by consumption descending
        usort($breakdown, function($a, $b) {
            return $b['consumption_ccf'] <=> $a['consumption_ccf'];
        });

        return response()->json([
            'period' => [
                'from' => $validated['date_from'],
                'to' => $validated['date_to'],
            ],
            'total_consumption_ccf' => round($totalCcf, 2),
            'appliance_breakdown' => $breakdown,
            'total_appliances' => count($breakdown),
        ]);
    }

    /**
     * Check gas safety.
     */
    public function safetyCheck(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'check_date' => 'nullable|date',
        ]);

        $checkDate = $validated['check_date'] ?? now();
        
        // Get recent gas readings (last 7 days)
        $recentReadings = GasReading::where('unit_id', $validated['unit_id'])
            ->whereHas('meterReading', function($q) use ($checkDate) {
                $q->where('reading_date', '>=', $checkDate->copy()->subDays(7));
            })
            ->with(['meterReading'])
            ->orderBy('created_at', 'desc')
            ->get();

        $safetyCheck = [
            'check_date' => $checkDate,
            'total_readings_analyzed' => $recentReadings->count(),
            'safety_issues' => [],
            'recommendations' => [],
            'overall_safety_status' => 'safe',
        ];

        foreach ($recentReadings as $reading) {
            // Check carbon monoxide levels
            if ($reading->carbon_monoxide_ppm && $reading->carbon_monoxide_ppm > 9) {
                $safetyCheck['safety_issues'][] = [
                    'date' => $reading->meterReading->reading_date,
                    'issue' => 'High carbon monoxide detected',
                    'level_ppm' => $reading->carbon_monoxide_ppm,
                    'severity' => $reading->carbon_monoxide_ppm > 35 ? 'critical' : 'warning',
                    'action' => 'Immediate ventilation required. Consider professional inspection.',
                ];
                
                if ($reading->carbon_monoxide_ppm > 35) {
                    $safetyCheck['overall_safety_status'] = 'critical';
                } elseif ($safetyCheck['overall_safety_status'] === 'safe') {
                    $safetyCheck['overall_safety_status'] = 'warning';
                }
            }

            // Check for leaks
            if ($reading->leak_detected) {
                $safetyCheck['safety_issues'][] = [
                    'date' => $reading->meterReading->reading_date,
                    'issue' => 'Gas leak detected',
                    'leak_rate_gph' => $reading->leak_rate_gph,
                    'severity' => 'critical',
                    'action' => 'EVACUATE AREA. Call emergency services immediately.',
                ];
                $safetyCheck['overall_safety_status'] = 'critical';
            }

            // Check methane percentage (for natural gas)
            if ($reading->gas_type === 'natural' && $reading->methane_percentage) {
                if ($reading->methane_percentage < 85 || $reading->methane_percentage > 95) {
                    $safetyCheck['safety_issues'][] = [
                        'date' => $reading->meterReading->reading_date,
                        'issue' => 'Abnormal methane content',
                        'methane_percentage' => $reading->methane_percentage,
                        'severity' => 'warning',
                        'action' => 'Contact gas supplier for quality check.',
                    ];
                    if ($safetyCheck['overall_safety_status'] === 'safe') {
                        $safetyCheck['overall_safety_status'] = 'warning';
                    }
                }
            }
        }

        // Add recommendations based on findings
        if (empty($safetyCheck['safety_issues'])) {
            $safetyCheck['recommendations'][] = [
                'priority' => 'low',
                'recommendation' => 'No immediate safety concerns detected. Continue regular monitoring.',
            ];
        } else {
            $safetyCheck['recommendations'][] = [
                'priority' => 'high',
                'recommendation' => 'Schedule professional gas system inspection immediately.',
            ];
            
            if ($safetyCheck['overall_safety_status'] === 'critical') {
                $safetyCheck['recommendations'][] = [
                    'priority' => 'critical',
                    'recommendation' => 'EVACUATE if smell of gas is present. Call emergency services.',
                ];
            }
        }

        return response()->json($safetyCheck);
    }

    /**
     * Calculate gas bill estimate.
     */
    public function calculateEstimate(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'consumption_ccf' => 'required|numeric|min:0',
            'consumption_therms' => 'nullable|numeric|min:0',
            'gas_type' => 'required|in:natural,propane,butane',
            'has_delivery_service' => 'boolean',
            'has_storage_service' => 'boolean',
            'calorific_value_btu_per_cf' => 'nullable|numeric|min:0',
        ]);

        $consumptionCcf = $validated['consumption_ccf'];
        $gasType = $validated['gas_type'];
        $calorificValue = $validated['calorific_value_btu_per_cf'] ?? 1030; // Default for natural gas
        
        // Calculate therms if not provided
        $consumptionTherms = $validated['consumption_therms'] ?? 
            (($consumptionCcf * $calorificValue) / 100000);

        // Base rates (should come from database)
        $rates = [
            'natural' => [
                'commodity_rate_per_therm' => 1.20,
                'delivery_charge_per_therm' => 0.30,
                'storage_charge_per_therm' => 0.10,
                'service_fee' => 12,
            ],
            'propane' => [
                'commodity_rate_per_therm' => 2.50,
                'delivery_charge_per_therm' => 0.50,
                'storage_charge_per_therm' => 0.25,
                'service_fee' => 15,
            ],
            'butane' => [
                'commodity_rate_per_therm' => 2.80,
                'delivery_charge_per_therm' => 0.60,
                'storage_charge_per_therm' => 0.30,
                'service_fee' => 18,
            ]
        ];

        $rate = $rates[$gasType];
        
        $commodityCharge = $consumptionTherms * $rate['commodity_rate_per_therm'];
        $deliveryCharge = ($validated['has_delivery_service'] ?? true) ? 
            ($consumptionTherms * $rate['delivery_charge_per_therm']) : 0;
        $storageCharge = ($validated['has_storage_service'] ?? true) ? 
            ($consumptionTherms * $rate['storage_charge_per_therm']) : 0;
        $serviceFee = $rate['service_fee'];
        
        $total = $commodityCharge + $deliveryCharge + $storageCharge + $serviceFee;
        
        // Calculate carbon emissions
        $carbonEmissionsKg = $consumptionCcf * 5.3; // Average emissions factor for natural gas
        
        $estimate = [
            'consumption_ccf' => round($consumptionCcf, 2),
            'consumption_therms' => round($consumptionTherms, 2),
            'gas_type' => $gasType,
            'calorific_value_btu_per_cf' => $calorificValue,
            'commodity_charge' => round($commodityCharge, 2),
            'delivery_charge' => round($deliveryCharge, 2),
            'storage_charge' => round($storageCharge, 2),
            'service_fee' => round($serviceFee, 2),
            'total_estimate' => round($total, 2),
            'carbon_emissions_kg' => round($carbonEmissionsKg, 2),
            'energy_content_btu' => round($consumptionCcf * $calorificValue, 0),
        ];

        return response()->json($estimate);
    }

    /**
     * Trigger safety alert.
     */
    private function triggerSafetyAlert(GasReading $gasReading)
    {
        // Implement safety alert logic
        // This could create an Alert record, send notifications, etc.
        
        $alertData = [
            'type' => 'safety',
            'severity' => 'critical',
            'title' => 'Gas Safety Alert',
            'message' => 'High carbon monoxide levels detected in Unit ' . 
                ($gasReading->unit->unit_number ?? 'Unknown'),
            'data' => [
                'carbon_monoxide_ppm' => $gasReading->carbon_monoxide_ppm,
                'reading_date' => $gasReading->meterReading->reading_date,
                'unit_id' => $gasReading->unit_id,
            ]
        ];
        
        // Create alert record (you need to implement this)
        // Alert::create($alertData);
        
        // Send notifications (email, SMS, etc.)
        // Notification::send($recipients, new GasSafetyAlert($gasReading));
    }
}