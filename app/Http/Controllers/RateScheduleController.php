<?php

namespace App\Http\Controllers;

use App\Models\RateSchedule;
use App\Models\UtilityType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RateScheduleController extends Controller
{
    /**
     * Display a listing of rate schedules.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = RateSchedule::with(['utilityType', 'rates']);
            
            // Filter by utility type
            if ($request->has('utility_type_id')) {
                $query->where('utility_type_id', $request->utility_type_id);
            }
            
            // Filter by active status
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }
            
            // Filter by type
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }
            
            // Filter effective date
            if ($request->has('effective_date')) {
                $query->where('effective_date', '<=', $request->effective_date)
                      ->where(function($q) use ($request) {
                          $q->where('expiration_date', '>=', $request->effective_date)
                            ->orWhereNull('expiration_date');
                      });
            }
            
            // Sort
            $sortBy = $request->get('sort_by', 'effective_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            $schedules = $query->paginate($request->get('per_page', 20));
            
            return response()->json([
                'success' => true,
                'data' => $schedules
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rate schedules',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created rate schedule.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:tiered,time_of_use,flat,seasonal',
            'utility_type_id' => 'required|exists:utility_types,id',
            'effective_date' => 'required|date',
            'expiration_date' => 'nullable|date|after:effective_date',
            'is_active' => 'boolean',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $schedule = RateSchedule::create($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Rate schedule created successfully',
                'data' => $schedule->load(['utilityType'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create rate schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified rate schedule.
     */
    public function show(RateSchedule $rateSchedule): JsonResponse
    {
        try {
            $rateSchedule->load(['utilityType', 'rates']);
            return response()->json([
                'success' => true,
                'data' => $rateSchedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rate schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified rate schedule.
     */
    public function update(Request $request, RateSchedule $rateSchedule): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|in:tiered,time_of_use,flat,seasonal',
            'utility_type_id' => 'sometimes|exists:utility_types,id',
            'effective_date' => 'sometimes|date',
            'expiration_date' => 'nullable|date|after:effective_date',
            'is_active' => 'sometimes|boolean',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $rateSchedule->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Rate schedule updated successfully',
                'data' => $rateSchedule->load(['utilityType'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update rate schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified rate schedule.
     */
    public function destroy(RateSchedule $rateSchedule): JsonResponse
    {
        try {
            // Check if schedule has rates
            if ($rateSchedule->rates()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete rate schedule with associated rates. Delete rates first.'
                ], 400);
            }
            
            $rateSchedule->delete();
            return response()->json([
                'success' => true,
                'message' => 'Rate schedule deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete rate schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active rate schedule for a utility type.
     */
    public function getActiveSchedule($utilityTypeId): JsonResponse
    {
        try {
            $schedule = RateSchedule::where('utility_type_id', $utilityTypeId)
                ->where('is_active', true)
                ->where('effective_date', '<=', now())
                ->where(function($query) {
                    $query->where('expiration_date', '>=', now())
                          ->orWhereNull('expiration_date');
                })
                ->with('rates')
                ->first();
            
            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active rate schedule found for this utility type'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $schedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active rate schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(RateSchedule $rateSchedule): JsonResponse
    {
        try {
            $rateSchedule->update([
                'is_active' => !$rateSchedule->is_active
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Rate schedule status updated',
                'data' => $rateSchedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update rate schedule status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate rate for consumption.
     */
    public function calculateRate(Request $request, RateSchedule $rateSchedule): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'consumption' => 'required|numeric|min:0',
            'date' => 'nullable|date',
            'time_period' => 'nullable|string|in:peak,off_peak,shoulder',
            'season' => 'nullable|string|in:summer,winter,spring,fall'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $consumption = $request->consumption;
            $date = $request->date ?: now();
            $timePeriod = $request->time_period;
            $season = $request->season;
            
            // Get applicable rates
            $rates = $rateSchedule->rates()
                ->where(function($query) use ($consumption) {
                    $query->whereNull('min_consumption')
                          ->orWhere('min_consumption', '<=', $consumption);
                })
                ->where(function($query) use ($consumption) {
                    $query->whereNull('max_consumption')
                          ->orWhere('max_consumption', '>=', $consumption);
                })
                ->where(function($query) use ($timePeriod) {
                    if ($timePeriod) {
                        $query->where('time_period', $timePeriod)
                              ->orWhereNull('time_period');
                    }
                })
                ->where(function($query) use ($season) {
                    if ($season) {
                        $query->where('season', $season)
                              ->orWhereNull('season');
                    }
                })
                ->orderBy('min_consumption')
                ->get();
            
            if ($rates->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No applicable rates found for the given parameters'
                ], 404);
            }
            
            $totalCost = 0;
            $remainingConsumption = $consumption;
            $rateDetails = [];
            
            foreach ($rates as $rate) {
                $applicableConsumption = $remainingConsumption;
                
                if ($rate->max_consumption !== null && $applicableConsumption > ($rate->max_consumption - ($rate->min_consumption ?: 0))) {
                    $applicableConsumption = $rate->max_consumption - ($rate->min_consumption ?: 0);
                }
                
                if ($applicableConsumption > 0) {
                    $rateCost = $applicableConsumption * $rate->rate_per_unit;
                    $totalCost += $rateCost;
                    
                    $rateDetails[] = [
                        'rate_id' => $rate->id,
                        'min_consumption' => $rate->min_consumption,
                        'max_consumption' => $rate->max_consumption,
                        'rate_per_unit' => $rate->rate_per_unit,
                        'consumption' => $applicableConsumption,
                        'cost' => $rateCost,
                        'time_period' => $rate->time_period,
                        'season' => $rate->season
                    ];
                    
                    $remainingConsumption -= $applicableConsumption;
                }
                
                if ($remainingConsumption <= 0) {
                    break;
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'schedule_id' => $rateSchedule->id,
                    'schedule_name' => $rateSchedule->name,
                    'total_consumption' => $consumption,
                    'total_cost' => $totalCost,
                    'average_rate' => $consumption > 0 ? $totalCost / $consumption : 0,
                    'rate_details' => $rateDetails
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate rate',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}