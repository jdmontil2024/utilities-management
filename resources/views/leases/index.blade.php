@extends('layouts.app')

@section('title', 'Leases Management - Utility Wise')

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

    /* Leases Grid */
    .leases-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 30px;
    }

    /* Leases Table View */
    .leases-table {
        margin-bottom: 30px;
        overflow-x: auto;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        border: 1px solid #dee2e6;
    }

    .leases-table table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1200px;
    }

    .leases-table th {
        background: #f8f9fa;
        padding: 18px 15px;
        text-align: left;
        font-weight: 600;
        color: #2c3e50;
        border: 1px solid #dee2e6;
        font-size: 14px;
        white-space: nowrap;
    }

    .leases-table td {
        padding: 16px 15px;
        border: 1px solid #e9ecef;
        vertical-align: middle;
        font-size: 14px;
        line-height: 1.4;
    }

    .leases-table tbody tr:hover td {
        background: #f8f9fa;
        border-color: #cfe2ff;
    }

    /* Hidden class for toggling views */
    .hidden {
        display: none !important;
    }

    /* Lease Card Styles */
    .lease-card {
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

    .lease-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,.15);
    }

    /* Lease Header with Status */
    .lease-card-header {
        height: 140px;
        width: 100%;
        overflow: hidden;
        position: relative;
        background: linear-gradient(135deg, #4a5568, #2d3748);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px;
        color: white;
    }

    .lease-number {
        font-size: 16px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .lease-number span {
        opacity: 0.9;
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

    .status-active {
        background: #d4edda;
        color: #155724;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-expired {
        background: #f8d7da;
        color: #721c24;
    }

    .status-terminated {
        background: #e8f4fc;
        color: #2c3e50;
    }

    /* Card Content */
    .lease-card-content {
        padding: 12px 15px;
    }

    /* Tenant Name */
    .tenant-name-link {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        text-decoration: none;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .tenant-name-link:hover {
        color: #3498db;
    }

    /* Unit Info */
    .lease-unit-info {
        display: flex;
        align-items: center;
        gap: 4px;
        margin-bottom: 12px;
        font-size: 12px;
        color: #6c757d;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .lease-unit-info a {
        color: #6c757d;
        text-decoration: none;
    }

    .lease-unit-info a:hover {
        color: #3498db;
    }

    /* Key Stats */
    .lease-stats {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        padding: 8px 0;
        border-top: 1px solid #f0f0f0;
        border-bottom: 1px solid #f0f0f0;
    }

    .stat-mini {
        text-align: center;
        flex: 1;
    }

    .stat-mini-value {
        font-size: 16px;
        font-weight: 700;
        color: #2c3e50;
        line-height: 1.2;
    }

    .stat-mini-label {
        font-size: 10px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    /* Lease End with Days Badge */
    .lease-end {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: 12px;
    }

    .lease-date {
        color: #495057;
    }

    .days-badge {
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
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

    /* Card Actions */
    .lease-card-actions {
        display: flex;
        gap: 6px;
    }

    .lease-card-actions .btn-sm {
        flex: 1;
        height: 28px;
        font-size: 11px;
        padding: 0 8px;
        min-width: 0;
    }

    /* Table View Specific Styles */
    .table-lease-number {
        font-weight: 600;
        color: #2c3e50;
        text-decoration: none;
        display: block;
        margin-bottom: 5px;
    }

    .table-lease-number:hover {
        color: #3498db;
    }

    .table-lease-created {
        color: #6c757d;
        font-size: 12px;
    }

    .table-tenant-name {
        font-weight: 600;
        color: #2c3e50;
        text-decoration: none;
        display: block;
        margin-bottom: 5px;
    }

    .table-tenant-name:hover {
        color: #3498db;
    }

    .table-tenant-email {
        color: #6c757d;
        font-size: 12px;
    }

    .table-unit-info {
        display: flex;
        flex-direction: column;
    }

    .table-unit-number {
        font-weight: 600;
        color: #2c3e50;
        text-decoration: none;
    }

    .table-unit-number:hover {
        color: #3498db;
    }

    .table-building-name {
        color: #6c757d;
        font-size: 12px;
        margin-top: 2px;
    }

    .table-lease-dates {
        display: flex;
        flex-direction: column;
    }

    .lease-date-arrow {
        color: #6c757d;
        margin: 2px 0;
        font-size: 12px;
    }

    .table-rent {
        font-weight: 600;
        color: #2c3e50;
        font-size: 14px;
    }

    /* Status badges - for table view only */
    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        text-align: center;
        min-width: 100px;
    }

    /* Buttons */
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

    .add-lease-btn {
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

    .add-lease-btn:hover {
        background: #2d3748;
        border-color: #2d3748;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,.1);
        color: white;
        text-decoration: none;
    }

    .export-btn {
        padding: 10px 20px;
        background: #718096;
        border: 1px solid #718096;
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

    .export-btn:hover {
        background: #4a5568;
        border-color: #4a5568;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,.1);
        color: white;
        text-decoration: none;
    }

    /* Table Action Buttons */
    .table-action-buttons {
        display: flex;
        gap: 4px;
        min-width: 240px;
    }

    .table-action-buttons .btn-sm {
        height: 32px;
        min-width: 70px;
        flex: 1;
    }

    /* Page Actions */
    .page-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding: 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        border: 1px solid #dee2e6;
    }

    .stats-info {
        font-size: 14px;
        color: #6c757d;
    }

    .stats-info strong {
        color: #2c3e50;
    }

    .action-button-group {
        display: flex;
        align-items: center;
        gap: 10px;
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

    /* Filter by Building Section */
    .filter-building-section {
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

    .building-stats {
        display: flex;
        gap: 20px;
        margin-left: auto;
        flex-wrap: wrap;
    }

    .building-stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .building-stat-label {
        font-size: 12px;
        color: #6c757d;
    }

    .building-stat-value {
        font-weight: 600;
        color: #2c3e50;
    }

    .active-filter {
        background: #e8f4fc;
        border-left: 4px solid #4a5568;
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

    /* Delete form styling */
    .delete-form {
        margin: 0;
        padding: 0;
        display: flex;
        flex: 1;
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

    @keyframes slideDown {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Responsive */
    @media (max-width: 1400px) {
        .leases-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 1100px) {
        .leases-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .page-content {
            padding: 20px 15px !important;
        }

        .leases-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .page-actions {
            flex-direction: column;
            gap: 15px;
            text-align: center;
            padding: 15px;
        }

        .action-button-group {
            width: 100%;
            justify-content: center;
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
        
        .export-btn,
        .add-lease-btn {
            width: 100%;
            justify-content: center;
        }

        .building-stats {
            margin-left: 0;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .lease-card-actions {
            flex-direction: row;
            gap: 6px;
        }

        .lease-card-actions .btn-sm {
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
        
        .lease-card {
            margin: 0;
        }

        .lease-card-actions .btn-sm {
            height: 32px;
        }

        .table-action-buttons .btn-sm {
            height: 36px;
        }
        
        .building-stats {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        
        .action-button-group {
            flex-direction: column;
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
                <h1 class="page-title">📄 Leases Management</h1>
                <p class="page-subtitle">Manage all lease agreements across all buildings</p>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="stats-grid">
        @php
            $totalActive = $leases->where('lease_status', 'active')->count();
            
            $expiringSoon = $leases
                ->where('lease_status', 'active')
                ->filter(function($lease) {
                    return $lease->end_date && 
                           $lease->end_date->between(now(), now()->addDays(30));
                })->count();
                
            $expired = $leases->where('lease_status', 'expired')->count() + 
                      $leases->where('lease_status', 'active')
                             ->filter(function($lease) {
                                 return $lease->end_date && $lease->end_date->isPast();
                             })->count();
                
            $monthlyRevenue = $leases->where('lease_status', 'active')->sum('monthly_rent');
        @endphp
        
        <div class="stat-card">
            <div class="stat-value">{{ $totalActive }}</div>
            <div class="stat-label">Active Leases</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $expiringSoon }}</div>
            <div class="stat-label">Expiring Soon</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $expired }}</div>
            <div class="stat-label">Expired Leases</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">₱{{ number_format($monthlyRevenue, 0) }}</div>
            <div class="stat-label">Monthly Revenue</div>
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

    <!-- Filter by Building Section with Add Lease Button -->
    <div class="filter-building-section {{ request('building_id') ? 'active-filter' : '' }}">
        <div class="filter-header">
            <div class="filter-title">
                <span>🏢</span>
                <h3>Filter by Building</h3>
            </div>
            
            <div class="filter-form">
                <form action="{{ route('leases.index') }}" method="GET" style="display: flex; gap: 10px; width: 100%;">
                    <select name="building_id" onchange="this.form.submit()" class="filter-select">
                        <option value="">All Buildings ({{ $leases->total() }} leases)</option>
                        @foreach($buildings ?? [] as $building)
                            <option value="{{ $building->id }}" {{ request('building_id') == $building->id ? 'selected' : '' }}>
                                {{ $building->name }} ({{ $building->leases_count ?? 0 }} leases)
                            </option>
                        @endforeach
                    </select>

                    <select name="status" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                    </select>
                    
                    @if(request('building_id') || request('status'))
                        <a href="{{ route('leases.index') }}" class="clear-filter-btn">
                            ✕ Clear
                        </a>
                    @endif
                </form>
                
                <!-- Add Lease Button -->
                <a href="{{ route('leases.create', request('building_id') ? ['building_id' => request('building_id')] : []) }}" class="add-lease-btn">
                    ➕ Add Lease
                </a>
            </div>
            
            @if(request('building_id') && isset($selectedBuilding))
                @php
                    $buildingLeases = $selectedBuilding->leases;
                    $buildingActive = $buildingLeases->where('lease_status', 'active')->count();
                    
                    $buildingExpiring = $buildingLeases
                        ->where('lease_status', 'active')
                        ->filter(function($lease) {
                            return $lease->end_date && 
                                   $lease->end_date->between(now(), now()->addDays(30));
                        })->count();
                        
                    $buildingRevenue = $buildingLeases->where('lease_status', 'active')->sum('monthly_rent');
                @endphp
                <div class="building-stats">
                    <div class="building-stat-item">
                        <span class="building-stat-label">📍</span>
                        <span class="building-stat-value">{{ $selectedBuilding->city }}</span>
                    </div>
                    <div class="building-stat-item">
                        <span class="building-stat-label">Active:</span>
                        <span class="building-stat-value" style="color: #155724;">{{ $buildingActive }}</span>
                    </div>
                    <div class="building-stat-item">
                        <span class="building-stat-label">Expiring:</span>
                        <span class="building-stat-value" style="color: #856404;">{{ $buildingExpiring }}</span>
                    </div>
                    <div class="building-stat-item">
                        <span class="building-stat-label">Revenue:</span>
                        <span class="building-stat-value">₱{{ number_format($buildingRevenue, 0) }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Leases Content -->
    @if($leases->count() > 0)
        <!-- Grid View -->
        <div class="leases-grid" id="gridView">
            @foreach($leases as $index => $lease)
            @php
                $statusColors = [
                    'active' => 'status-active',
                    'pending' => 'status-pending',
                    'expired' => 'status-expired',
                    'terminated' => 'status-terminated',
                ];
                $statusClass = $statusColors[$lease->lease_status] ?? 'status-active';
                
                // Calculate days left as whole number
                $daysLeft = 0;
                if ($lease->end_date && $lease->lease_status === 'active') {
                    $leaseEndDate = $lease->end_date;
                    if (is_string($leaseEndDate)) {
                        $leaseEndDate = \Carbon\Carbon::parse($leaseEndDate);
                    }
                    $daysLeft = (int) now()->startOfDay()->diffInDays($leaseEndDate->startOfDay(), false);
                    $daysLeft = $daysLeft > 0 ? $daysLeft : 0;
                }
                $daysClass = $daysLeft <= 7 ? 'days-danger' : ($daysLeft <= 30 ? 'days-warning' : 'days-success');
                
                $formattedStart = $lease->start_date ? (\Carbon\Carbon::parse($lease->start_date)->format('M d, Y')) : 'N/A';
                $formattedEnd = $lease->end_date ? (\Carbon\Carbon::parse($lease->end_date)->format('M d, Y')) : 'N/A';
            @endphp
            
            <div class="lease-card" style="animation-delay: {{ $index * 0.05 }}s">
                <!-- Lease Header -->
                <div class="lease-card-header">
                    <div class="lease-number">
                        <span>📄</span>
                        {{ $lease->lease_number }}
                    </div>
                    <span class="status-badge-compact {{ $statusClass }}">
                        {{ $lease->status_label ?? ucfirst($lease->lease_status) }}
                    </span>
                </div>
                
                <!-- Card Content -->
                <div class="lease-card-content">
                    <!-- Tenant Info -->
                    <div class="tenant-card-header" style="margin-bottom: 8px;">
                        <a href="{{ route('tenants.show', $lease->tenant) }}" class="tenant-name-link">
                            {{ $lease->tenant->full_name ?? 'N/A' }}
                        </a>
                    </div>
                    
                    <!-- Unit Info -->
                    <div class="lease-unit-info">
                        <span>🏢</span>
                        @if($lease->unit)
                            <a href="{{ route('units.show', $lease->unit) }}">
                                Unit {{ $lease->unit->unit_number ?? 'N/A' }}
                            </a>
                            <span>• {{ $lease->unit->building->name ?? 'No building' }}</span>
                        @else
                            <span>No unit assigned</span>
                        @endif
                    </div>
                    
                    <!-- Key Stats - Rent & Duration -->
                    <div class="lease-stats">
                        <div class="stat-mini">
                            <div class="stat-mini-value">₱{{ number_format($lease->monthly_rent / 1000, 1) }}k</div>
                            <div class="stat-mini-label">Rent</div>
                        </div>
                        <div class="stat-mini">
                            <div class="stat-mini-value">
                                @if($lease->start_date && $lease->end_date)
                                    @php
                                        $startDate = is_string($lease->start_date) ? \Carbon\Carbon::parse($lease->start_date) : $lease->start_date;
                                        $endDate = is_string($lease->end_date) ? \Carbon\Carbon::parse($lease->end_date) : $lease->end_date;
                                        $months = floor($startDate->diffInMonths($endDate));
                                    @endphp
                                    {{ $months }}mo
                                @else
                                    N/A
                                @endif
                            </div>
                            <div class="stat-mini-label">Term</div>
                        </div>
                    </div>
                    
                    <!-- Lease End with Days -->
                    @if($lease->end_date)
                    <div class="lease-end">
                        <span class="lease-date">📅 {{ $formattedEnd }}</span>
                        @if($lease->lease_status === 'active' && $daysLeft > 0)
                            <span class="days-badge {{ $daysClass }}">{{ $daysLeft }}d left</span>
                        @elseif($lease->lease_status === 'expired' || ($lease->end_date && \Carbon\Carbon::parse($lease->end_date)->isPast()))
                            <span class="days-badge days-danger">Expired</span>
                        @endif
                    </div>
                    @endif
                    
                    <!-- Actions - Icon Only -->
                    <div class="lease-card-actions">
                        <a href="{{ route('leases.show', $lease) }}" 
                           class="btn-sm" 
                           style="background: #4a5568; color: white;">
                            👁️
                        </a>
                        <a href="{{ route('leases.edit', $lease) }}" 
                           class="btn-sm" 
                           style="background: #718096; color: white;">
                            ✏️
                        </a>
                        @if($lease->lease_status === 'active')
                        <form action="{{ route('leases.terminate', $lease) }}" method="POST" class="delete-form" 
                              onsubmit="return confirmTerminateLease(this, '{{ addslashes($lease->lease_number) }}')">
                            @csrf
                            <button type="submit" 
                                    class="btn-sm" 
                                    style="background: #856404; color: white;">
                                ⛔
                            </button>
                        </form>
                        @endif
                        <form action="{{ route('leases.destroy', $lease) }}" method="POST" class="delete-form" 
                              onsubmit="return confirmDeleteLease(this, '{{ addslashes($lease->lease_number) }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="btn-sm" 
                                    style="background: #2d3748; color: white;">
                                🗑️
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Table View -->
        <div class="leases-table hidden" id="tableView">
            <table>
                <thead>
                    <tr>
                        <th>Lease #</th>
                        <th>Tenant</th>
                        <th>Unit</th>
                        <th>Lease Period</th>
                        <th>Monthly Rent</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leases as $lease)
                    @php
                        $daysLeft = 0;
                        if ($lease->end_date && $lease->lease_status === 'active') {
                            $leaseEndDate = $lease->end_date;
                            if (is_string($leaseEndDate)) {
                                $leaseEndDate = \Carbon\Carbon::parse($leaseEndDate);
                            }
                            $daysLeft = (int) now()->startOfDay()->diffInDays($leaseEndDate->startOfDay(), false);
                            $daysLeft = $daysLeft > 0 ? $daysLeft : 0;
                        }
                        $daysClass = $daysLeft <= 7 ? 'days-danger' : ($daysLeft <= 30 ? 'days-warning' : 'days-success');
                        $formattedStart = $lease->start_date ? (\Carbon\Carbon::parse($lease->start_date)->format('M d, Y')) : 'N/A';
                        $formattedEnd = $lease->end_date ? (\Carbon\Carbon::parse($lease->end_date)->format('M d, Y')) : 'N/A';
                    @endphp
                    <tr>
                        <td>
                            <div class="table-lease-info">
                                <a href="{{ route('leases.show', $lease) }}" class="table-lease-number">
                                    {{ $lease->lease_number }}
                                </a>
                                <span class="table-lease-created">
                                    Created {{ $lease->created_at?->format('M d, Y') }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="table-tenant-info">
                                <a href="{{ route('tenants.show', $lease->tenant) }}" class="table-tenant-name">
                                    {{ $lease->tenant->full_name ?? 'N/A' }}
                                </a>
                                <span class="table-tenant-email">{{ $lease->tenant->email ?? '' }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="table-unit-info">
                                <a href="{{ route('units.show', $lease->unit) }}" class="table-unit-number">
                                    Unit {{ $lease->unit->unit_number ?? 'N/A' }}
                                </a>
                                <span class="table-building-name">{{ $lease->unit->building->name ?? '' }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="table-lease-dates">
                                <span class="lease-date">{{ $formattedStart }}</span>
                                <span class="lease-date-arrow">→</span>
                                <span class="lease-date">{{ $formattedEnd }}</span>
                                @if($lease->lease_status === 'active' && $daysLeft > 0)
                                    <span class="days-badge {{ $daysClass }}" style="margin-left: 0; margin-top: 4px;">
                                        {{ $daysLeft }} days left
                                    </span>
                                @elseif($lease->lease_status === 'expired' || ($lease->end_date && \Carbon\Carbon::parse($lease->end_date)->isPast()))
                                    <span class="days-badge days-danger" style="margin-left: 0; margin-top: 4px;">
                                        Expired
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="table-rent">₱{{ number_format($lease->monthly_rent ?? 0, 0) }}</span>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $lease->lease_status }}">
                                {{ $lease->status_label ?? ucfirst($lease->lease_status) }}
                            </span>
                        </td>
                        <td>
                            <div class="table-action-buttons">
                                <a href="{{ route('leases.show', $lease) }}" 
                                   class="btn-sm" 
                                   style="background: #4a5568; color: white;">
                                    👁️ View
                                </a>
                                <a href="{{ route('leases.edit', $lease) }}" 
                                   class="btn-sm" 
                                   style="background: #718096; color: white;">
                                    ✏️ Edit
                                </a>
                                @if($lease->lease_status === 'active')
                                <form action="{{ route('leases.terminate', $lease) }}" method="POST" class="delete-form" 
                                      onsubmit="return confirmTerminateLease(this, '{{ addslashes($lease->lease_number) }}')">
                                    @csrf
                                    <button type="submit" 
                                            class="btn-sm" 
                                            style="background: #856404; color: white;">
                                        ⛔ End
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('leases.destroy', $lease) }}" method="POST" class="delete-form" 
                                      onsubmit="return confirmDeleteLease(this, '{{ addslashes($lease->lease_number) }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn-sm" 
                                            style="background: #2d3748; color: white;">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($leases->hasPages())
            <div class="pagination-container">
                {{ $leases->withQueryString()->links() }}
            </div>
        @endif
    @else
        <div class="no-data">
            <div class="no-data-icon">📄</div>
            <h3>No leases found</h3>
            @if(request('building_id') && isset($selectedBuilding))
                <p>This building doesn't have any leases yet.</p>
                <a href="{{ route('leases.create', ['building_id' => request('building_id')]) }}" class="btn-sm" style="background: #4a5568; color: white; margin-top: 15px; padding: 10px 20px; text-decoration: none; display: inline-block;">
                    ➕ Add Lease to {{ $selectedBuilding->name }}
                </a>
            @else
                <p>Start by creating your first lease agreement.</p>
                <a href="{{ route('leases.create') }}" class="btn-sm" style="background: #4a5568; color: white; margin-top: 15px; padding: 10px 20px; text-decoration: none; display: inline-block;">
                    ➕ Create First Lease
                </a>
            @endif
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // View switching
    document.addEventListener('DOMContentLoaded', function() {
        const gridView = document.getElementById('gridView');
        const tableView = document.getElementById('tableView');
        const gridBtn = document.getElementById('gridBtn');
        const tableBtn = document.getElementById('tableBtn');
        
        // If view toggle buttons exist
        if (gridBtn && tableBtn) {
            const savedView = localStorage.getItem('leaseView') || 'grid';
            
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
            
            gridBtn.addEventListener('click', function() {
                gridView?.classList.remove('hidden');
                tableView?.classList.add('hidden');
                gridBtn.classList.add('active');
                tableBtn.classList.remove('active');
                localStorage.setItem('leaseView', 'grid');
            });
            
            tableBtn.addEventListener('click', function() {
                tableView?.classList.remove('hidden');
                gridView?.classList.add('hidden');
                tableBtn.classList.add('active');
                gridBtn.classList.remove('active');
                localStorage.setItem('leaseView', 'table');
            });
        } else {
            // Just use saved view without toggle buttons
            const savedView = localStorage.getItem('leaseView') || 'grid';
            
            if (savedView === 'table' && tableView && gridView) {
                gridView.classList.add('hidden');
                tableView.classList.remove('hidden');
            }
        }
    });

    // Confirm dialog
    function showConfirmDialog(message, title = 'Confirm Action') {
        return new Promise((resolve) => {
            const dialog = document.createElement('div');
            dialog.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
            `;
            
            const dialogContent = document.createElement('div');
            dialogContent.style.cssText = `
                background: white;
                border-radius: 8px;
                padding: 25px;
                width: 90%;
                max-width: 350px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                text-align: center;
            `;
            
            dialogContent.innerHTML = `
                <div style="margin-bottom: 20px;">
                    <div style="font-size: 42px; color: #ffc107; margin-bottom: 12px;">⚠️</div>
                    <h3 style="color: #2c3e50; margin-bottom: 8px;">${title}</h3>
                    <p style="color: #6c757d;">${message}</p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button id="confirmCancel" style="background: #718096; color: white; border: none; padding: 9px 18px; border-radius: 4px; cursor: pointer; flex: 1;">Cancel</button>
                    <button id="confirmAction" style="background: #2d3748; color: white; border: none; padding: 9px 18px; border-radius: 4px; cursor: pointer; flex: 1;">Continue</button>
                </div>
            `;
            
            dialog.appendChild(dialogContent);
            document.body.appendChild(dialog);
            document.body.style.overflow = 'hidden';
            
            dialog.querySelector('#confirmCancel').onclick = () => {
                document.body.removeChild(dialog);
                document.body.style.overflow = 'auto';
                resolve(false);
            };
            
            dialog.querySelector('#confirmAction').onclick = () => {
                document.body.removeChild(dialog);
                document.body.style.overflow = 'auto';
                resolve(true);
            };
        });
    }

    // Delete confirmation
    async function confirmDeleteLease(form, leaseNumber) {
        event.preventDefault();
        
        const confirmed = await showConfirmDialog(
            `Delete lease "${leaseNumber}"? This cannot be undone.`,
            'Delete Lease'
        );
        
        if (confirmed) {
            const button = form.querySelector('button[type="submit"]');
            button.innerHTML = '⏳';
            button.disabled = true;
            form.submit();
        }
        
        return false;
    }

    // Terminate confirmation
    async function confirmTerminateLease(form, leaseNumber) {
        event.preventDefault();
        
        const confirmed = await showConfirmDialog(
            `Terminate lease "${leaseNumber}"? This will mark the lease as terminated and free up the unit.`,
            'Terminate Lease'
        );
        
        if (confirmed) {
            const button = form.querySelector('button[type="submit"]');
            button.innerHTML = '⏳';
            button.disabled = true;
            form.submit();
        }
        
        return false;
    }

    // Add slide animation if not already in layout
    if (!document.querySelector('#slideAnimation')) {
        const style = document.createElement('style');
        style.id = 'slideAnimation';
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }
</script>
@endpush