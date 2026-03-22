<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\AlertRule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AlertController extends Controller
{
    /**
     * Display a listing of alerts.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Alert::query();
            
            // Filter by type
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }
            
            // Filter by severity
            if ($request->has('severity')) {
                $query->where('severity', $request->severity);
            }
            
            // Filter by read status
            if ($request->has('is_read')) {
                $query->where('is_read', $request->boolean('is_read'));
            }
            
            // Filter by date range
            if ($request->has('start_date')) {
                $query->where('created_at', '>=', $request->start_date);
            }
            
            if ($request->has('end_date')) {
                $query->where('created_at', '<=', $request->end_date);
            }
            
            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            // Paginate
            $perPage = $request->get('per_page', 20);
            $alerts = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $alerts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch alerts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created alert.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'alert_rule_id' => 'nullable|exists:alert_rules,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|string|in:consumption,payment,maintenance,system',
            'severity' => 'required|string|in:info,warning,critical',
            'data' => 'nullable|array',
            'alertable_type' => 'nullable|string',
            'alertable_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $alert = Alert::create($request->all());
            
            // If alertable_type and alertable_id are provided, attach them
            if ($request->has(['alertable_type', 'alertable_id'])) {
                $alert->alertable_type = $request->alertable_type;
                $alert->alertable_id = $request->alertable_id;
                $alert->save();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Alert created successfully',
                'data' => $alert
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create alert',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified alert.
     */
    public function show(Alert $alert): JsonResponse
    {
        try {
            $alert->load(['alertRule']);
            return response()->json([
                'success' => true,
                'data' => $alert
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch alert',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark alert as read.
     */
    public function markAsRead(Alert $alert): JsonResponse
    {
        try {
            $alert->update([
                'is_read' => true,
                'read_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Alert marked as read',
                'data' => $alert
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark alert as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark alert as resolved.
     */
    public function markAsResolved(Alert $alert): JsonResponse
    {
        try {
            $alert->update([
                'resolved_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Alert marked as resolved',
                'data' => $alert
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark alert as resolved',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all alerts as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            Alert::where('is_read', false)->update([
                'is_read' => true,
                'read_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'All alerts marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all alerts as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread alerts count.
     */
    public function unreadCount(): JsonResponse
    {
        try {
            $count = Alert::where('is_read', false)->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get unread alerts count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get alerts summary.
     */
    public function summary(): JsonResponse
    {
        try {
            $total = Alert::count();
            $unread = Alert::where('is_read', false)->count();
            $critical = Alert::where('severity', 'critical')->count();
            $warning = Alert::where('severity', 'warning')->count();
            $info = Alert::where('severity', 'info')->count();
            
            $today = Alert::whereDate('created_at', today())->count();
            $thisWeek = Alert::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count();
            $thisMonth = Alert::whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'unread' => $unread,
                    'by_severity' => [
                        'critical' => $critical,
                        'warning' => $warning,
                        'info' => $info
                    ],
                    'by_time' => [
                        'today' => $today,
                        'this_week' => $thisWeek,
                        'this_month' => $thisMonth
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get alerts summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified alert.
     */
    public function destroy(Alert $alert): JsonResponse
    {
        try {
            $alert->delete();
            return response()->json([
                'success' => true,
                'message' => 'Alert deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete alert',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}