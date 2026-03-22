<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ActivityLog::query();
            
            // Filter by log name
            if ($request->has('log_name')) {
                $query->where('log_name', $request->log_name);
            }
            
            // Filter by subject type
            if ($request->has('subject_type')) {
                $query->where('subject_type', $request->subject_type);
            }
            
            // Filter by subject ID
            if ($request->has('subject_id')) {
                $query->where('subject_id', $request->subject_id);
            }
            
            // Filter by causer type
            if ($request->has('causer_type')) {
                $query->where('causer_type', $request->causer_type);
            }
            
            // Filter by causer ID
            if ($request->has('causer_id')) {
                $query->where('causer_id', $request->causer_id);
            }
            
            // Filter by date range
            if ($request->has('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            
            if ($request->has('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
            
            // Search in description
            if ($request->has('search')) {
                $query->where('description', 'like', '%' . $request->search . '%');
            }
            
            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            $logs = $query->paginate($request->get('per_page', 50));
            
            // Load relationships
            $logs->load(['causer', 'subject']);
            
            return response()->json([
                'success' => true,
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activity logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified activity log.
     */
    public function show(ActivityLog $activityLog): JsonResponse
    {
        try {
            $activityLog->load(['causer', 'subject']);
            return response()->json([
                'success' => true,
                'data' => $activityLog
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activity log',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get activity log statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $query = ActivityLog::query();
            
            // Filter by date range
            if ($request->has('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            
            if ($request->has('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
            
            $total = $query->count();
            
            // Count by log name
            $byLogName = $query->clone()
                ->selectRaw('log_name, COUNT(*) as count')
                ->groupBy('log_name')
                ->orderBy('count', 'desc')
                ->get();
            
            // Count by causer type
            $byCauserType = $query->clone()
                ->selectRaw('causer_type, COUNT(*) as count')
                ->whereNotNull('causer_type')
                ->groupBy('causer_type')
                ->orderBy('count', 'desc')
                ->get();
            
            // Count by subject type
            $bySubjectType = $query->clone()
                ->selectRaw('subject_type, COUNT(*) as count')
                ->whereNotNull('subject_type')
                ->groupBy('subject_type')
                ->orderBy('count', 'desc')
                ->get();
            
            // Daily activity for the last 30 days
            $dailyActivity = $query->clone()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            // Top causers
            $topCausers = $query->clone()
                ->selectRaw('causer_type, causer_id, COUNT(*) as count')
                ->whereNotNull('causer_id')
                ->groupBy('causer_type', 'causer_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get()
                ->load('causer');
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_logs' => $total,
                    'by_log_name' => $byLogName,
                    'by_causer_type' => $byCauserType,
                    'by_subject_type' => $bySubjectType,
                    'daily_activity' => $dailyActivity,
                    'top_causers' => $topCausers
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get activity log statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get activity logs for a specific subject.
     */
    public function forSubject(Request $request, $subjectType, $subjectId): JsonResponse
    {
        try {
            $query = ActivityLog::where('subject_type', $subjectType)
                ->where('subject_id', $subjectId)
                ->orderBy('created_at', 'desc');
            
            $logs = $query->paginate($request->get('per_page', 20));
            $logs->load(['causer']);
            
            return response()->json([
                'success' => true,
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activity logs for subject',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get activity logs for a specific causer.
     */
    public function forCauser(Request $request, $causerType, $causerId): JsonResponse
    {
        try {
            $query = ActivityLog::where('causer_type', $causerType)
                ->where('causer_id', $causerId)
                ->orderBy('created_at', 'desc');
            
            $logs = $query->paginate($request->get('per_page', 20));
            $logs->load(['subject']);
            
            return response()->json([
                'success' => true,
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activity logs for causer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent activity logs.
     */
    public function recent(Request $request): JsonResponse
    {
        try {
            $query = ActivityLog::query()
                ->orderBy('created_at', 'desc')
                ->limit($request->get('limit', 50));
            
            $logs = $query->get();
            $logs->load(['causer', 'subject']);
            
            return response()->json([
                'success' => true,
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent activity logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get activity log summary by date.
     */
    public function summaryByDate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'group_by' => 'sometimes|in:day,week,month'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $groupBy = $request->group_by ?? 'day';
            
            $query = ActivityLog::whereBetween('created_at', [$startDate, $endDate]);
            
            switch ($groupBy) {
                case 'day':
                    $query->selectRaw('DATE(created_at) as period, COUNT(*) as count')
                        ->groupBy('period')
                        ->orderBy('period');
                    break;
                    
                case 'week':
                    $query->selectRaw('YEARWEEK(created_at) as period, COUNT(*) as count')
                        ->groupBy('period')
                        ->orderBy('period');
                    break;
                    
                case 'month':
                    $query->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as period, COUNT(*) as count')
                        ->groupBy('period')
                        ->orderBy('period');
                    break;
            }
            
            $summary = $query->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'period' => [
                        'start' => $startDate,
                        'end' => $endDate,
                        'group_by' => $groupBy
                    ],
                    'summary' => $summary,
                    'total' => $summary->sum('count')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get activity log summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clean up old activity logs.
     */
    public function cleanup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'days' => 'required|integer|min:1|max:3650'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $days = $request->days;
            $cutoffDate = now()->subDays($days);
            
            $deletedCount = ActivityLog::where('created_at', '<', $cutoffDate)->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Activity logs cleaned up successfully',
                'data' => [
                    'days_old' => $days,
                    'cutoff_date' => $cutoffDate,
                    'deleted_count' => $deletedCount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clean up activity logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available log names.
     */
    public function logNames(): JsonResponse
    {
        try {
            $logNames = ActivityLog::select('log_name')
                ->distinct()
                ->whereNotNull('log_name')
                ->orderBy('log_name')
                ->pluck('log_name');
            
            return response()->json([
                'success' => true,
                'data' => $logNames
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch log names',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get activity log trends.
     */
    public function trends(): JsonResponse
    {
        try {
            // Last 7 days trend
            $last7Days = ActivityLog::whereDate('created_at', '>=', now()->subDays(7))
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            // Last 30 days trend
            $last30Days = ActivityLog::whereDate('created_at', '>=', now()->subDays(30))
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            // Hourly distribution for today
            $todayHourly = ActivityLog::whereDate('created_at', today())
                ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'last_7_days' => $last7Days,
                    'last_30_days' => $last30Days,
                    'today_hourly' => $todayHourly
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get activity log trends',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}