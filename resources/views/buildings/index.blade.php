@extends('layouts.app')

@section('title', 'Buildings Management - Utility Wise')

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

    /* Buildings Grid */
    .buildings-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 30px;
    }

    /* Buildings Table View */
    .buildings-table {
        margin-bottom: 30px;
        overflow-x: auto;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        border: 1px solid #dee2e6;
    }

    .buildings-table table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1000px;
    }

    .buildings-table th {
        background: #f8f9fa;
        padding: 18px 15px;
        text-align: left;
        font-weight: 600;
        color: #2c3e50;
        border: 1px solid #dee2e6;
        font-size: 14px;
        white-space: nowrap;
    }

    .buildings-table td {
        padding: 16px 15px;
        border: 1px solid #e9ecef;
        vertical-align: middle;
        font-size: 14px;
        line-height: 1.4;
    }

    .buildings-table tbody tr:hover td {
        background: #f8f9fa;
        border-color: #cfe2ff;
    }

    /* Hidden class for toggling views */
    .hidden {
        display: none !important;
    }

    /* Building Card Styles */
    .building-card {
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

    .building-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,.15);
    }

    /* Building Photo Container */
    .building-photo-container {
        height: 140px;
        width: 100%;
        overflow: hidden;
        position: relative;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .building-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .building-card:hover .building-photo {
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

    /* Card Content */
    .building-card-content {
        padding: 12px 15px;
    }

    .building-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 8px;
    }

    .building-name {
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

    .building-name:hover {
        color: #3498db;
    }

    /* Building Type - Minimal */
    .building-type-mini {
        display: flex;
        align-items: center;
        gap: 4px;
        margin-bottom: 12px;
        font-size: 12px;
        color: #6c757d;
    }

    .building-type-mini span {
        font-weight: 500;
    }

    /* Key Stats */
    .building-stats {
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

    /* Location */
    .building-location {
        font-size: 12px;
        color: #6c757d;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Card Actions */
    .building-card-actions {
        display: flex;
        gap: 6px;
    }

    .building-card-actions .btn-sm {
        flex: 1;
        height: 28px;
        font-size: 11px;
        padding: 0 8px;
        min-width: 0;
    }

    /* Table View Specific Styles */
    .table-building-name {
        font-weight: 600;
        color: #2c3e50;
        text-decoration: none;
        display: block;
        margin-bottom: 5px;
    }

    .table-building-name:hover {
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

    /* Status badges */
    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        text-align: center;
        min-width: 100px;
    }

    .status-active {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .status-inactive {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .status-under_construction {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .status-renovation {
        background: #e8f4fc;
        color: #2c3e50;
        border: 1px solid #d1e4ff;
    }

    /* Filter Section Styles */
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

    .add-building-btn {
        padding: 10px 20px;
        background: #4a5568;
        border: 1px solid #4a5568;
        border-radius: 6px;
        color: white;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .add-building-btn:hover {
        background: #2d3748;
        border-color: #2d3748;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,.1);
        color: white;
        text-decoration: none;
    }

    .active-filter {
        background: #e8f4fc;
        border-left: 4px solid #4a5568;
    }

    /* Type badges */
    .type-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 16px;
        font-size: 12px;
        font-weight: 500;
        background: #e9ecef;
        color: #495057;
    }

    .type-residential {
        background: #d4edda;
        color: #155724;
    }

    .type-commercial {
        background: #cce5ff;
        color: #004085;
    }

    .type-mixed {
        background: #fff3cd;
        color: #856404;
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
        .buildings-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 1100px) {
        .buildings-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .page-content {
            padding: 20px 15px !important;
        }

        .buildings-grid {
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
        
        .add-building-btn {
            width: 100%;
            justify-content: center;
        }

        .building-card-actions {
            flex-direction: row;
            gap: 6px;
        }

        .building-card-actions .btn-sm {
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
        
        .building-card {
            margin: 0;
        }

        .building-card-actions {
            flex-direction: row;
            gap: 6px;
        }

        .building-card-actions .btn-sm {
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
                <h1 class="page-title">🏢 Buildings Management</h1>
                <p class="page-subtitle">Manage all your properties efficiently</p>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="stats-grid">
        @php
            $totalBuildingsAll = \App\Models\Building::count();
            $activeBuildingsAll = \App\Models\Building::where('status', 'active')->count();
            $inProgressBuildingsAll = \App\Models\Building::whereIn('status', ['under_construction', 'renovation'])->count();
            $totalUnitsAllBuildings = \App\Models\Unit::count();
        @endphp
        
        <div class="stat-card">
            <div class="stat-value">{{ $totalBuildingsAll }}</div>
            <div class="stat-label">Total Buildings</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $activeBuildingsAll }}</div>
            <div class="stat-label">Active</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $inProgressBuildingsAll }}</div>
            <div class="stat-label">In Progress</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $totalUnitsAllBuildings }}</div>
            <div class="stat-label">Total Units</div>
        </div>
    </div>

    <!-- Filter by Building Type Section with Add Building Button -->
    <div class="filter-section {{ request('building_type') ? 'active-filter' : '' }}">
        <div class="filter-header">
            <div class="filter-title">
                <span>🏷️</span>
                <h3>Filter by Building Type</h3>
            </div>
            
            <div class="filter-form">
                <form action="{{ route('buildings.index') }}" method="GET" style="display: flex; gap: 10px; width: 100%;">
                    <select name="building_type" onchange="this.form.submit()" class="filter-select">
                        <option value="">All Types ({{ $buildings->total() }} buildings)</option>
                        <option value="residential" {{ request('building_type') == 'residential' ? 'selected' : '' }}>
                            Residential ({{ $typeCounts['residential'] ?? 0 }})
                        </option>
                        <option value="commercial" {{ request('building_type') == 'commercial' ? 'selected' : '' }}>
                            Commercial ({{ $typeCounts['commercial'] ?? 0 }})
                        </option>
                        <option value="mixed" {{ request('building_type') == 'mixed' ? 'selected' : '' }}>
                            Mixed-Use ({{ $typeCounts['mixed'] ?? 0 }})
                        </option>
                    </select>
                    
                    @if(request('building_type'))
                        <a href="{{ route('buildings.index') }}" class="clear-filter-btn">
                            ✕ Clear
                        </a>
                    @endif
                </form>
                
                <!-- Add Building Button -->
                <a href="{{ route('buildings.create') }}" class="add-building-btn">
                    ➕ Add Building
                </a>
            </div>
        </div>
    </div>

    <!-- Buildings Content -->
    @if($buildings->count() > 0)
        <!-- Grid View -->
        <div class="buildings-grid" id="gridView">
            @foreach($buildings as $index => $building)
            <div class="building-card" style="animation-delay: {{ $index * 0.05 }}s">
                <!-- Building Photo -->
                <div class="building-photo-container">
                    @php
                        $primaryPhoto = $building->photos()->where('is_primary', true)->first();
                    @endphp
                    
                    @if($primaryPhoto)
                        <img src="{{ Storage::url($primaryPhoto->path) }}" 
                             alt="{{ $building->name }}" 
                             class="building-photo"
                             title="{{ $building->name }}">
                    @else
                        <div class="no-photo-placeholder">
                            <div class="icon">🏢</div>
                            <div>No Photo</div>
                        </div>
                    @endif
                </div>
                
                <!-- Card Content -->
                <div class="building-card-content">
                    <!-- Header with Name -->
                    <div class="building-card-header">
                        <a href="{{ route('buildings.show', $building) }}" class="building-name" title="{{ $building->name }}">
                            {{ $building->name }}
                        </a>
                    </div>
                    
                    <!-- Building Type - Minimal -->
                    <div class="building-type-mini">
                        @php
                            $typeIcons = [
                                'residential' => '🏠',
                                'commercial' => '🏢',
                                'mixed' => '🔄'
                            ];
                            $typeLabels = [
                                'residential' => 'Residential',
                                'commercial' => 'Commercial',
                                'mixed' => 'Mixed-Use'
                            ];
                        @endphp
                        {{ $typeIcons[$building->building_type] ?? '🏷️' }}
                        <span>{{ $typeLabels[$building->building_type] ?? $building->building_type }}</span>
                    </div>
                    
                    <!-- Key Stats -->
                    <div class="building-stats">
                        <div class="stat-mini">
                            <div class="stat-mini-value">{{ $building->units_count ?? 0 }}</div>
                            <div class="stat-mini-label">Units</div>
                        </div>
                        <div class="stat-mini">
                            <div class="stat-mini-value">{{ $building->total_floors }}</div>
                            <div class="stat-mini-label">Floors</div>
                        </div>
                        <div class="stat-mini">
                            <div class="stat-mini-value">{{ $building->year_built }}</div>
                            <div class="stat-mini-label">Year</div>
                        </div>
                    </div>
                    
                    <!-- Location -->
                    <div class="building-location">
                        <span>📍</span>
                        <span>{{ $building->city }}{{ $building->state ? ', ' . $building->state : '' }}</span>
                    </div>
                    
                    <!-- Actions -->
                    <div class="building-card-actions">
                        <a href="{{ route('buildings.show', $building) }}" 
                           class="btn-sm" 
                           style="background: #4a5568; color: white;">
                            👁️
                        </a>
                        <a href="{{ route('buildings.edit', $building) }}" 
                           class="btn-sm" 
                           style="background: #718096; color: white;">
                            ✏️
                        </a>
                        <form action="{{ route('buildings.destroy', $building) }}" method="POST" class="delete-form" 
                              onsubmit="return confirmDelete(this, '{{ $building->name }}')">
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
        
        <!-- Table View (Hidden by default) -->
        <div class="buildings-table hidden" id="tableView">
            <table>
                <thead>
                    <tr>
                        <th>Building Name</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Specifications</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($buildings as $building)
                    <tr>
                        <td>
                            <a href="{{ route('buildings.show', $building) }}" class="table-building-name">
                                {{ $building->name }}
                            </a>
                            <div class="table-address">
                                {{ Str::limit($building->address, 40) }}
                            </div>
                        </td>
                        <td>
                            <div class="table-location">
                                {{ $building->city }}, {{ $building->state }}
                            </div>
                            <div class="text-muted text-small">
                                {{ $building->zip_code }}
                            </div>
                        </td>
                        <td>
                            @php
                                $typeIcons = [
                                    'residential' => '🏠',
                                    'commercial' => '🏢',
                                    'mixed' => '🔄'
                                ];
                                $typeLabels = [
                                    'residential' => 'Residential',
                                    'commercial' => 'Commercial',
                                    'mixed' => 'Mixed-Use'
                                ];
                            @endphp
                            <span class="type-badge type-{{ $building->building_type }}">
                                {{ $typeIcons[$building->building_type] ?? '🏷️' }} {{ $typeLabels[$building->building_type] ?? $building->building_type }}
                            </span>
                        </td>
                        <td>
                            <div class="table-specs">
                                <div class="table-spec-item">
                                    <span>🏢</span>
                                    <span class="table-spec-value">{{ $building->units_count ?? 0 }}</span>
                                    <span>Units</span>
                                </div>
                                <div class="table-spec-item">
                                    <span>🏗️</span>
                                    <span class="table-spec-value">{{ $building->total_floors }}</span>
                                    <span>Floors</span>
                                </div>
                                <div class="table-spec-item">
                                    <span>📅</span>
                                    <span class="table-spec-value">{{ $building->year_built }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $statusLabels = [
                                    'active' => 'Active',
                                    'inactive' => 'Inactive',
                                    'under_construction' => 'Construction',
                                    'renovation' => 'Renovation'
                                ];
                            @endphp
                            <span class="status-badge status-{{ $building->status }}">
                                {{ $statusLabels[$building->status] ?? $building->status }}
                            </span>
                        </td>
                        <td>
                            <div class="table-action-buttons">
                                <a href="{{ route('buildings.show', $building) }}" 
                                   class="btn-sm" 
                                   style="background: #4a5568; color: white;">
                                    👁️ View
                                </a>
                                <a href="{{ route('buildings.edit', $building) }}" 
                                   class="btn-sm" 
                                   style="background: #718096; color: white;">
                                    ✏️ Edit
                                </a>
                                <form action="{{ route('buildings.destroy', $building) }}" method="POST" class="delete-form" 
                                      onsubmit="return confirmDelete(this, '{{ $building->name }}')">
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
        @if($buildings->hasPages())
            <div class="pagination-container">
                {{ $buildings->withQueryString()->links() }}
            </div>
        @endif
    @else
        <div class="no-data">
            <div style="font-size: 48px; margin-bottom: 15px;">🏢</div>
            <h3>No buildings found</h3>
            @if(request('building_type'))
                <p>No {{ ucfirst(request('building_type')) }} buildings found.</p>
                <a href="{{ route('buildings.create', ['building_type' => request('building_type')]) }}" class="btn-sm" style="background: #4a5568; color: white; margin-top: 15px; padding: 10px 20px; text-decoration: none; display: inline-block;">
                    ➕ Add {{ ucfirst(request('building_type')) }} Building
                </a>
            @else
                <p>Start by adding your first property to the system.</p>
                <a href="{{ route('buildings.create') }}" class="btn-sm" style="background: #4a5568; color: white; margin-top: 15px; padding: 10px 20px; text-decoration: none; display: inline-block;">
                    ➕ Add First Building
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
        const savedView = localStorage.getItem('buildingView') || 'grid';
        
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
    async function confirmDelete(form, buildingName) {
        event.preventDefault();
        
        const confirmed = await showConfirmDialog(
            `Delete "${buildingName}"? This cannot be undone.`,
            'Delete Building'
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