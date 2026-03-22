<?php

namespace App\Http\Controllers;

use App\Models\AlertRule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AlertRuleController extends Controller
{
    /**
     * Display a listing of alert rules.
     */
    public function index(): JsonResponse
    {
        try {
            $rules = AlertRule::all();
            return response()->json([
                'success' => true,
                'data' => $rules
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch alert rules',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created alert rule.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:consumption,payment,maintenance,system',
            'condition' => 'required|string|in:>,<,>=,<=,==,!=',
            'threshold_value' => 'nullable|numeric',
            'threshold_field' => 'nullable|string|max:255',
            'notification_method' => 'required|string|in:email,sms,in_app,all',
            'recipients' => 'nullable|array',
            'severity' => 'required|string|in:info,warning,critical',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $rule = AlertRule::create($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Alert rule created successfully',
                'data' => $rule
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create alert rule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified alert rule.
     */
    public function show(AlertRule $alertRule): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $alertRule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch alert rule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified alert rule.
     */
    public function update(Request $request, AlertRule $alertRule): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|in:consumption,payment,maintenance,system',
            'condition' => 'sometimes|string|in:>,<,>=,<=,==,!=',
            'threshold_value' => 'nullable|numeric',
            'threshold_field' => 'nullable|string|max:255',
            'notification_method' => 'sometimes|string|in:email,sms,in_app,all',
            'recipients' => 'nullable|array',
            'severity' => 'sometimes|string|in:info,warning,critical',
            'is_active' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $alertRule->update($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Alert rule updated successfully',
                'data' => $alertRule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update alert rule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified alert rule.
     */
    public function destroy(AlertRule $alertRule): JsonResponse
    {
        try {
            $alertRule->delete();
            return response()->json([
                'success' => true,
                'message' => 'Alert rule deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete alert rule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle alert rule active status.
     */
    public function toggleActive(AlertRule $alertRule): JsonResponse
    {
        try {
            $alertRule->update([
                'is_active' => !$alertRule->is_active
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Alert rule status updated',
                'data' => $alertRule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update alert rule status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active alert rules.
     */
    public function active(): JsonResponse
    {
        try {
            $rules = AlertRule::where('is_active', true)->get();
            return response()->json([
                'success' => true,
                'data' => $rules
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active alert rules',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get alert rules by type.
     */
    public function byType($type): JsonResponse
    {
        try {
            $rules = AlertRule::where('type', $type)->get();
            return response()->json([
                'success' => true,
                'data' => $rules
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch alert rules by type',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}