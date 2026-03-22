@extends('layouts.app')

@section('title', 'Tenants Management - Utility Wise')

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

    /* Tenants Grid */
    .tenants-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 30px;
    }

    /* Tenants Table View */
    .tenants-table {
        margin-bottom: 30px;
        overflow-x: auto;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        border: 1px solid #dee2e6;
    }

    .tenants-table table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1000px;
    }

    .tenants-table th {
        background: #f8f9fa;
        padding: 18px 15px;
        text-align: left;
        font-weight: 600;
        color: #2c3e50;
        border: 1px solid #dee2e6;
        font-size: 14px;
        white-space: nowrap;
    }

    .tenants-table td {
        padding: 16px 15px;
        border: 1px solid #e9ecef;
        vertical-align: middle;
        font-size: 14px;
        line-height: 1.4;
    }

    .tenants-table tbody tr:hover td {
        background: #f8f9fa;
        border-color: #cfe2ff;
    }

    /* Hidden class for toggling views */
    .hidden {
        display: none !important;
    }

    /* Tenant Card Styles */
    .tenant-card {
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

    .tenant-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,.15);
    }

    /* Tenant Photo Container */
    .tenant-photo-container {
        height: 140px;
        width: 100%;
        overflow: hidden;
        position: relative;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .tenant-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .tenant-card:hover .tenant-photo {
        transform: scale(1.05);
    }

    .no-photo-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #e9ecef, #dee2e6);
        color: #6c757d;
        font-size: 14px;
        flex-direction: column;
        gap: 5px;
    }

    .no-photo-placeholder .icon {
        font-size: 36px;
        opacity: 0.5;
    }

    .no-photo-placeholder .initials {
        font-size: 16px;
        font-weight: 600;
    }

    /* Card Content */
    .tenant-card-content {
        padding: 12px 15px;
    }

    .tenant-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 8px;
    }

    .tenant-name {
        font-size: 16px;
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

    .tenant-name:hover {
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

    /* Location */
    .tenant-location {
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

    .tenant-location a {
        color: #6c757d;
        text-decoration: none;
    }

    .tenant-location a:hover {
        color: #3498db;
    }

    /* Key Stats - ONLY 2 */
    .tenant-stats {
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
    .tenant-card-actions {
        display: flex;
        gap: 6px;
    }

    .tenant-card-actions .btn-sm {
        flex: 1;
        height: 28px;
        font-size: 11px;
        padding: 0 8px;
        min-width: 0;
    }

    /* Table View Specific Styles */
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

    .table-tenant-since {
        color: #6c757d;
        font-size: 12px;
    }

    .table-contact-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
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

    .add-tenant-btn {
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

    .add-tenant-btn:hover {
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
        min-width: 220px;
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
        .tenants-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 1100px) {
        .tenants-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .page-content {
            padding: 20px 15px !important;
        }

        .tenants-grid {
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
        .add-tenant-btn {
            width: 100%;
            justify-content: center;
        }

        .building-stats {
            margin-left: 0;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .tenant-card-actions {
            flex-direction: row;
            gap: 6px;
        }

        .tenant-card-actions .btn-sm {
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
        
        .tenant-card {
            margin: 0;
        }

        .tenant-card-actions .btn-sm {
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
                <h1 class="page-title">👥 Tenants Management</h1>
                <p class="page-subtitle">Manage all tenants and lease agreements across all buildings</p>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="stats-grid">
        @php
            $totalActive = \App\Models\Tenant::where('lease_status', 'active')->count();
            
            $expiringSoon = \App\Models\Tenant::where('lease_status', 'active')
                ->whereBetween('lease_end_date', [now(), now()->addDays(30)])
                ->count();
                
            $expired = \App\Models\Tenant::where('lease_status', 'expired')
                ->orWhere(function($q) {
                    $q->where('lease_status', 'active')
                      ->where('lease_end_date', '<', now());
                })->count();
                
            $monthlyRevenue = \App\Models\Tenant::where('lease_status', 'active')->sum('monthly_rent');
        @endphp
        
        <div class="stat-card">
            <div class="stat-value">{{ $totalActive }}</div>
            <div class="stat-label">Active Tenants</div>
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

    <!-- Filter by Building Section with Add Tenant Button -->
    <div class="filter-building-section {{ request('building_id') ? 'active-filter' : '' }}">
        <div class="filter-header">
            <div class="filter-title">
                <span>🏢</span>
                <h3>Filter by Building</h3>
            </div>
            
            <div class="filter-form">
                <form action="{{ route('tenants.index') }}" method="GET" style="display: flex; gap: 10px; width: 100%;">
                    <select name="building_id" onchange="this.form.submit()" class="filter-select">
                        <option value="">All Buildings ({{ $totalActive + $expired }} tenants)</option>
                        @foreach($buildings ?? [] as $building)
                            <option value="{{ $building->id }}" {{ request('building_id') == $building->id ? 'selected' : '' }}>
                                {{ $building->name }} ({{ $building->tenants_count ?? 0 }} tenants)
                            </option>
                        @endforeach
                    </select>
                    
                    @if(request('building_id'))
                        <a href="{{ route('tenants.index') }}" class="clear-filter-btn">
                            ✕ Clear
                        </a>
                    @endif
                </form>
                
                <!-- Add Tenant Button -->
                <a href="{{ route('tenants.create', request('building_id') ? ['building_id' => request('building_id')] : []) }}" class="add-tenant-btn">
                    ➕ Add Tenant
                </a>
            </div>
            
            @if(request('building_id') && isset($selectedBuilding))
                @php
                    $buildingTenants = $selectedBuilding->tenants;
                    $buildingActive = $buildingTenants->where('lease_status', 'active')->count();
                    
                    $buildingExpiring = $buildingTenants
                        ->where('lease_status', 'active')
                        ->filter(function($tenant) {
                            return $tenant->lease_end_date && 
                                   $tenant->lease_end_date->between(now(), now()->addDays(30));
                        })->count();
                        
                    $buildingRevenue = $buildingTenants->where('lease_status', 'active')->sum('monthly_rent');
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

    <!-- Tenants Content -->
    @if($tenants->count() > 0)
        <!-- Grid View -->
        <div class="tenants-grid" id="gridView">
            @foreach($tenants as $index => $tenant)
            @php
                $statusColors = [
                    'active' => 'status-active',
                    'pending' => 'status-pending',
                    'expired' => 'status-expired',
                    'terminated' => 'status-terminated',
                ];
                $statusClass = $statusColors[$tenant->lease_status] ?? 'status-active';
                
                // Calculate days left as whole number
                $daysLeft = 0;
                if ($tenant->lease_end_date && $tenant->lease_status === 'active') {
                    // Check if it's a string or Carbon instance
                    $leaseEndDate = $tenant->lease_end_date;
                    if (is_string($leaseEndDate)) {
                        $leaseEndDate = \Carbon\Carbon::parse($leaseEndDate);
                    }
                    $daysLeft = (int) now()->startOfDay()->diffInDays($leaseEndDate->startOfDay(), false);
                    $daysLeft = $daysLeft > 0 ? $daysLeft : 0;
                }
                $daysClass = $daysLeft <= 7 ? 'days-danger' : ($daysLeft <= 30 ? 'days-warning' : 'days-success');
                
                $initials = collect(explode(' ', $tenant->full_name))
                    ->map(fn($name) => strtoupper(substr($name, 0, 1)))
                    ->take(2)
                    ->join('');
            @endphp
            
            <div class="tenant-card" style="animation-delay: {{ $index * 0.05 }}s">
                <!-- Tenant Photo -->
                <div class="tenant-photo-container">
                    @if($tenant->government_id && in_array(strtolower(pathinfo($tenant->government_id, PATHINFO_EXTENSION)), ['jpg','jpeg','png','gif','webp']))
                        <img src="{{ Storage::url($tenant->government_id) }}" 
                             alt="{{ $tenant->full_name }}" 
                             class="tenant-photo">
                    @else
                        <div class="no-photo-placeholder">
                            <div class="icon">👤</div>
                            <div class="initials">{{ $initials }}</div>
                        </div>
                    @endif
                </div>
                
                <!-- Card Content -->
                <div class="tenant-card-content">
                    <!-- Header -->
                    <div class="tenant-card-header">
                        <a href="{{ route('tenants.show', $tenant) }}" class="tenant-name">
                            {{ $tenant->full_name }}
                        </a>
                        <span class="status-badge-compact {{ $statusClass }}">
                            {{ $tenant->lease_status_label ?? ucfirst($tenant->lease_status) }}
                        </span>
                    </div>
                    
                    <!-- Location -->
                    <div class="tenant-location">
                        <span>🏢</span>
                        @if($tenant->building)
                            <a href="{{ route('buildings.show', $tenant->building) }}">
                                {{ Str::limit($tenant->building->name, 20) }}
                            </a>
                            @if($tenant->unit)
                                <span>• Unit {{ $tenant->unit->unit_number }}</span>
                            @endif
                        @else
                            <span>No building assigned</span>
                        @endif
                    </div>
                    
                    <!-- Key Stats - Rent & Phone Only -->
                    <div class="tenant-stats">
                        <div class="stat-mini">
                            <div class="stat-mini-value">₱{{ number_format($tenant->monthly_rent / 1000, 1) }}k</div>
                            <div class="stat-mini-label">Rent</div>
                        </div>
                        <div class="stat-mini">
                            <div class="stat-mini-value">{{ $tenant->phone ? substr($tenant->phone, 0, 4).'...' : 'N/A' }}</div>
                            <div class="stat-mini-label">Phone</div>
                        </div>
                    </div>
                    
                    <!-- Lease End with Days -->
                    @if($tenant->lease_end_date)
                    <div class="lease-end">
                        <span class="lease-date">📅 {{ $tenant->lease_end_date instanceof \Carbon\Carbon ? $tenant->lease_end_date->format('M d, Y') : \Carbon\Carbon::parse($tenant->lease_end_date)->format('M d, Y') }}</span>
                        @if($tenant->lease_status === 'active' && $daysLeft > 0)
                            <span class="days-badge {{ $daysClass }}">{{ $daysLeft }}d left</span>
                        @elseif($tenant->lease_status === 'expired' || ($tenant->lease_end_date && \Carbon\Carbon::parse($tenant->lease_end_date)->isPast()))
                            <span class="days-badge days-danger">Expired</span>
                        @endif
                    </div>
                    @endif
                    
                    <!-- Actions - Icon Only -->
                    <div class="tenant-card-actions">
                        <a href="{{ route('tenants.show', $tenant) }}" 
                           class="btn-sm" 
                           style="background: #4a5568; color: white;">
                            👁️
                        </a>
                        <a href="{{ route('tenants.edit', $tenant) }}" 
                           class="btn-sm" 
                           style="background: #718096; color: white;">
                            ✏️
                        </a>
                        <form action="{{ route('tenants.destroy', $tenant) }}" method="POST" class="delete-form" 
                              onsubmit="return confirmDeleteTenant(this, '{{ addslashes($tenant->full_name) }}')">
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
        <div class="tenants-table hidden" id="tableView">
            <table>
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>Contact</th>
                        <th>Location</th>
                        <th>Lease Period</th>
                        <th>Monthly Rent</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tenants as $tenant)
                    @php
                        $daysLeft = 0;
                        if ($tenant->lease_end_date && $tenant->lease_status === 'active') {
                            // Check if it's a string or Carbon instance
                            $leaseEndDate = $tenant->lease_end_date;
                            if (is_string($leaseEndDate)) {
                                $leaseEndDate = \Carbon\Carbon::parse($leaseEndDate);
                            }
                            $daysLeft = (int) now()->startOfDay()->diffInDays($leaseEndDate->startOfDay(), false);
                            $daysLeft = $daysLeft > 0 ? $daysLeft : 0;
                        }
                        $daysClass = $daysLeft <= 7 ? 'days-danger' : ($daysLeft <= 30 ? 'days-warning' : 'days-success');
                    @endphp
                    <tr>
                        <td>
                            <div class="table-tenant-info">
                                <a href="{{ route('tenants.show', $tenant) }}" class="table-tenant-name">
                                    {{ $tenant->full_name }}
                                </a>
                                <span class="table-tenant-since">
                                    Since {{ $tenant->lease_start_date ? (\Carbon\Carbon::parse($tenant->lease_start_date)->format('M Y')) : $tenant->created_at?->format('M Y') }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="table-contact-info">
                                <div style="color: #495057; display: flex; align-items: center; gap: 4px;">
                                    <span>📞</span> {{ $tenant->phone ?? 'N/A' }}
                                </div>
                                <div style="color: #6c757d; font-size: 12px; display: flex; align-items: center; gap: 4px;">
                                    <span>✉️</span> {{ $tenant->email }}
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="table-location-info">
                                @if($tenant->building)
                                    <a href="{{ route('buildings.show', $tenant->building) }}" class="table-building-name">
                                        {{ $tenant->building->name }}
                                    </a>
                                @else
                                    <span class="table-building-name">No Building</span>
                                @endif
                                @if($tenant->unit)
                                    <span class="table-unit-number">
                                        Unit {{ $tenant->unit->unit_number }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="table-lease-dates">
                                <span class="lease-date">{{ $tenant->lease_start_date ? \Carbon\Carbon::parse($tenant->lease_start_date)->format('M d, Y') : 'N/A' }}</span>
                                <span class="lease-date-arrow">→</span>
                                <span class="lease-date">{{ $tenant->lease_end_date ? \Carbon\Carbon::parse($tenant->lease_end_date)->format('M d, Y') : 'N/A' }}</span>
                                @if($tenant->lease_status === 'active' && $daysLeft > 0)
                                    <span class="days-badge {{ $daysClass }}" style="margin-left: 0; margin-top: 4px;">
                                        {{ $daysLeft }} days left
                                    </span>
                                @elseif($tenant->lease_status === 'expired' || ($tenant->lease_end_date && \Carbon\Carbon::parse($tenant->lease_end_date)->isPast()))
                                    <span class="days-badge days-danger" style="margin-left: 0; margin-top: 4px;">
                                        Expired
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="table-rent">₱{{ number_format($tenant->monthly_rent ?? 0, 0) }}</span>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $tenant->lease_status }}">
                                {{ $tenant->lease_status_label ?? ucfirst($tenant->lease_status) }}
                            </span>
                        </td>
                        <td>
                            <div class="table-action-buttons">
                                <a href="{{ route('tenants.show', $tenant) }}" 
                                   class="btn-sm" 
                                   style="background: #4a5568; color: white;">
                                    👁️ View
                                </a>
                                <a href="{{ route('tenants.edit', $tenant) }}" 
                                   class="btn-sm" 
                                   style="background: #718096; color: white;">
                                    ✏️ Edit
                                </a>
                                <form action="{{ route('tenants.destroy', $tenant) }}" method="POST" class="delete-form" 
                                      onsubmit="return confirmDeleteTenant(this, '{{ addslashes($tenant->full_name) }}')">
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
        @if($tenants->hasPages())
            <div class="pagination-container">
                {{ $tenants->withQueryString()->links() }}
            </div>
        @endif
    @else
        <div class="no-data">
            <div class="no-data-icon">👥</div>
            <h3>No tenants found</h3>
            @if(request('building_id') && isset($selectedBuilding))
                <p>This building doesn't have any tenants yet.</p>
                <a href="{{ route('tenants.create', ['building_id' => request('building_id')]) }}" class="btn-sm" style="background: #4a5568; color: white; margin-top: 15px; padding: 10px 20px; text-decoration: none; display: inline-block;">
                    ➕ Add Tenant to {{ $selectedBuilding->name }}
                </a>
            @else
                <p>Start by adding your first tenant to the system.</p>
                <a href="{{ route('tenants.create') }}" class="btn-sm" style="background: #4a5568; color: white; margin-top: 15px; padding: 10px 20px; text-decoration: none; display: inline-block;">
                    ➕ Add First Tenant
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
        
        const savedView = localStorage.getItem('tenantView') || 'grid';
        
        if (savedView === 'table' && tableView && gridView) {
            gridView.classList.add('hidden');
            tableView.classList.remove('hidden');
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
                    <button id="confirmDelete" style="background: #2d3748; color: white; border: none; padding: 9px 18px; border-radius: 4px; cursor: pointer; flex: 1;">Delete</button>
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
            
            dialog.querySelector('#confirmDelete').onclick = () => {
                document.body.removeChild(dialog);
                document.body.style.overflow = 'auto';
                resolve(true);
            };
        });
    }

    // Delete confirmation
    async function confirmDeleteTenant(form, tenantName) {
        event.preventDefault();
        
        const confirmed = await showConfirmDialog(
            `Delete "${tenantName}"? This cannot be undone.`,
            'Delete Tenant'
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