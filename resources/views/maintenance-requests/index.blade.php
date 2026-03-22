@extends('layouts.app')

@section('title', 'Maintenance Requests - Utility Wise')

@push('styles')
<style>
    /* Page Header */
    .page-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }
    
    .page-title {
        font-size: 24px;
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .page-subtitle {
        color: #6c757d;
        margin-top: 5px;
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        border-radius: 8px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        border: 1px solid #dee2e6;
    }
    
    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #2c3e50;
        display: block;
        margin-bottom: 10px;
    }
    
    .stat-label {
        font-size: 13px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 500;
    }

    /* View Toggle */
    .view-toggle {
        display: flex;
        gap: 8px;
        background: #f8f9fa;
        padding: 4px;
        border-radius: 6px;
        border: 1px solid #dee2e6;
    }

    .view-toggle-btn {
        padding: 8px 16px;
        border: none;
        background: transparent;
        border-radius: 4px;
        font-weight: 500;
        font-size: 14px;
        color: #6c757d;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .view-toggle-btn.active {
        background: #4a5568;
        color: white;
        box-shadow: 0 2px 5px rgba(0,0,0,.1);
    }

    /* Requests Grid */
    .requests-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 30px;
    }

    /* Requests Table View */
    .requests-table {
        margin-bottom: 30px;
        overflow-x: auto;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        border: 1px solid #dee2e6;
    }

    .requests-table table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1000px;
    }

    .requests-table th {
        background: #f8f9fa;
        padding: 18px 15px;
        text-align: left;
        font-weight: 600;
        color: #2c3e50;
        border: 1px solid #dee2e6;
        font-size: 14px;
        white-space: nowrap;
    }

    .requests-table td {
        padding: 16px 15px;
        border: 1px solid #e9ecef;
        vertical-align: middle;
        font-size: 14px;
        line-height: 1.4;
    }

    .requests-table tbody tr:hover td {
        background: #f8f9fa;
        border-color: #cfe2ff;
    }

    /* Hidden class for toggling views */
    .hidden {
        display: none !important;
    }

    /* Request Card Styles */
    .request-card {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        border: 1px solid #dee2e6;
        transition: all 0.3s ease;
        position: relative;
        height: 100%;
        min-height: 240px;
        animation: fadeIn 0.3s ease forwards;
    }

    .request-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,.15);
    }

    .request-card.overdue {
        border-left: 4px solid #e74c3c;
    }

    /* Gradient Header - from units page */
    .request-photo-container {
        height: 140px;
        width: 100%;
        overflow: hidden;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Priority-based gradients */
    .request-photo-container.priority-emergency {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
    }

    .request-photo-container.priority-high {
        background: linear-gradient(135deg, #e67e22, #d35400);
    }

    .request-photo-container.priority-medium {
        background: linear-gradient(135deg, #f39c12, #e67e22);
    }

    .request-photo-container.priority-low {
        background: linear-gradient(135deg, #27ae60, #229954);
    }

    .request-photo-container.priority-default {
        background: linear-gradient(135deg, #4a5568, #2d3748);
    }

    .priority-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
        flex-direction: column;
        gap: 5px;
    }

    .priority-placeholder .icon {
        font-size: 36px;
        opacity: 0.9;
    }

    .priority-placeholder .text {
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        background: rgba(0,0,0,0.2);
        padding: 3px 10px;
        border-radius: 20px;
    }

    /* Card Content */
    .request-card-content {
        padding: 12px 15px;
    }

    .request-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 8px;
    }

    .request-title {
        font-size: 15px;
        font-weight: 600;
        color: #2c3e50;
        text-decoration: none;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
        flex: 1;
    }

    .request-title:hover {
        color: #3498db;
    }

    /* Compact Status Badge */
    .status-badge-compact {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        white-space: nowrap;
        margin-left: 8px;
    }

    .status-submitted {
        background: #e8f4fc;
        color: #2c3e50;
    }

    .status-assigned {
        background: #fff3cd;
        color: #856404;
    }

    .status-in_progress {
        background: #cce5ff;
        color: #004085;
    }

    .status-completed {
        background: #d4edda;
        color: #155724;
    }

    .status-cancelled {
        background: #f8d7da;
        color: #721c24;
    }

    /* Location */
    .request-location {
        display: flex;
        align-items: center;
        gap: 4px;
        margin-bottom: 8px;
        font-size: 12px;
        color: #6c757d;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .request-location a {
        color: #6c757d;
        text-decoration: none;
    }

    .request-location a:hover {
        color: #3498db;
    }

    /* Key Stats - ONLY 2 MOST IMPORTANT */
    .request-stats {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        padding: 8px 0;
        border-top: 1px solid #f0f0f0;
        border-bottom: 1px solid #f0f0f0;
    }

    .stat-mini {
        text-align: center;
        flex: 1;
    }

    .stat-mini-value {
        font-size: 14px;
        font-weight: 700;
        color: #2c3e50;
        line-height: 1.2;
    }

    .stat-mini-label {
        font-size: 9px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    /* Days Badge - Whole number */
    .days-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
    }

    .days-warning {
        background: #fff3cd;
        color: #856404;
    }

    .days-danger {
        background: #f8d7da;
        color: #721c24;
    }

    .days-success {
        background: #d4edda;
        color: #155724;
    }

    /* Description Snippet */
    .request-description {
        font-size: 11px;
        color: #6c757d;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Card Actions */
    .request-card-actions {
        display: flex;
        gap: 6px;
    }

    .request-card-actions .btn-sm {
        flex: 1;
        height: 28px;
        font-size: 11px;
        padding: 0 8px;
        min-width: 0;
    }

    /* Table View Specific Styles */
    .table-request-title {
        font-weight: 600;
        color: #2c3e50;
        text-decoration: none;
        display: block;
        margin-bottom: 5px;
    }

    .table-request-title:hover {
        color: #3498db;
    }

    .table-location-info {
        display: flex;
        flex-direction: column;
    }

    .table-building-name {
        font-weight: 600;
        color: #2c3e50;
        text-decoration: none;
    }

    .table-building-name:hover {
        color: #3498db;
    }

    .table-unit-number {
        color: #6c757d;
        font-size: 12px;
        margin-top: 4px;
    }

    .table-specs {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .table-spec-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f8f9fa;
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 13px;
        color: #495057;
    }

    .table-spec-value {
        font-weight: 600;
        color: #2c3e50;
    }

    /* Status badges - for table view */
    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        text-align: center;
        min-width: 100px;
    }

    .status-submitted {
        background: #e8f4fc;
        color: #2c3e50;
        border: 1px solid #d1e4ff;
    }

    .status-assigned {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .status-in_progress {
        background: #cce5ff;
        color: #004085;
        border: 1px solid #b8daff;
    }

    .status-completed {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .status-cancelled {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    /* Priority badges - for table view */
    .priority-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 16px;
        font-size: 12px;
        font-weight: 500;
    }

    .priority-emergency {
        background: #fde2e2;
        color: #c0392b;
    }

    .priority-high {
        background: #fbe9e7;
        color: #d35400;
    }

    .priority-medium {
        background: #fff3e0;
        color: #e67e22;
    }

    .priority-low {
        background: #e8f5e9;
        color: #229954;
    }

    /* Table Action Buttons */
    .table-action-buttons {
        display: flex;
        gap: 4px;
        min-width: 220px;
    }

    .table-action-buttons .btn-sm {
        height: 32px;
        min-width: 70px;
        flex: 1;
    }

    /* BUTTONS */
    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        height: 32px;
        min-width: 70px;
        text-decoration: none;
        white-space: nowrap;
        font-weight: 500;
        line-height: 1;
    }

    .btn-sm:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        text-decoration: none;
    }

    .new-request-btn {
        padding: 10px 20px;
        background: #4a5568;
        border: 1px solid #4a5568;
        border-radius: 6px;
        color: white;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .new-request-btn:hover {
        background: #2d3748;
        border-color: #2d3748;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,.1);
        color: white;
        text-decoration: none;
    }

    /* Filter Section */
    .filter-section {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        border: 1px solid #dee2e6;
        margin-bottom: 30px;
        padding: 20px;
    }

    .filter-header {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .filter-title {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .filter-title span {
        font-size: 18px;
    }

    .filter-title h3 {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }

    .filter-form {
        flex: 1;
        min-width: 250px;
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .filter-select {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        font-size: 14px;
        color: #2c3e50;
        background: white;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        transition: all 0.2s ease;
    }

    .filter-select:hover {
        border-color: #4a5568;
    }

    .filter-select:focus {
        outline: none;
        border-color: #4a5568;
        box-shadow: 0 0 0 3px rgba(74,85,104,0.1);
    }

    .clear-filter-btn {
        padding: 10px 20px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        color: #6c757d;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .clear-filter-btn:hover {
        background: #e9ecef;
        color: #2c3e50;
        text-decoration: none;
    }

    .active-filter {
        background: #e8f4fc;
        border-left: 4px solid #4a5568;
    }

    /* Stats Info */
    .stats-info {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 20px;
    }

    .stats-info strong {
        color: #2c3e50;
    }

    /* Empty State */
    .no-data {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin: 20px 0;
        background: white;
    }

    .no-data h3 {
        margin-bottom: 10px;
        font-weight: 600;
        color: #2c3e50;
    }

    .no-data p {
        margin-bottom: 20px;
        color: #6c757d;
    }

    .no-data-icon {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.3;
    }

    /* Pagination */
    .pagination-container {
        margin-top: 30px;
        padding: 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        border: 1px solid #dee2e6;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .pagination li a,
    .pagination li span {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 6px;
        background: #f8f9fa;
        color: #495057;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }

    .pagination li a:hover {
        background: #4a5568;
        color: white;
        border-color: #4a5568;
        text-decoration: none;
    }

    .pagination li.active span {
        background: #4a5568;
        color: white;
        border-color: #4a5568;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        align-items: center;
        justify-content: center;
        z-index: 10000;
        animation: fadeIn 0.2s ease;
    }

    .modal-content {
        background: white;
        border-radius: 8px;
        width: 90%;
        max-width: 350px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        animation: scaleIn 0.3s ease;
        text-align: center;
        padding: 25px;
    }

    .modal-header {
        margin-bottom: 20px;
    }

    .modal-icon {
        font-size: 48px;
        color: #e74c3c;
        margin-bottom: 15px;
    }

    .modal-title {
        color: #2c3e50;
        margin-bottom: 8px;
        font-size: 18px;
        font-weight: 600;
    }

    .modal-body {
        color: #6c757d;
        line-height: 1.5;
        font-size: 14px;
        margin-bottom: 20px;
    }

    .modal-footer {
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    .modal-btn {
        padding: 9px 18px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
        flex: 1;
    }

    .modal-btn.cancel {
        background: #718096;
        color: white;
    }

    .modal-btn.cancel:hover {
        background: #4a5568;
    }

    .modal-btn.delete {
        background: #2d3748;
        color: white;
    }

    .modal-btn.delete:hover {
        background: #1a202c;
    }

    /* Delete form styling */
    .delete-form {
        margin: 0;
        padding: 0;
        display: flex;
        flex: 1;
    }

    a.btn-sm, button.btn-sm {
        text-decoration: none !important;
    }

    /* Utility Classes */
    .text-truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .text-small {
        font-size: 12px;
    }

    .text-muted {
        color: #6c757d;
    }

    /* Animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    /* Responsive */
    @media (max-width: 1400px) {
        .requests-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 1100px) {
        .requests-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .page-content {
            padding: 20px 15px !important;
        }

        .requests-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .view-toggle {
            margin-top: 10px;
            align-self: flex-start;
        }
        
        .filter-header {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-form {
            width: 100%;
            flex-wrap: wrap;
        }
        
        .filter-select {
            width: 100%;
        }
        
        .new-request-btn {
            width: 100%;
            justify-content: center;
        }

        .request-card-actions {
            flex-direction: row;
            gap: 6px;
        }

        .request-card-actions .btn-sm {
            width: 100%;
            height: 32px;
            font-size: 12px;
            padding: 6px 12px;
        }

        .table-action-buttons {
            flex-direction: column;
            min-width: auto;
            gap: 8px;
        }

        .table-action-buttons .btn-sm {
            width: 100%;
            height: 36px;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .request-card {
            margin: 0;
        }

        .request-card-actions {
            flex-direction: row;
            gap: 6px;
        }

        .request-card-actions .btn-sm {
            height: 32px;
        }

        .table-action-buttons .btn-sm {
            height: 36px;
        }
    }
</style>
@endpush

@section('content')
<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <div>
                <h1 class="page-title">🔧 Maintenance Requests</h1>
                <p class="page-subtitle">Manage all maintenance requests across all buildings</p>
            </div>
            <!-- View toggle commented out as in original -->
        </div>
    </div>

    <!-- Success/Error Messages (using layout's toast system) -->
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Utilities.showToast('{{ session("success") }}', 'success');
            });
        </script>
    @endif
    
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Utilities.showToast('{{ session("error") }}', 'error');
            });
        </script>
    @endif

    <!-- Stats Overview -->
    <div class="stats-grid">
        @php
            use App\Models\MaintenanceRequest;
            
            $totalOpen = $requests->whereNotIn('status', ['completed', 'cancelled'])->count();
            $totalOverdue = $requests->filter(function($r) { 
                return method_exists($r, 'isOverdue') && $r->isOverdue(); 
            })->count();
            $completedThisMonth = $requests->where('status', 'completed')
                ->filter(function($r) {
                    return $r->completion_date && $r->completion_date->month == now()->month;
                })->count();
            $emergencyCount = $requests->where('priority', 'emergency')->whereNotIn('status', ['completed', 'cancelled'])->count();
            
            $lowCount = $requests->where('priority', 'low')->whereNotIn('status', ['completed', 'cancelled'])->count();
            $mediumCount = $requests->where('priority', 'medium')->whereNotIn('status', ['completed', 'cancelled'])->count();
            $highCount = $requests->where('priority', 'high')->whereNotIn('status', ['completed', 'cancelled'])->count();
        @endphp
        
        <div class="stat-card">
            <div class="stat-value">{{ $totalOpen }}</div>
            <div class="stat-label">Open Requests</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $totalOverdue }}</div>
            <div class="stat-label">Overdue</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $emergencyCount }}</div>
            <div class="stat-label">Emergency</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $completedThisMonth }}</div>
            <div class="stat-label">Completed This Month</div>
        </div>
    </div>

    <!-- Filter Section with New Request Button -->
    <div class="filter-section {{ request('building_id') || request('priority') ? 'active-filter' : '' }}">
        <div class="filter-header">
            <div class="filter-title">
                <span>🔍</span>
                <h3>Filter Requests</h3>
            </div>
            
            <div class="filter-form">
                <form action="{{ route('maintenance-requests.index') }}" method="GET" style="display: flex; gap: 10px; width: 100%;">
                    <select name="building_id" onchange="this.form.submit()" class="filter-select">
                        <option value="">All Buildings ({{ $requests->total() }} requests)</option>
                        @php
                            $buildings = \App\Models\Building::all();
                            foreach($buildings as $building) {
                                $count = $requests->where('unit.building_id', $building->id)->count();
                                if($count > 0) {
                                    echo '<option value="'.$building->id.'" '. (request('building_id') == $building->id ? 'selected' : '') .'>';
                                    echo $building->name . ' (' . $count . ')';
                                    echo '</option>';
                                }
                            }
                        @endphp
                    </select>
                    
                    <select name="priority" onchange="this.form.submit()" class="filter-select">
                        <option value="">All Priorities</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low ({{ $lowCount }})</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium ({{ $mediumCount }})</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High ({{ $highCount }})</option>
                        <option value="emergency" {{ request('priority') == 'emergency' ? 'selected' : '' }}>Emergency ({{ $emergencyCount }})</option>
                    </select>
                    
                    @if(request('building_id') || request('priority'))
                        <a href="{{ route('maintenance-requests.index') }}" class="clear-filter-btn">
                            ✕ Clear
                        </a>
                    @endif
                </form>
                
                <!-- New Request Button -->
                <a href="{{ route('maintenance-requests.create', request()->only(['building_id'])) }}" class="new-request-btn">
                    ➕ New Request
                </a>
            </div>
        </div>
    </div>

    <!-- Page Info -->
    <div class="stats-info">
        @if($requests->total() > 0)
            <strong>{{ $requests->firstItem() }}-{{ $requests->lastItem() }}</strong> of 
            <strong>{{ $requests->total() }}</strong> requests
        @else
            <strong>0</strong> requests found
        @endif
        @if(request('building_id'))
            @php
                $buildingName = \App\Models\Building::find(request('building_id'))?->name;
            @endphp
            <span style="margin-left: 10px; padding: 4px 10px; background: #e8f4fc; border-radius: 20px; font-size: 12px;">
                🏢 {{ $buildingName }}
            </span>
        @endif
        @if(request('priority'))
            <span style="margin-left: 10px; padding: 4px 10px; background: #e8f4fc; border-radius: 20px; font-size: 12px;">
                ⚠️ {{ ucfirst(request('priority')) }}
            </span>
        @endif
    </div>

    <!-- Maintenance Requests Grid View -->
    <div class="requests-grid" id="gridView">
        @if($requests->count() > 0)
            @foreach($requests as $index => $request)
            @php
                // Priority configuration
                $priorityConfig = [
                    'emergency' => ['class' => 'priority-emergency', 'icon' => '🚨', 'label' => 'Emergency'],
                    'high' => ['class' => 'priority-high', 'icon' => '🔥', 'label' => 'High'],
                    'medium' => ['class' => 'priority-medium', 'icon' => '⚡', 'label' => 'Medium'],
                    'low' => ['class' => 'priority-low', 'icon' => '🐢', 'label' => 'Low'],
                ];
                
                $priority = $priorityConfig[$request->priority] ?? ['class' => 'priority-default', 'icon' => '⚠️', 'label' => ucfirst($request->priority)];
                
                $statusLabels = [
                    'submitted' => 'Submitted',
                    'assigned' => 'Assigned',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled'
                ];
                
                $statusClass = 'status-' . str_replace('_', '', $request->status);
                
                // Calculate days open as whole number
                $daysOpen = (int) $request->created_at->diffInDays(now());
                $daysClass = $daysOpen > 7 ? 'days-danger' : ($daysOpen > 3 ? 'days-warning' : 'days-success');
            @endphp

            <div class="request-card {{ method_exists($request, 'isOverdue') && $request->isOverdue() ? 'overdue' : '' }}" 
                 style="animation-delay: {{ $index * 0.05 }}s">
                
                <!-- Gradient Header -->
                <div class="request-photo-container {{ $priority['class'] }}">
                    <div class="priority-placeholder">
                        <div class="icon">{{ $priority['icon'] }}</div>
                        <div class="text">{{ $priority['label'] }}</div>
                    </div>
                </div>

                <!-- Card Content -->
                <div class="request-card-content">
                    <!-- Header -->
                    <div class="request-card-header">
                        <a href="{{ route('maintenance-requests.show', $request) }}" class="request-title">
                            #{{ $request->id }} - {{ Str::limit($request->title, 20) }}
                        </a>
                        <span class="status-badge-compact {{ $statusClass }}">
                            {{ $statusLabels[$request->status] ?? $request->status }}
                        </span>
                    </div>
                    
                    <!-- Location -->
                    <div class="request-location">
                        <span>🏢</span>
                        @if($request->unit && $request->unit->building)
                            <span>{{ Str::limit($request->unit->building->name, 15) }} • Unit {{ $request->unit->unit_number }}</span>
                        @else
                            <span>No unit assigned</span>
                        @endif
                    </div>
                    
                    <!-- Key Stats - Only 2 -->
                    <div class="request-stats">
                        <div class="stat-mini">
                            <div class="stat-mini-value">{{ $request->maintenanceCategory->name ?? 'N/A' }}</div>
                            <div class="stat-mini-label">Category</div>
                        </div>
                        <div class="stat-mini">
                            <div class="stat-mini-value">
                                <span class="days-badge {{ $daysClass }}">{{ $daysOpen }}d</span>
                            </div>
                            <div class="stat-mini-label">Open</div>
                        </div>
                    </div>
                    
                    <!-- Description Snippet (if exists) -->
                    @if($request->description)
                    <div class="request-description">
                        {{ Str::limit($request->description, 40) }}
                    </div>
                    @endif
                    
                    <!-- Actions - Icon Only -->
                    <div class="request-card-actions">
                        <a href="{{ route('maintenance-requests.show', $request) }}" 
                           class="btn-sm" 
                           style="background: #4a5568; color: white;">
                            👁️
                        </a>
                        <a href="{{ route('maintenance-requests.edit', $request) }}" 
                           class="btn-sm" 
                           style="background: #718096; color: white;">
                            ✏️
                        </a>
                        <button type="button" 
                                class="btn-sm" 
                                style="background: #2d3748; color: white;"
                                onclick="openDeleteModal({{ $request->id }}, '{{ addslashes($request->title) }}')">
                            🗑️
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        @else
            <div class="no-data" style="grid-column: 1 / -1;">
                <div class="no-data-icon">🔧</div>
                <h3>No maintenance requests found</h3>
                @if(request('building_id') || request('priority'))
                    <p>No requests match your filter criteria.</p>
                    <a href="{{ route('maintenance-requests.create', request()->only(['building_id'])) }}" class="btn-sm" style="background: #4a5568; color: white; margin-top: 15px; text-decoration: none; display: inline-block;">
                        ➕ Create New Request
                    </a>
                    <a href="{{ route('maintenance-requests.index') }}" class="btn-sm" style="background: #718096; color: white; margin-top: 15px; margin-left: 10px; text-decoration: none; display: inline-block;">
                        📋 Clear Filters
                    </a>
                @else
                    <p>Get started by creating your first maintenance request.</p>
                    <a href="{{ route('maintenance-requests.create') }}" class="btn-sm" style="background: #4a5568; color: white; margin-top: 15px; text-decoration: none; display: inline-block;">
                        ➕ Create First Request
                    </a>
                @endif
            </div>
        @endif
    </div>

    <!-- Maintenance Requests Table View -->
    <div class="requests-table hidden" id="tableView">
        @if($requests->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Request</th>
                        <th>Location</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Days Open</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                    @php
                        $priorityConfig = [
                            'emergency' => ['class' => 'priority-emergency', 'label' => 'Emergency'],
                            'high' => ['class' => 'priority-high', 'label' => 'High'],
                            'medium' => ['class' => 'priority-medium', 'label' => 'Medium'],
                            'low' => ['class' => 'priority-low', 'label' => 'Low'],
                        ];
                        $priority = $priorityConfig[$request->priority] ?? ['class' => 'priority-default', 'label' => ucfirst($request->priority)];
                        
                        $statusLabels = [
                            'submitted' => 'Submitted',
                            'assigned' => 'Assigned',
                            'in_progress' => 'In Progress',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled'
                        ];
                        
                        $daysOpen = (int) $request->created_at->diffInDays(now());
                        $daysClass = $daysOpen > 7 ? 'days-danger' : ($daysOpen > 3 ? 'days-warning' : 'days-success');
                    @endphp
                    <tr>
                        <td>
                            <div class="table-request-info">
                                <a href="{{ route('maintenance-requests.show', $request) }}" class="table-request-title">
                                    #{{ $request->id }} - {{ Str::limit($request->title, 30) }}
                                </a>
                                @if($request->description)
                                    <div style="color: #6c757d; font-size: 12px; margin-top: 4px;">
                                        {{ Str::limit($request->description, 40) }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="table-location-info">
                                @if($request->unit && $request->unit->building)
                                    <span class="table-building-name">
                                        {{ $request->unit->building->name }}
                                    </span>
                                    <span class="table-unit-number">
                                        Unit {{ $request->unit->unit_number }}
                                    </span>
                                @else
                                    <span>No unit assigned</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            {{ $request->maintenanceCategory->name ?? 'N/A' }}
                        </td>
                        <td>
                            <span class="priority-badge {{ $priority['class'] }}">
                                {{ $priority['label'] }}
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $request->status }}">
                                {{ $statusLabels[$request->status] ?? $request->status }}
                            </span>
                        </td>
                        <td>
                            <span class="days-badge {{ $daysClass }}">{{ $daysOpen }} days</span>
                        </td>
                        <td>
                            <div class="table-action-buttons">
                                <a href="{{ route('maintenance-requests.show', $request) }}" 
                                   class="btn-sm" 
                                   style="background: #4a5568; color: white;">
                                    👁️ View
                                </a>
                                <a href="{{ route('maintenance-requests.edit', $request) }}" 
                                   class="btn-sm" 
                                   style="background: #718096; color: white;">
                                    ✏️ Edit
                                </a>
                                <button type="button" 
                                        class="btn-sm" 
                                        style="background: #2d3748; color: white;"
                                        onclick="openDeleteModal({{ $request->id }}, '{{ addslashes($request->title) }}')">
                                    🗑️ Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">
                <div class="no-data-icon">🔧</div>
                <h3>No maintenance requests found</h3>
                @if(request('building_id') || request('priority'))
                    <p>No requests match your filter criteria.</p>
                    <a href="{{ route('maintenance-requests.create', request()->only(['building_id'])) }}" class="btn-sm" style="background: #4a5568; color: white; margin-top: 15px; text-decoration: none; display: inline-block;">
                        ➕ Create New Request
                    </a>
                    <a href="{{ route('maintenance-requests.index') }}" class="btn-sm" style="background: #718096; color: white; margin-top: 15px; margin-left: 10px; text-decoration: none; display: inline-block;">
                        📋 Clear Filters
                    </a>
                @else
                    <p>Get started by creating your first maintenance request.</p>
                    <a href="{{ route('maintenance-requests.create') }}" class="btn-sm" style="background: #4a5568; color: white; margin-top: 15px; text-decoration: none; display: inline-block;">
                        ➕ Create First Request
                    </a>
                @endif
            </div>
        @endif
    </div>
    
    <!-- Pagination -->
    @if($requests->hasPages())
        <div class="pagination-container">
            {{ $requests->withQueryString()->links() }}
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">⚠️</div>
                <h3 class="modal-title">Confirm Delete</h3>
            </div>
            <div class="modal-body" id="deleteModalMessage">
                Are you sure you want to delete this request? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button class="modal-btn cancel" onclick="closeDeleteModal()">Cancel</button>
                <form id="deleteForm" method="POST" style="flex: 1;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="modal-btn delete" style="width: 100%;">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // View switching functionality
    document.addEventListener('DOMContentLoaded', function() {
        const gridView = document.getElementById('gridView');
        const tableView = document.getElementById('tableView');
        const gridBtn = document.querySelector('[data-view="grid"]');
        const tableBtn = document.querySelector('[data-view="table"]');
        
        // Load saved preference from localStorage if toggle buttons exist
        if (gridBtn && tableBtn) {
            const savedView = localStorage.getItem('maintenanceView') || 'grid';
            
            // Set initial view based on saved preference
            if (savedView === 'table') {
                gridView?.classList.add('hidden');
                tableView?.classList.remove('hidden');
                gridBtn.classList.remove('active');
                tableBtn.classList.add('active');
            } else {
                gridView?.classList.remove('hidden');
                tableView?.classList.add('hidden');
                gridBtn.classList.add('active');
                tableBtn.classList.remove('active');
            }
            
            // Add click listeners
            gridBtn.addEventListener('click', function() {
                gridView?.classList.remove('hidden');
                tableView?.classList.add('hidden');
                gridBtn.classList.add('active');
                tableBtn.classList.remove('active');
                localStorage.setItem('maintenanceView', 'grid');
            });
            
            tableBtn.addEventListener('click', function() {
                tableView?.classList.remove('hidden');
                gridView?.classList.add('hidden');
                tableBtn.classList.add('active');
                gridBtn.classList.remove('active');
                localStorage.setItem('maintenanceView', 'table');
            });
        } else {
            // Just ensure grid view is visible if no toggle buttons
            if (gridView) gridView.classList.remove('hidden');
            if (tableView) tableView.classList.add('hidden');
        }

        // Delete modal functions
        window.openDeleteModal = function(requestId, requestTitle) {
            document.getElementById('deleteModalMessage').innerHTML = `Are you sure you want to delete "<strong>${requestTitle}</strong>"?<br>This action cannot be undone.`;
            document.getElementById('deleteForm').action = `/maintenance-requests/${requestId}`;
            document.getElementById('deleteModal').style.display = 'flex';
        };

        window.closeDeleteModal = function() {
            document.getElementById('deleteModal').style.display = 'none';
        };

        // Add animation to cards on load
        const cards = document.querySelectorAll('.request-card');
        cards.forEach(card => {
            card.style.animationPlayState = 'running';
        });
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('deleteModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    };

    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('deleteModal');
            if (modal) modal.style.display = 'none';
        }
    });

    // Add slide animation if not already in layout
    if (!document.querySelector('#slideAnimation')) {
        const style = document.createElement('style');
        style.id = 'slideAnimation';
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0%); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }
</script>
@endpush