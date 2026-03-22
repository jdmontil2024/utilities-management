<?php

namespace App\Http\Controllers;

use App\Models\Consumption;
use App\Models\Unit;
use App\Models\UtilityType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConsumptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Consumption::with(['unit', 'utilityType']);
        
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        
        if ($request->has('utility_type_id')) {
            $query->where('utility_type_id', $request->utility_type_id);
        }
        
        if ($request->has('period_start_from')) {
            $query->where('period_start', '>=', $request->period_start_from);
        }
        
        if ($request->has('period_start_to')) {
            $query->where('period_start', '<=', $request->period_start_to);
        }
        
        if ($request->has('is_estimated')) {
            $query->where('is_estimated', $request->boolean('is_estimated'));
        }
        
        $consumptions = $query->orderBy('period_start', 'desc')->paginate(20);
        return response()->json($consumptions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'utility_type_id' => 'required|exists:utility_types,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'consumption' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'average_daily_consumption' => 'nullable|numeric|min:0',
            'peak_consumption' => 'nullable|numeric|min:0',
            'peak_date' => 'nullable|date',
            'daily_breakdown' => 'nullable|array',
            'is_estimated' => 'boolean',
        ]);

        // Calculate average daily consumption if not provided
        if (!isset($validated['average_daily_consumption'])) {
            $days = Carbon::parse($validated['period_start'])->diffInDays($validated['period_end']) + 1;
            $validated['average_daily_consumption'] = $validated['consumption'] / $days;
        }

        // Check for overlapping periods
        $overlap = Consumption::where('unit_id', $validated['unit_id'])
            ->where('utility_type_id', $validated['utility_type_id'])
            ->where(function($query) use ($validated) {
                $query->whereBetween('period_start', [$validated['period_start'], $validated['period_end']])
                      ->orWhereBetween('period_end', [$validated['period_start'], $validated['period_end']])
                      ->orWhere(function($q) use ($validated) {
                          $q->where('period_start', '<=', $validated['period_start'])
                            ->where('period_end', '>=', $validated['period_end']);
                      });
            })
            ->exists();
            
        if ($overlap) {
            return response()->json([
                'message' => 'Consumption record already exists for this period'
            ], Response::HTTP_CONFLICT);
        }

        $consumption = Consumption::create($validated);
        return response()->json($consumption, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Consumption $consumption)
    {
        $consumption->load(['unit', 'utilityType']);
        return response()->json($consumption);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Consumption $consumption)
    {
        $validated = $request->validate([
            'unit_id' => 'sometimes|required|exists:units,id',
            'utility_type_id' => 'sometimes|required|exists:utility_types,id',
            'period_start' => 'sometimes|required|date',
            'period_end' => 'sometimes|required|date|after:period_start',
            'consumption' => 'sometimes|required|numeric|min:0',
            'cost' => 'sometimes|required|numeric|min:0',
            'average_daily_consumption' => 'nullable|numeric|min:0',
            'peak_consumption' => 'nullable|numeric|min:0',
            'peak_date' => 'nullable|date',
            'daily_breakdown' => 'nullable|array',
            'is_estimated' => 'boolean',
        ]);

        // Recalculate average daily consumption if consumption or dates changed
        if (isset($validated['consumption']) || isset($validated['period_start']) || isset($validated['period_end'])) {
            $consumptionValue = $validated['consumption'] ?? $consumption->consumption;
            $periodStart = Carbon::parse($validated['period_start'] ?? $consumption->period_start);
            $periodEnd = Carbon::parse($validated['period_end'] ?? $consumption->period_end);
            $days = $periodStart->diffInDays($periodEnd) + 1;
            $validated['average_daily_consumption'] = $consumptionValue / $days;
        }

        $consumption->update($validated);
        return response()->json($consumption);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Consumption $consumption)
    {
        $consumption->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Generate consumption records from meter readings.
     */
    public function generateFromReadings(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'utility_type_id' => 'required|exists:utility_types,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
        ]);

        // Get meter readings for the period
        $meterReadings = DB::table('meter_readings')
            ->where('unit_id', $validated['unit_id'])
            ->where('utility_type_id', $validated['utility_type_id'])
            ->whereBetween('reading_date', [$validated['period_start'], $validated['period_end']])
            ->orderBy('reading_date')
            ->get();

        if ($meterReadings->isEmpty()) {
            return response()->json([
                'message' => 'No meter readings found for the specified period'
            ], Response::HTTP_NOT_FOUND);
        }

        // Calculate total consumption and cost
        $totalConsumption = $meterReadings->sum('consumption');
        
        // Get utility type for rate information
        $utilityType = UtilityType::find($validated['utility_type_id']);
        
        // Calculate cost (simplified - should use actual rates)
        $rate = $this->getUtilityRate($utilityType->id);
        $totalCost = $totalConsumption * $rate;
        
        // Calculate daily breakdown
        $dailyBreakdown = [];
        $dailyTotals = $meterReadings->groupBy(function($item) {
            return Carbon::parse($item->reading_date)->format('Y-m-d');
        });
        
        foreach ($dailyTotals as $date => $readings) {
            $dailyConsumption = collect($readings)->sum('consumption');
            $dailyBreakdown[$date] = [
                'consumption' => $dailyConsumption,
                'cost' => $dailyConsumption * $rate,
                'readings_count' => count($readings),
            ];
        }
        
        // Find peak consumption day
        $peakDay = null;
        $peakConsumption = 0;
        foreach ($dailyBreakdown as $date => $data) {
            if ($data['consumption'] > $peakConsumption) {
                $peakConsumption = $data['consumption'];
                $peakDay = $date;
            }
        }
        
        // Calculate average daily consumption
        $days = Carbon::parse($validated['period_start'])->diffInDays($validated['period_end']) + 1;
        $averageDaily = $totalConsumption / $days;

        // Create or update consumption record
        $consumption = Consumption::updateOrCreate(
            [
                'unit_id' => $validated['unit_id'],
                'utility_type_id' => $validated['utility_type_id'],
                'period_start' => $validated['period_start'],
                'period_end' => $validated['period_end'],
            ],
            [
                'consumption' => $totalConsumption,
                'cost' => $totalCost,
                'average_daily_consumption' => $averageDaily,
                'peak_consumption' => $peakConsumption,
                'peak_date' => $peakDay,
                'daily_breakdown' => $dailyBreakdown,
                'is_estimated' => false,
            ]
        );

        return response()->json($consumption, Response::HTTP_CREATED);
    }

    /**
     * Get consumption statistics.
     */
    public function statistics(Request $request)
    {
        $query = Consumption::query();
        
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        
        if ($request->has('utility_type_id')) {
            $query->where('utility_type_id', $request->utility_type_id);
        }
        
        if ($request->has('date_from')) {
            $query->where('period_start', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('period_start', '<=', $request->date_to);
        }

        $stats = [
            'total_records' => $query->count(),
            'total_consumption' => $query->sum('consumption'),
            'total_cost' => $query->sum('cost'),
            'average_consumption_per_period' => $query->avg('consumption'),
            'average_cost_per_period' => $query->avg('cost'),
            'estimated_records' => $query->clone()->where('is_estimated', true)->count(),
            'actual_records' => $query->clone()->where('is_estimated', false)->count(),
            'consumption_by_utility' => $query->clone()
                ->with('utilityType')
                ->select('utility_type_id', DB::raw('SUM(consumption) as total_consumption, SUM(cost) as total_cost'))
                ->groupBy('utility_type_id')
                ->get()
                ->mapWithKeys(function($item) {
                    return [$item->utilityType->name ?? 'Unknown' => [
                        'consumption' => $item->total_consumption,
                        'cost' => $item->total_cost,
                    ]];
                }),
        ];

        return response()->json($stats);
    }

    /**
     * Compare consumption between periods.
     */
    public function comparePeriods(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'utility_type_id' => 'required|exists:utility_types,id',
            'current_period_start' => 'required|date',
            'current_period_end' => 'required|date|after:current_period_start',
            'previous_period_start' => 'required|date',
            'previous_period_end' => 'required|date|after:previous_period_start',
        ]);

        // Get current period consumption
        $current = Consumption::where('unit_id', $validated['unit_id'])
            ->where('utility_type_id', $validated['utility_type_id'])
            ->where('period_start', '>=', $validated['current_period_start'])
            ->where('period_end', '<=', $validated['current_period_end'])
            ->first();

        // Get previous period consumption
        $previous = Consumption::where('unit_id', $validated['unit_id'])
            ->where('utility_type_id', $validated['utility_type_id'])
            ->where('period_start', '>=', $validated['previous_period_start'])
            ->where('period_end', '<=', $validated['previous_period_end'])
            ->first();

        if (!$current || !$previous) {
            return response()->json([
                'message' => 'Consumption data not available for one or both periods'
            ], Response::HTTP_NOT_FOUND);
        }

        $currentDays = Carbon::parse($current->period_start)->diffInDays($current->period_end) + 1;
        $previousDays = Carbon::parse($previous->period_start)->diffInDays($previous->period_end) + 1;

        $currentDaily = $current->consumption / $currentDays;
        $previousDaily = $previous->consumption / $previousDays;

        $consumptionChange = $current->consumption - $previous->consumption;
        $consumptionChangePercent = $previous->consumption > 0 ? 
            ($consumptionChange / $previous->consumption) * 100 : 0;

        $costChange = $current->cost - $previous->cost;
        $costChangePercent = $previous->cost > 0 ? 
            ($costChange / $previous->cost) * 100 : 0;

        $dailyChange = $currentDaily - $previousDaily;
        $dailyChangePercent = $previousDaily > 0 ? 
            ($dailyChange / $previousDaily) * 100 : 0;

        $comparison = [
            'current_period' => [
                'period' => $current->period_start->format('M d, Y') . ' - ' . $current->period_end->format('M d, Y'),
                'days' => $currentDays,
                'total_consumption' => $current->consumption,
                'total_cost' => $current->cost,
                'daily_average' => $currentDaily,
                'peak_consumption' => $current->peak_consumption,
                'peak_date' => $current->peak_date,
                'is_estimated' => $current->is_estimated,
            ],
            'previous_period' => [
                'period' => $previous->period_start->format('M d, Y') . ' - ' . $previous->period_end->format('M d, Y'),
                'days' => $previousDays,
                'total_consumption' => $previous->consumption,
                'total_cost' => $previous->cost,
                'daily_average' => $previousDaily,
                'peak_consumption' => $previous->peak_consumption,
                'peak_date' => $previous->peak_date,
                'is_estimated' => $previous->is_estimated,
            ],
            'comparison' => [
                'consumption_change' => [
                    'absolute' => $consumptionChange,
                    'percentage' => round($consumptionChangePercent, 2),
                    'trend' => $consumptionChange > 0 ? 'increase' : ($consumptionChange < 0 ? 'decrease' : 'stable'),
                ],
                'cost_change' => [
                    'absolute' => $costChange,
                    'percentage' => round($costChangePercent, 2),
                    'trend' => $costChange > 0 ? 'increase' : ($costChange < 0 ? 'decrease' : 'stable'),
                ],
                'daily_average_change' => [
                    'absolute' => $dailyChange,
                    'percentage' => round($dailyChangePercent, 2),
                    'trend' => $dailyChange > 0 ? 'increase' : ($dailyChange < 0 ? 'decrease' : 'stable'),
                ],
            ],
            'recommendations' => $this->generateConsumptionRecommendations(
                $consumptionChangePercent,
                $costChangePercent,
                $currentDaily
            ),
        ];

        return response()->json($comparison);
    }

    /**
     * Get consumption trends.
     */
    public function trends(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'utility_type_id' => 'required|exists:utility_types,id',
            'periods' => 'nullable|integer|min:3|max:24',
        ]);

        $periods = $validated['periods'] ?? 12;
        
        $trends = Consumption::where('unit_id', $validated['unit_id'])
            ->where('utility_type_id', $validated['utility_type_id'])
            ->where('period_end', '>=', now()->subMonths($periods))
            ->orderBy('period_start')
            ->get()
            ->map(function($consumption) {
                return [
                    'period' => $consumption->period_start->format('M Y'),
                    'consumption' => $consumption->consumption,
                    'cost' => $consumption->cost,
                    'daily_average' => $consumption->average_daily_consumption,
                    'peak_consumption' => $consumption->peak_consumption,
                    'is_estimated' => $consumption->is_estimated,
                ];
            });

        // Calculate trend line (simple linear regression)
        $trendData = $this->calculateTrendLine($trends->pluck('consumption')->toArray());

        return response()->json([
            'unit_id' => $validated['unit_id'],
            'utility_type_id' => $validated['utility_type_id'],
            'periods_analyzed' => $trends->count(),
            'trend_data' => $trends,
            'trend_analysis' => $trendData,
            'seasonal_pattern' => $this->detectSeasonalPattern($trends),
        ]);
    }

    /**
     * Forecast future consumption.
     */
    public function forecast(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'utility_type_id' => 'required|exists:utility_types,id',
            'forecast_periods' => 'required|integer|min:1|max:12',
            'include_seasonality' => 'boolean',
        ]);

        // Get historical data
        $historical = Consumption::where('unit_id', $validated['unit_id'])
            ->where('utility_type_id', $validated['utility_type_id'])
            ->where('period_end', '>=', now()->subMonths(24))
            ->orderBy('period_start')
            ->get();

        if ($historical->count() < 6) {
            return response()->json([
                'message' => 'Insufficient historical data for forecasting'
            ], Response::HTTP_BAD_REQUEST);
        }

        $forecast = $this->generateForecast(
            $historical->pluck('consumption')->toArray(),
            $validated['forecast_periods'],
            $validated['include_seasonality'] ?? false
        );

        return response()->json([
            'unit_id' => $validated['unit_id'],
            'utility_type_id' => $validated['utility_type_id'],
            'historical_periods' => $historical->count(),
            'forecast_periods' => $validated['forecast_periods'],
            'forecast' => $forecast,
            'confidence_interval' => '±15%', // Simplified
            'assumptions' => [
                'Historical trends continue',
                'No major changes in occupancy or usage patterns',
                'Seasonal patterns persist (if included)',
            ],
        ]);
    }

    /**
     * Get utility rate.
     */
    private function getUtilityRate($utilityTypeId)
    {
        // This should query your rates table
        // Default rates for common utilities
        $defaultRates = [
            1 => 0.15, // Electricity: $0.15 per kWh
            2 => 0.005, // Water: $0.005 per gallon
            3 => 1.50, // Gas: $1.50 per CCF
            4 => 0.10, // Sewer: $0.10 per unit
            5 => 0.05, // Trash: $0.05 per unit
        ];

        return $defaultRates[$utilityTypeId] ?? 0.10;
    }

    /**
     * Generate consumption recommendations.
     */
    private function generateConsumptionRecommendations($consumptionChangePercent, $costChangePercent, $currentDaily)
    {
        $recommendations = [];

        if ($consumptionChangePercent > 20) {
            $recommendations[] = [
                'priority' => 'high',
                'category' => 'Consumption Increase',
                'recommendation' => 'Significant consumption increase detected. Investigate for potential leaks or abnormal usage.',
                'action' => 'Check for leaks, review appliance usage patterns.',
            ];
        } elseif ($consumptionChangePercent > 10) {
            $recommendations[] = [
                'priority' => 'medium',
                'category' => 'Consumption Increase',
                'recommendation' => 'Moderate consumption increase. Monitor closely for trends.',
                'action' => 'Review recent changes in occupancy or appliance usage.',
            ];
        } elseif ($consumptionChangePercent < -20) {
            $recommendations[] = [
                'priority' => 'low',
                'category' => 'Consumption Decrease',
                'recommendation' => 'Significant consumption decrease. Verify meter readings are accurate.',
                'action' => 'Confirm meter readings and check for meter issues.',
            ];
        }

        if ($costChangePercent > $consumptionChangePercent + 10) {
            $recommendations[] = [
                'priority' => 'high',
                'category' => 'Cost Increase',
                'recommendation' => 'Cost increase exceeds consumption increase. Check for rate changes or additional fees.',
                'action' => 'Review utility bills for rate changes or new charges.',
            ];
        }

        // Add general conservation tips
        $recommendations[] = [
            'priority' => 'low',
            'category' => 'Conservation',
            'recommendation' => 'Regular maintenance of appliances can improve efficiency.',
            'action' => 'Schedule annual appliance maintenance.',
        ];

        return $recommendations;
    }

    /**
     * Calculate trend line.
     */
    private function calculateTrendLine($data)
    {
        if (count($data) < 2) {
            return null;
        }

        $n = count($data);
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumX += $i;
            $sumY += $data[$i];
            $sumXY += $i * $data[$i];
            $sumX2 += $i * $i;
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        // Calculate R-squared
        $ssTotal = 0;
        $ssResidual = 0;
        $meanY = $sumY / $n;

        for ($i = 0; $i < $n; $i++) {
            $ssTotal += pow($data[$i] - $meanY, 2);
            $predicted = $intercept + $slope * $i;
            $ssResidual += pow($data[$i] - $predicted, 2);
        }

        $rSquared = $ssTotal > 0 ? 1 - ($ssResidual / $ssTotal) : 1;

        return [
            'slope' => $slope,
            'intercept' => $intercept,
            'r_squared' => $rSquared,
            'trend' => $slope > 0.1 ? 'increasing' : ($slope < -0.1 ? 'decreasing' : 'stable'),
            'strength' => $rSquared > 0.7 ? 'strong' : ($rSquared > 0.3 ? 'moderate' : 'weak'),
        ];
    }

    /**
     * Detect seasonal pattern.
     */
    private function detectSeasonalPattern($trends)
    {
        if ($trends->count() < 12) {
            return 'Insufficient data for seasonal analysis';
        }

        $monthlyAverages = [];
        $monthlyCounts = [];

        foreach ($trends as $trend) {
            $month = Carbon::parse($trend['period'])->month;
            if (!isset($monthlyAverages[$month])) {
                $monthlyAverages[$month] = 0;
                $monthlyCounts[$month] = 0;
            }
            $monthlyAverages[$month] += $trend['consumption'];
            $monthlyCounts[$month]++;
        }

        // Calculate average for each month
        foreach ($monthlyAverages as $month => $total) {
            if ($monthlyCounts[$month] > 0) {
                $monthlyAverages[$month] = $total / $monthlyCounts[$month];
            }
        }

        // Find peak and trough months
        $peakMonth = array_search(max($monthlyAverages), $monthlyAverages);
        $troughMonth = array_search(min($monthlyAverages), $monthlyAverages);

        $monthNames = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        return [
            'peak_month' => $monthNames[$peakMonth] ?? 'Unknown',
            'trough_month' => $monthNames[$troughMonth] ?? 'Unknown',
            'seasonal_variation' => round((max($monthlyAverages) - min($monthlyAverages)) / max($monthlyAverages) * 100, 1) . '%',
            'pattern' => $this->identifySeasonalPatternType($peakMonth, $troughMonth),
        ];
    }

    /**
     * Identify seasonal pattern type.
     */
    private function identifySeasonalPatternType($peakMonth, $troughMonth)
    {
        // Simple pattern identification
        if (in_array($peakMonth, [12, 1, 2]) && in_array($troughMonth, [6, 7, 8])) {
            return 'Winter peak (heating season)';
        } elseif (in_array($peakMonth, [6, 7, 8]) && in_array($troughMonth, [12, 1, 2])) {
            return 'Summer peak (cooling season)';
        } else {
            return 'Mixed or unclear seasonal pattern';
        }
    }

    /**
     * Generate forecast.
     */
    private function generateForecast($historicalData, $periods, $includeSeasonality)
    {
        // Simple moving average forecast
        $n = count($historicalData);
        $forecast = [];

        // Use last 3 periods for simple average
        $recent = array_slice($historicalData, -3);
        $baseForecast = array_sum($recent) / count($recent);

        for ($i = 1; $i <= $periods; $i++) {
            $forecastValue = $baseForecast;
            
            // Add simple seasonality if requested
            if ($includeSeasonality && $n >= 12) {
                $monthIndex = ($n + $i - 1) % 12;
                $monthlyPattern = $this->calculateMonthlyPattern($historicalData);
                if (isset($monthlyPattern[$monthIndex])) {
                    $forecastValue *= $monthlyPattern[$monthIndex];
                }
            }

            // Add small random variation
            $variation = mt_rand(-5, 5) / 100; // ±5%
            $forecastValue *= (1 + $variation);

            $forecast[] = [
                'period' => now()->addMonths($i)->format('M Y'),
                'forecasted_consumption' => round($forecastValue, 2),
                'forecasted_cost' => round($forecastValue * $this->getUtilityRate(1), 2), // Using electricity rate as default
                'confidence' => 'medium',
            ];
        }

        return $forecast;
    }

    /**
     * Calculate monthly pattern.
     */
    private function calculateMonthlyPattern($historicalData)
    {
        if (count($historicalData) < 12) {
            return array_fill(0, 12, 1.0);
        }

        // Group by month and calculate averages
        $monthlyTotals = array_fill(0, 12, 0);
        $monthlyCounts = array_fill(0, 12, 0);

        foreach ($historicalData as $index => $value) {
            $month = $index % 12;
            $monthlyTotals[$month] += $value;
            $monthlyCounts[$month]++;
        }

        // Calculate monthly averages
        $monthlyAverages = [];
        $overallAverage = array_sum($historicalData) / count($historicalData);

        for ($month = 0; $month < 12; $month++) {
            if ($monthlyCounts[$month] > 0) {
                $monthAverage = $monthlyTotals[$month] / $monthlyCounts[$month];
                $monthlyAverages[$month] = $monthAverage / $overallAverage;
            } else {
                $monthlyAverages[$month] = 1.0;
            }
        }

        return $monthlyAverages;
    }
}