<?php

namespace App\Http\Controllers;

use App\Models\Rate;
use App\Models\RateSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RateController extends Controller
{
    /**
     * Display a listing of rates.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Rate::with(['rateSchedule']);
            
            // Filter by rate schedule
            if ($request->has('rate_schedule_id')) {
                $query->where('rate_schedule_id', $request->rate_schedule_id);
            }
            
            // Filter by time period
            if ($request->has('time_period')) {
                $query->where('time_period', $request->time_period);
            }
            
            // Filter by season
            if ($request->has('season')) {
                $query->where('season', $request->season);
            }
            
            // Sort
            $sortBy = $request->get('sort_by', 'min_consumption');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);
            
            $rates = $query->paginate($request->get('per_page', 20));
            
            return response()->json([
                'success' => true,
                'data' => $rates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created rate.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rate_schedule_id' => 'required|exists:rate_schedules,id',
            'min_consumption' => 'nullable|numeric|min:0',
            'max_consumption' => 'nullable|numeric|gt:min_consumption',
            'rate_per_unit' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
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
            // Check for overlapping consumption ranges
            $overlap = Rate::where('rate_schedule_id', $request->rate_schedule_id)
                ->where(function($query) use ($request) {
                    // Check if new range overlaps with existing ranges
                    if ($request->min_consumption !== null && $request->max_consumption !== null) {
                        $query->where(function($q) use ($request) {
                            $q->whereBetween('min_consumption', [$request->min_consumption, $request->max_consumption])
                              ->orWhereBetween('max_consumption', [$request->min_consumption, $request->max_consumption])
                              ->orWhere(function($q2) use ($request) {
                                  $q2->where('min_consumption', '<=', $request->min_consumption)
                                     ->where('max_consumption', '>=', $request->max_consumption);
                              });
                        });
                    } elseif ($request->min_consumption !== null) {
                        $query->where('max_consumption', '>=', $request->min_consumption)
                              ->orWhereNull('max_consumption');
                    } elseif ($request->max_consumption !== null) {
                        $query->where('min_consumption', '<=', $request->max_consumption)
                              ->orWhereNull('min_consumption');
                    }
                })
                ->where('time_period', $request->time_period)
                ->where('season', $request->season)
                ->exists();
            
            if ($overlap) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rate range overlaps with existing rates for the same time period and season'
                ], 409);
            }
            
            $rate = Rate::create($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Rate created successfully',
                'data' => $rate->load(['rateSchedule'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create rate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified rate.
     */
    public function show(Rate $rate): JsonResponse
    {
        try {
            $rate->load(['rateSchedule']);
            return response()->json([
                'success' => true,
                'data' => $rate
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified rate.
     */
    public function update(Request $request, Rate $rate): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rate_schedule_id' => 'sometimes|exists:rate_schedules,id',
            'min_consumption' => 'nullable|numeric|min:0',
            'max_consumption' => 'nullable|numeric|gt:min_consumption',
            'rate_per_unit' => 'sometimes|numeric|min:0',
            'unit' => 'sometimes|string|max:50',
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
            // Check for overlapping consumption ranges (excluding current rate)
            $overlap = Rate::where('rate_schedule_id', $request->rate_schedule_id ?? $rate->rate_schedule_id)
                ->where('id', '!=', $rate->id)
                ->where(function($query) use ($request, $rate) {
                    $min = $request->min_consumption ?? $rate->min_consumption;
                    $max = $request->max_consumption ?? $rate->max_consumption;
                    
                    if ($min !== null && $max !== null) {
                        $query->where(function($q) use ($min, $max) {
                            $q->whereBetween('min_consumption', [$min, $max])
                              ->orWhereBetween('max_consumption', [$min, $max])
                              ->orWhere(function($q2) use ($min, $max) {
                                  $q2->where('min_consumption', '<=', $min)
                                     ->where('max_consumption', '>=', $max);
                              });
                        });
                    } elseif ($min !== null) {
                        $query->where('max_consumption', '>=', $min)
                              ->orWhereNull('max_consumption');
                    } elseif ($max !== null) {
                        $query->where('min_consumption', '<=', $max)
                              ->orWhereNull('min_consumption');
                    }
                })
                ->where('time_period', $request->time_period ?? $rate->time_period)
                ->where('season', $request->season ?? $rate->season)
                ->exists();
            
            if ($overlap) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rate range overlaps with existing rates for the same time period and season'
                ], 409);
            }
            
            $rate->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Rate updated successfully',
                'data' => $rate->load(['rateSchedule'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update rate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified rate.
     */
    public function destroy(Rate $rate): JsonResponse
    {
        try {
            $rate->delete();
            return response()->json([
                'success' => true,
                'message' => 'Rate deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete rate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rates by schedule.
     */
    public function bySchedule($scheduleId): JsonResponse
    {
        try {
            $rates = Rate::where('rate_schedule_id', $scheduleId)
                ->orderBy('min_consumption')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $rates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rates by schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}