@extends('layouts.app')

@section('title', 'Units Management - Utility Wise')

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

    /* Units Grid */
    .units-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 30px;
    }

    /* Units Table View */
    .units-table {
        margin-bottom: 30px;
        overflow-x: auto;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        border: 1px solid #dee2e6;
    }

    .units-table table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1000px;
    }

    .units-table th {
        background: #f8f9fa;
        padding: 18px 15px;
        text-align: left;
        font-weight: 600;
        color: #2c3e50;
        border: 1px solid #dee2e6;
        font-size: 14px;
        white-space: nowrap;
    }

    .units-table td {
        padding: 16px 15px;
        border: 1px solid #e9ecef;
        vertical-align: middle;
        font-size: 14px;
        line-height: 1.4;
    }

    .units-table tbody tr:hover td {
        background: #f8f9fa;
        border-color: #cfe2ff;
    }

    /* Hidden class for toggling views */
    .hidden {
        display: none !important;
    }

    /* Unit Card Styles */
    .unit-card {
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

    .unit-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,.15);
    }

    /* Unit Photo Container - STATUS-BASED GRADIENTS */
    .unit-photo-container {
        height: 140px;
        width: 100%;
        overflow: hidden;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Status-based gradients */
    .unit-photo-container.status-vacant {
        background: linear-gradient(135deg, #6c757d, #495057);
    }

    .unit-photo-container.status-occupied {
        background: linear-gradient(135deg, #28a745, #1e7e34);
    }

    .unit-photo-container.status-maintenance,
    .unit-photo-container.status-under_maintenance {
        background: linear-gradient(135deg, #ffc107, #d39e00);
    }

    .unit-photo-container.status-renovation {
        background: linear-gradient(135deg, #17a2b8, #117a8b);
    }

    .unit-photo-container.status-reserved {
        background: linear-gradient(135deg, #6f42c1, #563d7c);
    }

    .unit-photo-container.status-ready {
        background: linear-gradient(135deg, #20c997, #198754);
    }

    .unit-photo-container.status-default {
        background: linear-gradient(135deg, #4a5568, #2d3748);
    }

    .status-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
        flex-direction: column;
        gap: 8px;
    }

    .status-placeholder .icon {
        font-size: 42px;
        opacity: 0.9;
    }

    .status-placeholder .text {
        font-weight: 600;
        letter-spacing: 0.5px;
        background: rgba(0,0,0,0.2);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
    }

    /* Card Content */
    .unit-card-content {
        padding: 12px 15px;
    }

    .unit-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 8px;
    }

    .unit-number {
        font-size: 16px;
        font-weight: 700;
        color: #2c3e50;
        text-decoration: none;
        line-height: 1.3;
    }

    .unit-number:hover {
        color: #3498db;
    }

    .unit-building {
        display: flex;
        align-items: center;
        gap: 4px;
        margin-bottom: 12px;
        font-size: 12px;
        color: #6c757d;
    }

    .unit-building span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Key Stats - ONLY 3 */
    .unit-stats {
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

    /* Card Actions */
    .unit-card-actions {
        display: flex;
        gap: 6px;
    }

    .unit-card-actions .btn-sm {
        flex: 1;
        height: 28px;
        font-size: 11px;
        padding: 0 8px;
        min-width: 0;
    }

    /* Table View Specific Styles */
    .table-unit-name {
        font-weight: 600;
        color: #2c3e50;
        text-decoration: none;
        display: block;
        margin-bottom: 5px;
    }

    .table-unit-name:hover {
        color: #3498db;
    }

    .table-location {
        color: #6c757d;
        font-size: 13px;
    }

    .table-address {
        color: #6c757d;
        font-size: 13px;
        line-height: 1.4;
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

    .status-vacant {
        background: #e9ecef;
        color: #495057;
        border: 1px solid #dee2e6;
    }

    .status-occupied {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .status-maintenance, .status-under_maintenance {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .status-renovation {
        background: #e8f4fc;
        color: #2c3e50;
        border: 1px solid #d1e4ff;
    }

    .status-reserved {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    .status-ready {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
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

    .add-unit-btn {
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

    .add-unit-btn:hover {
        background: #2d3748;
        border-color: #2d3748;
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

    .quick-access {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #eee;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    .quick-access-label {
        font-size: 12px;
        color: #6c757d;
        padding: 5px 0;
    }

    .quick-access-link {
        padding: 6px 14px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 20px;
        color: #495057;
        text-decoration: none;
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .quick-access-link:hover {
        background: #e9ecef;
        border-color: #4a5568;
        color: #2c3e50;
        text-decoration: none;
    }

    .unit-count-badge {
        background: white;
        padding: 2px 6px;
        border-radius: 12px;
        font-size: 11px;
        color: #2c3e50;
    }

    /* Active Filter Indicator */
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

    /* Animation */
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

    /* Responsive */
    @media (max-width: 1400px) {
        .units-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 1100px) {
        .units-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .page-content {
            padding: 20px 15px !important;
        }

        .units-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
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
        
        .add-unit-btn {
            width: 100%;
            justify-content: center;
        }
        
        .building-stats {
            margin-left: 0;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .unit-card-actions {
            flex-direction: row;
            gap: 6px;
        }

        .unit-card-actions .btn-sm {
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
        
        .unit-card {
            margin: 0;
        }

        .unit-card-actions {
            flex-direction: row;
            gap: 6px;
        }

        .unit-card-actions .btn-sm {
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
                <h1 class="page-title">🏠 Units Management</h1>
                <p class="page-subtitle">Manage all units across your buildings</p>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="stats-grid">
        @php
            $totalUnits = \App\Models\Unit::count();
            $occupiedUnits = \App\Models\Unit::where('status', 'occupied')->count();
            $vacantUnits = \App\Models\Unit::where('status', 'vacant')->count();
            $monthlyRevenue = \App\Models\Unit::sum('monthly_rent');
        @endphp
        
        <div class="stat-card">
            <div class="stat-value">{{ $totalUnits }}</div>
            <div class="stat-label">Total Units</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $occupiedUnits }}</div>
            <div class="stat-label">Occupied</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $vacantUnits }}</div>
            <div class="stat-label">Vacant</div>
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

    <!-- Filter by Building Section with Add Unit Button -->
    <div class="filter-building-section {{ request('building_id') ? 'active-filter' : '' }}">
        <div class="filter-header">
            <div class="filter-title">
                <span>🏢</span>
                <h3>Filter by Building</h3>
            </div>
            
            <div class="filter-form">
                <form action="{{ route('units.index') }}" method="GET" style="display: flex; gap: 10px; width: 100%;">
                    <select name="building_id" onchange="this.form.submit()" class="filter-select">
                        <option value="">All Buildings ({{ $totalUnits }} units)</option>
                        @foreach($buildings ?? [] as $building)
                            <option value="{{ $building->id }}" {{ request('building_id') == $building->id ? 'selected' : '' }}>
                                {{ $building->name }} ({{ $building->units_count }} units)
                            </option>
                        @endforeach
                    </select>
                    
                    @if(request('building_id'))
                        <a href="{{ route('units.index') }}" class="clear-filter-btn">
                            ✕ Clear
                        </a>
                    @endif
                </form>
                
                <!-- Add Unit Button -->
                <a href="{{ route('units.create', request('building_id') ? ['building_id' => request('building_id')] : []) }}" class="add-unit-btn">
                    ➕ Add Unit
                </a>
            </div>
            
            @if(request('building_id') && isset($selectedBuilding))
                @php
                    $buildingUnits = $selectedBuilding->units;
                    $buildingOccupied = $buildingUnits->where('status', 'occupied')->count();
                    $buildingVacant = $buildingUnits->where('status', 'vacant')->count();
                    $buildingRevenue = $buildingUnits->sum('monthly_rent');
                @endphp
                <div class="building-stats">
                    <div class="building-stat-item">
                        <span class="building-stat-label">📍</span>
                        <span class="building-stat-value">{{ $selectedBuilding->city }}</span>
                    </div>
                    <div class="building-stat-item">
                        <span class="building-stat-label">Occupied:</span>
                        <span class="building-stat-value" style="color: #155724;">{{ $buildingOccupied }}</span>
                    </div>
                    <div class="building-stat-item">
                        <span class="building-stat-label">Vacant:</span>
                        <span class="building-stat-value">{{ $buildingVacant }}</span>
                    </div>
                    <div class="building-stat-item">
                        <span class="building-stat-label">Revenue:</span>
                        <span class="building-stat-value">₱{{ number_format($buildingRevenue, 0) }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Units Content -->
    @if($units->count() > 0)
        <!-- Grid View -->
        <div class="units-grid" id="gridView">
            @foreach($units as $index => $unit)
            @php
                // Status configuration
                $statusConfig = [
                    'vacant' => ['class' => 'status-vacant', 'icon' => '📭', 'label' => 'Vacant'],
                    'occupied' => ['class' => 'status-occupied', 'icon' => '👤', 'label' => 'Occupied'],
                    'maintenance' => ['class' => 'status-maintenance', 'icon' => '🔧', 'label' => 'Maintenance'],
                    'under_maintenance' => ['class' => 'status-maintenance', 'icon' => '🔧', 'label' => 'Maintenance'],
                    'renovation' => ['class' => 'status-renovation', 'icon' => '🏗️', 'label' => 'Renovation'],
                    'reserved' => ['class' => 'status-reserved', 'icon' => '📅', 'label' => 'Reserved'],
                    'ready' => ['class' => 'status-ready', 'icon' => '✅', 'label' => 'Ready'],
                ];
                
                $status = $statusConfig[$unit->status] ?? ['class' => 'status-default', 'icon' => '🚪', 'label' => ucfirst($unit->status)];
            @endphp
            
            <div class="unit-card" style="animation-delay: {{ $index * 0.05 }}s">
                <!-- Unit Photo - Status Gradient -->
                <div class="unit-photo-container {{ $status['class'] }}">
                    <div class="status-placeholder">
                        <div class="icon">{{ $status['icon'] }}</div>
                        <div class="text">{{ $status['label'] }}</div>
                    </div>
                </div>
                
                <!-- Card Content -->
                <div class="unit-card-content">
                    <!-- Unit Number -->
                    <div class="unit-card-header">
                        <a href="{{ route('units.show', $unit) }}" class="unit-number">
                            Unit {{ $unit->unit_number }}
                        </a>
                    </div>
                    
                    <!-- Building Name -->
                    <div class="unit-building">
                        <span>🏢</span>
                        <span title="{{ $unit->building->name }}">{{ Str::limit($unit->building->name, 25) }}</span>
                    </div>
                    
                    <!-- Key Stats -->
                    <div class="unit-stats">
                        <div class="stat-mini">
                            <div class="stat-mini-value">₱{{ number_format($unit->monthly_rent / 1000, 1) }}k</div>
                            <div class="stat-mini-label">Rent</div>
                        </div>
                        <div class="stat-mini">
                            <div class="stat-mini-value">{{ $unit->bedrooms ?? '-' }}</div>
                            <div class="stat-mini-label">Beds</div>
                        </div>
                        <div class="stat-mini">
                            <div class="stat-mini-value">{{ $unit->bathrooms ?? '-' }}</div>
                            <div class="stat-mini-label">Baths</div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="unit-card-actions">
                        <a href="{{ route('units.show', $unit) }}" 
                           class="btn-sm" 
                           style="background: #4a5568; color: white;">
                            👁️
                        </a>
                        <a href="{{ route('units.edit', $unit) }}" 
                           class="btn-sm" 
                           style="background: #718096; color: white;">
                            ✏️
                        </a>
                        <form action="{{ route('units.destroy', $unit) }}" method="POST" class="delete-form" 
                              onsubmit="return confirmDeleteUnit(this, '{{ $unit->unit_number }}')">
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
        <div class="units-table hidden" id="tableView">
            <table>
                <thead>
                    <tr>
                        <th>Unit #</th>
                        <th>Building</th>
                        <th>Specifications</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($units as $unit)
                    @php
                        $statusConfig = [
                            'vacant' => ['label' => 'Vacant'],
                            'occupied' => ['label' => 'Occupied'],
                            'maintenance' => ['label' => 'Maintenance'],
                            'under_maintenance' => ['label' => 'Maintenance'],
                            'renovation' => ['label' => 'Renovation'],
                            'reserved' => ['label' => 'Reserved'],
                            'ready' => ['label' => 'Ready'],
                        ];
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('units.show', $unit) }}" class="table-unit-name">
                                Unit {{ $unit->unit_number }}
                            </a>
                        </td>
                        <td>
                            <div class="table-location">
                                {{ $unit->building->name }}
                            </div>
                            <div class="text-muted text-small">
                                {{ $unit->building->city }}
                            </div>
                        </td>
                        <td>
                            <div class="table-specs">
                                <div class="table-spec-item">
                                    <span>💰</span>
                                    <span class="table-spec-value">₱{{ number_format($unit->monthly_rent, 0) }}</span>
                                </div>
                                <div class="table-spec-item">
                                    <span>🛏️</span>
                                    <span class="table-spec-value">{{ $unit->bedrooms ?? '-' }}</span>
                                </div>
                                <div class="table-spec-item">
                                    <span>🚿</span>
                                    <span class="table-spec-value">{{ $unit->bathrooms ?? '-' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $unit->status }}">
                                {{ $statusConfig[$unit->status]['label'] ?? ucfirst($unit->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="table-action-buttons">
                                <a href="{{ route('units.show', $unit) }}" 
                                   class="btn-sm" 
                                   style="background: #4a5568; color: white;">
                                    👁️ View
                                </a>
                                <a href="{{ route('units.edit', $unit) }}" 
                                   class="btn-sm" 
                                   style="background: #718096; color: white;">
                                    ✏️ Edit
                                </a>
                                <form action="{{ route('units.destroy', $unit) }}" method="POST" class="delete-form" 
                                      onsubmit="return confirmDeleteUnit(this, '{{ $unit->unit_number }}')">
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
        @if($units->hasPages())
            <div class="pagination-container">
                {{ $units->withQueryString()->links() }}
            </div>
        @endif
    @else
        <div class="no-data">
            <div style="font-size: 48px; margin-bottom: 15px;">🚪</div>
            <h3>No units found</h3>
            @if(request('building_id') && isset($selectedBuilding))
                <p>This building doesn't have any units yet.</p>
                <a href="{{ route('units.create', ['building_id' => request('building_id')]) }}" class="btn-sm" style="background: #4a5568; color: white; margin-top: 15px; padding: 10px 20px; text-decoration: none; display: inline-block;">
                    ➕ Add Unit to {{ $selectedBuilding->name }}
                </a>
            @else
                <p>Start by adding your first unit to the system.</p>
                <a href="{{ route('units.create') }}" class="btn-sm" style="background: #4a5568; color: white; margin-top: 15px; padding: 10px 20px; text-decoration: none; display: inline-block;">
                    ➕ Add First Unit
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
        
        // You can add view toggle buttons here if needed
        const savedView = localStorage.getItem('unitView') || 'grid';
        
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
    async function confirmDeleteUnit(form, unitNumber) {
        event.preventDefault();
        
        const confirmed = await showConfirmDialog(
            `Delete Unit ${unitNumber}? This cannot be undone.`,
            'Delete Unit'
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