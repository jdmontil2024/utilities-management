<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Display a listing of reports.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Report::query();
            
            // Filter by type
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }
            
            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            // Filter by generated_by
            if ($request->has('generated_by')) {
                $query->where('generated_by', $request->generated_by);
            }
            
            // Filter by date range
            if ($request->has('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            
            if ($request->has('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
            
            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            $reports = $query->paginate($request->get('per_page', 20));
            
            return response()->json([
                'success' => true,
                'data' => $reports
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch reports',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created report request.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:financial,consumption,occupancy,maintenance',
            'format' => 'required|string|in:pdf,excel,csv,html',
            'parameters' => 'nullable|array',
            'status' => 'sometimes|string|in:pending,generating,completed,failed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $report = Report::create([
                'name' => $request->name,
                'type' => $request->type,
                'format' => $request->format,
                'parameters' => $request->parameters,
                'status' => 'pending',
                'generated_by' => auth()->id()
            ]);
            
            // Queue report generation
            dispatch(function () use ($report) {
                $this->generateReport($report);
            })->afterResponse();
            
            return response()->json([
                'success' => true,
                'message' => 'Report generation queued successfully',
                'data' => $report
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create report request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified report.
     */
    public function show(Report $report): JsonResponse
    {
        try {
            $report->load(['generatedBy']);
            return response()->json([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download the report file.
     */
    public function download(Report $report): JsonResponse
    {
        try {
            if ($report->status !== 'completed' || empty($report->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Report is not ready for download'
                ], 400);
            }
            
            if (!Storage::exists($report->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Report file not found'
                ], 404);
            }
            
            // Return file download response
            return response()->download(
                Storage::path($report->file_path),
                $report->name . '.' . $report->format
            );
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified report.
     */
    public function destroy(Report $report): JsonResponse
    {
        try {
            // Delete file if exists
            if ($report->file_path && Storage::exists($report->file_path)) {
                Storage::delete($report->file_path);
            }
            
            $report->delete();
            return response()->json([
                'success' => true,
                'message' => 'Report deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate report immediately.
     */
    public function generate(Report $report): JsonResponse
    {
        try {
            $this->generateReport($report);
            
            return response()->json([
                'success' => true,
                'message' => 'Report generated successfully',
                'data' => $report->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate financial report.
     */
    private function generateFinancialReport(Report $report, array $parameters)
    {
        // Example financial report data
        $data = [
            'period' => $parameters['period'] ?? 'monthly',
            'start_date' => $parameters['start_date'] ?? now()->startOfMonth(),
            'end_date' => $parameters['end_date'] ?? now()->endOfMonth(),
            'revenue' => [
                'rent' => 15000,
                'utilities' => 5000,
                'fees' => 1000
            ],
            'expenses' => [
                'maintenance' => 3000,
                'utilities' => 2000,
                'taxes' => 2500
            ],
            'summary' => [
                'total_revenue' => 21000,
                'total_expenses' => 7500,
                'net_income' => 13500
            ]
        ];
        
        $report->data = $data;
        $this->generateFile($report, $data);
    }

    /**
     * Generate consumption report.
     */
    private function generateConsumptionReport(Report $report, array $parameters)
    {
        // Example consumption report data
        $data = [
            'period' => $parameters['period'] ?? 'monthly',
            'utility_type' => $parameters['utility_type'] ?? 'all',
            'start_date' => $parameters['start_date'] ?? now()->startOfMonth(),
            'end_date' => $parameters['end_date'] ?? now()->endOfMonth(),
            'consumption' => [
                'electricity' => [
                    'total_kwh' => 15000,
                    'average_daily' => 500,
                    'peak_consumption' => 800
                ],
                'water' => [
                    'total_gallons' => 75000,
                    'average_daily' => 2500,
                    'peak_consumption' => 4000
                ],
                'gas' => [
                    'total_ccf' => 5000,
                    'average_daily' => 167,
                    'peak_consumption' => 300
                ]
            ],
            'costs' => [
                'total' => 8500,
                'electricity' => 4500,
                'water' => 2500,
                'gas' => 1500
            ]
        ];
        
        $report->data = $data;
        $this->generateFile($report, $data);
    }

    /**
     * Generate occupancy report.
     */
    private function generateOccupancyReport(Report $report, array $parameters)
    {
        // Example occupancy report data
        $data = [
            'as_of_date' => $parameters['as_of_date'] ?? now(),
            'occupancy_rate' => 85,
            'vacancy_rate' => 15,
            'total_units' => 100,
            'occupied_units' => 85,
            'vacant_units' => 15,
            'by_building' => [
                ['building' => 'Building A', 'total' => 50, 'occupied' => 45, 'vacant' => 5],
                ['building' => 'Building B', 'total' => 30, 'occupied' => 25, 'vacant' => 5],
                ['building' => 'Building C', 'total' => 20, 'occupied' => 15, 'vacant' => 5]
            ]
        ];
        
        $report->data = $data;
        $this->generateFile($report, $data);
    }

    /**
     * Generate maintenance report.
     */
    private function generateMaintenanceReport(Report $report, array $parameters)
    {
        // Example maintenance report data
        $data = [
            'period' => $parameters['period'] ?? 'monthly',
            'start_date' => $parameters['start_date'] ?? now()->startOfMonth(),
            'end_date' => $parameters['end_date'] ?? now()->endOfMonth(),
            'summary' => [
                'total_requests' => 45,
                'completed' => 40,
                'in_progress' => 3,
                'pending' => 2
            ],
            'by_category' => [
                ['category' => 'Plumbing', 'count' => 15, 'avg_cost' => 150],
                ['category' => 'Electrical', 'count' => 12, 'avg_cost' => 200],
                ['category' => 'HVAC', 'count' => 10, 'avg_cost' => 300],
                ['category' => 'Appliance', 'count' => 8, 'avg_cost' => 100]
            ],
            'costs' => [
                'total' => 7500,
                'labor' => 4500,
                'parts' => 3000
            ]
        ];
        
        $report->data = $data;
        $this->generateFile($report, $data);
    }

    /**
     * Generate the actual file based on format.
     */
    private function generateFile(Report $report, array $data)
    {
        $fileName = 'reports/' . uniqid() . '_' . str_slug($report->name) . '.' . $report->format;
        
        try {
            $report->update(['status' => 'generating']);
            
            switch ($report->format) {
                case 'pdf':
                    $pdf = PDF::loadView('reports.template', ['data' => $data, 'report' => $report]);
                    Storage::put($fileName, $pdf->output());
                    break;
                    
                case 'excel':
                    // Implement Excel generation
                    // Excel::store(new ReportExport($data), $fileName);
                    Storage::put($fileName, json_encode($data));
                    break;
                    
                case 'csv':
                    $csvContent = $this->arrayToCsv($data);
                    Storage::put($fileName, $csvContent);
                    break;
                    
                case 'html':
                    $htmlContent = view('reports.template', ['data' => $data, 'report' => $report])->render();
                    Storage::put($fileName, $htmlContent);
                    break;
            }
            
            $report->update([
                'status' => 'completed',
                'file_path' => $fileName,
                'file_size' => Storage::size($fileName),
                'mime_type' => Storage::mimeType($fileName),
                'generated_at' => now()
            ]);
            
        } catch (\Exception $e) {
            $report->update([
                'status' => 'failed',
                'data' => ['error' => $e->getMessage()]
            ]);
            throw $e;
        }
    }

    /**
     * Convert array to CSV.
     */
    private function arrayToCsv(array $data): string
    {
        $output = fopen('php://temp', 'r+');
        
        $flattened = $this->flattenArray($data);
        foreach ($flattened as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    /**
     * Flatten array for CSV.
     */
    private function flattenArray(array $array, $prefix = ''): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value) && isset($value[0]) && is_array($value[0])) {
                // Handle array of arrays (tabular data)
                $result[] = array_merge([$prefix . $key], array_keys($value[0]));
                foreach ($value as $row) {
                    $result[] = array_merge([$prefix . $key], array_values($row));
                }
            } elseif (is_array($value)) {
                // Recursively flatten nested arrays
                $result = array_merge($result, $this->flattenArray($value, $prefix . $key . '.'));
            } else {
                // Simple key-value pair
                $result[] = [$prefix . $key, $value];
            }
        }
        
        return $result;
    }

    /**
     * Main report generation method.
     */
    private function generateReport(Report $report)
    {
        $parameters = $report->parameters ?? [];
        
        switch ($report->type) {
            case 'financial':
                $this->generateFinancialReport($report, $parameters);
                break;
            case 'consumption':
                $this->generateConsumptionReport($report, $parameters);
                break;
            case 'occupancy':
                $this->generateOccupancyReport($report, $parameters);
                break;
            case 'maintenance':
                $this->generateMaintenanceReport($report, $parameters);
                break;
            default:
                throw new \Exception("Unknown report type: {$report->type}");
        }
    }

    /**
     * Get report statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $total = Report::count();
            $completed = Report::where('status', 'completed')->count();
            $failed = Report::where('status', 'failed')->count();
            $pending = Report::where('status', 'pending')->count();
            $generating = Report::where('status', 'generating')->count();
            
            $byType = Report::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->get();
            
            $byFormat = Report::selectRaw('format, COUNT(*) as count')
                ->groupBy('format')
                ->get();
            
            $recent = Report::orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'totals' => [
                        'total' => $total,
                        'completed' => $completed,
                        'failed' => $failed,
                        'pending' => $pending,
                        'generating' => $generating
                    ],
                    'by_type' => $byType,
                    'by_format' => $byFormat,
                    'recent_reports' => $recent
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get report statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available report templates.
     */
    public function templates(): JsonResponse
    {
        try {
            $templates = [
                [
                    'name' => 'Monthly Financial Report',
                    'type' => 'financial',
                    'description' => 'Monthly revenue, expenses, and net income report',
                    'parameters' => [
                        'period' => 'monthly',
                        'start_date' => 'required|date',
                        'end_date' => 'required|date'
                    ]
                ],
                [
                    'name' => 'Utility Consumption Report',
                    'type' => 'consumption',
                    'description' => 'Monthly utility consumption and costs',
                    'parameters' => [
                        'period' => 'monthly',
                        'utility_type' => 'optional|string',
                        'start_date' => 'required|date',
                        'end_date' => 'required|date'
                    ]
                ],
                [
                    'name' => 'Occupancy Report',
                    'type' => 'occupancy',
                    'description' => 'Current occupancy and vacancy rates',
                    'parameters' => [
                        'as_of_date' => 'optional|date'
                    ]
                ],
                [
                    'name' => 'Maintenance Summary',
                    'type' => 'maintenance',
                    'description' => 'Monthly maintenance requests and costs',
                    'parameters' => [
                        'period' => 'monthly',
                        'start_date' => 'required|date',
                        'end_date' => 'required|date'
                    ]
                ]
            ];
            
            return response()->json([
                'success' => true,
                'data' => $templates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get report templates',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}