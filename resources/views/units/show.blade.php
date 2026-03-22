@extends('layouts.app')

@section('title', 'Unit ' . $unit->unit_number . ' - Unit Details')

@push('styles')
<style>
    /* UNIT HEADER */
    .unit-header {
        background: white;
        color: #2c3e50;
        padding: 30px;
        border-radius: 8px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        border: 1px solid #dee2e6;
    }

    .unit-title {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 10px;
        color: #2c3e50;
    }

    .unit-location {
        font-size: 15px;
        color: #6c757d;
        margin-bottom: 20px;
    }

    .unit-location a {
        color: #4a5568;
        text-decoration: none;
    }

    .unit-location a:hover {
        color: #2d3748;
        text-decoration: underline;
    }

    .unit-meta {
        display: flex;
        gap: 15px;
        font-size: 14px;
        flex-wrap: wrap;
    }

    .meta-item {
        background: #f8f9fa;
        padding: 8px 15px;
        border-radius: 6px;
        border: 1px solid #e9ecef;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 20px;
    }

    .header-left {
        flex: 1;
        min-width: 300px;
    }

    /* STATS GRID */
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

    /* TAB CONTAINER */
    .tab-container {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        border: 1px solid #dee2e6;
        margin-bottom: 40px;
    }

    .tab-header {
        display: flex;
        border-bottom: 1px solid #dee2e6;
        background: #f8f9fa;
        overflow-x: auto;
    }

    .tab-button {
        padding: 18px 30px;
        border: none;
        background: none;
        cursor: pointer;
        font-weight: 500;
        color: #6c757d;
        transition: all 0.3s ease;
        border-bottom: 3px solid transparent;
        white-space: nowrap;
        font-size: 14px;
        font-family: 'Inter', sans-serif;
    }

    .tab-button:hover {
        background: #e9ecef;
        color: #495057;
    }

    .tab-button.active {
        color: #4a5568;
        border-bottom-color: #4a5568;
        background: white;
        font-weight: 600;
    }

    .tab-content {
        padding: 30px;
    }

    .tab-pane {
        display: none;
    }

    .tab-pane.active {
        display: block;
    }

    /* INFO GRID */
    .overview-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .overview-box {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        border: 1px solid #dee2e6;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: fit-content;
    }

    .overview-box-header {
        background: #f8f9fa;
        padding: 15px 20px;
        border-bottom: 1px solid #dee2e6;
        font-weight: 700;
        color: #2c3e50;
        font-size: 16px;
        text-align: center;
        letter-spacing: 0.3px;
    }

    .overview-box-content {
        padding: 20px;
        flex: 1;
    }

    .info-item {
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e9ecef;
    }

    .info-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .info-label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 4px;
    }

    .info-value {
        font-size: 15px;
        font-weight: 600;
        color: #2c3e50;
        word-break: break-word;
    }

    .description-text {
        color: #2c3e50;
        font-size: 14px;
        line-height: 1.8;
        margin: 0;
        word-break: break-word;
    }

    /* Responsive for overview grid */
    @media (max-width: 992px) {
        .overview-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .overview-grid {
            grid-template-columns: 1fr;
        }
    }

    /* TENANT CARD */
    .tenant-card {
        background: white;
        border-radius: 8px;
        padding: 30px;
        border: 1px solid #dee2e6;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
    }

    .tenant-info {
        flex: 1;
    }

    .tenant-name {
        font-size: 24px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .tenant-detail-item {
        margin-bottom: 12px;
        color: #495057;
        font-size: 15px;
    }

    .tenant-detail-item a {
        color: #4a5568;
        text-decoration: none;
    }

    .tenant-detail-item a:hover {
        color: #2d3748;
        text-decoration: underline;
    }

    .lease-progress {
        margin-top: 25px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    .progress-bar-container {
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
        margin: 15px 0;
    }

    .progress-bar-fill {
        height: 100%;
        background: #4a5568;
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    /* BADGES */
    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        text-align: center;
        min-width: 100px;
        background: transparent;
        border: 1px solid;
    }

    .status-occupied {
        border-color: #155724;
        color: #155724;
    }

    .status-vacant {
        border-color: #495057;
        color: #495057;
    }

    .status-maintenance {
        border-color: #856404;
        color: #856404;
    }

    .status-renovation {
        border-color: #0c5460;
        color: #0c5460;
    }

    .status-reserved {
        border-color: #2c3e50;
        color: #2c3e50;
    }

    .status-ready {
        border-color: #155724;
        color: #155724;
    }

    .type-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        background: transparent;
        border: 1px solid #4a5568;
        color: #4a5568;
        text-align: center;
        min-width: 100px;
    }

    /* BUTTONS */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 20px;
        border-radius: 4px;
        border: 1px solid #4a5568;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
        font-family: 'Inter', sans-serif;
        background: transparent;
        color: #4a5568;
    }

    .btn:hover {
        background: #4a5568;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,.1);
        text-decoration: none;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
        border: 1px solid #4a5568;
        background: transparent;
        color: #4a5568;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
        line-height: 1;
        height: 32px;
    }

    .btn-sm:hover {
        background: #4a5568;
        color: white;
        text-decoration: none;
    }

    .btn-danger {
        border: 1px solid #2d3748;
        color: #2d3748;
    }

    .btn-danger:hover {
        background: #2d3748;
        color: white;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* FEATURES */
    .features-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 15px;
    }

    .feature-tag {
        background: transparent;
        color: #4a5568;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        border: 1px solid #4a5568;
    }

    /* DAYS REMAINING BADGE */
    .days-badge {
        display: inline-block;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        margin-left: 10px;
        background: transparent;
        border: 1px solid;
    }
    
    .days-warning {
        border-color: #856404;
        color: #856404;
    }
    
    .days-danger {
        border-color: #721c24;
        color: #721c24;
    }
    
    .days-success {
        border-color: #155724;
        color: #155724;
    }

    /* MAINTENANCE TABLE */
    .maintenance-table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        overflow-x: auto;
        border: 1px solid #dee2e6;
        margin-bottom: 30px;
    }

    .maintenance-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }

    .maintenance-table th {
        background: #f8f9fa;
        padding: 18px 15px;
        font-weight: 600;
        color: #2c3e50;
        border: 1px solid #dee2e6;
        font-size: 14px;
        white-space: nowrap;
        text-align: center;
    }

    .maintenance-table td {
        padding: 16px 15px;
        border: 1px solid #e9ecef;
        vertical-align: middle;
        font-size: 14px;
        line-height: 1.4;
        text-align: center;
    }

    .maintenance-table td:first-child {
        text-align: left;
    }

    .maintenance-table tbody tr:hover td {
        background: #f8f9fa;
        border-color: #cfe2ff;
    }

    /* PRIORITY BADGES */
    .priority-emergency {
        border-color: #e74c3c;
        color: #e74c3c;
    }
    
    .priority-high {
        border-color: #e67e22;
        color: #e67e22;
    }
    
    .priority-medium {
        border-color: #f39c12;
        color: #f39c12;
    }
    
    .priority-low {
        border-color: #27ae60;
        color: #27ae60;
    }

    /* STATUS BADGES for maintenance */
    .status-submitted {
        border-color: #2c3e50;
        color: #2c3e50;
    }

    .status-assigned {
        border-color: #856404;
        color: #856404;
    }

    .status-in_progress {
        border-color: #004085;
        color: #004085;
    }

    .status-completed {
        border-color: #155724;
        color: #155724;
    }

    .status-cancelled {
        border-color: #721c24;
        color: #721c24;
    }

    /* NO DATA */
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

    /* MODAL STYLES */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 10000;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.2s ease;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 8px;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        animation: scaleIn 0.3s ease;
    }

    .modal-header {
        padding: 15px 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #6c757d;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: all 0.2s ease;
    }

    .modal-close:hover {
        background: #e9ecef;
        color: #2c3e50;
    }

    .modal-body {
        padding: 20px;
        max-height: calc(90vh - 70px);
        overflow: auto;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .modal-body img {
        max-width: 100%;
        max-height: calc(90vh - 110px);
        object-fit: contain;
        border-radius: 4px;
    }

    .modal-body iframe {
        width: 100%;
        height: 70vh;
        border: none;
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
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

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .page-content {
            padding: 20px 15px !important;
        }

        .unit-header {
            padding: 25px;
        }
        
        .unit-title {
            font-size: 24px;
        }
        
        .header-content {
            flex-direction: column;
        }
        
        .action-buttons {
            width: 100%;
            justify-content: flex-start;
        }
        
        .btn {
            flex: 1;
            min-width: 120px;
            justify-content: center;
        }
        
        .unit-meta {
            flex-direction: column;
            gap: 10px;
        }
        
        .tab-button {
            padding: 15px 20px;
            font-size: 13px;
        }
        
        .stat-value {
            font-size: 24px;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .tenant-name {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .days-badge {
            margin-left: 0;
        }

        .maintenance-table {
            min-width: 700px;
        }
    }

    @media (max-width: 576px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .tab-button {
            padding: 12px 15px;
            font-size: 12px;
        }
        
        .tab-content {
            padding: 20px;
        }
    }
</style>
@endpush

@section('content')
<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">🚪 Unit Details</h1>
            <p class="page-subtitle">View and manage unit information</p>
        </div>
    </div>

    <!-- Unit Header -->
    <div class="unit-header">
        <div class="header-content">
            <div class="header-left">
                <h1 class="unit-title">Unit {{ $unit->unit_number }}</h1>
                <div class="unit-location">
                    <a href="{{ route('buildings.show', $unit->building) }}">
                        {{ $unit->building->name }}
                    </a> 
                    - {{ $unit->building->address }}, {{ $unit->building->city }}
                </div>
                <div class="unit-meta">
                    <div class="meta-item">
                        {{ $unit->unit_type_label ?? ucfirst($unit->unit_type ?? 'N/A') }}
                    </div>
                    
                    <div class="meta-item">
                        ₱{{ number_format($unit->monthly_rent, 0) }}/month
                    </div>
                    
                    <div class="meta-item">
                        @php
                            $statusColors = [
                                'vacant' => 'status-vacant',
                                'occupied' => 'status-occupied',
                                'maintenance' => 'status-maintenance',
                                'under_maintenance' => 'status-maintenance',
                                'renovation' => 'status-renovation',
                                'reserved' => 'status-reserved',
                                'ready' => 'status-ready',
                            ];
                            $statusLabels = [
                                'vacant' => 'Vacant',
                                'occupied' => 'Occupied',
                                'maintenance' => 'Maintenance',
                                'under_maintenance' => 'Maintenance',
                                'renovation' => 'Renovation',
                                'reserved' => 'Reserved',
                                'ready' => 'Ready',
                            ];
                        @endphp
                        <span class="status-badge {{ $statusColors[$unit->status] ?? 'status-vacant' }}">
                            {{ $statusLabels[$unit->status] ?? ucfirst($unit->status) }}
                        </span>
                    </div>
                    
                    @if($unit->floor)
                    <div class="meta-item">
                        Floor: {{ $unit->floor }}
                    </div>
                    @endif
                    
                    @if($unit->area)
                    <div class="meta-item">
                        {{ number_format($unit->area) }} sq ft
                    </div>
                    @endif
                </div>
            </div>
            <div class="action-buttons">
                <a href="{{ route('units.edit', $unit) }}" class="btn">
                    Edit Unit
                </a>
                <a href="{{ route('buildings.show', $unit->building) }}" class="btn">
                    Back to Building
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    @php
        $hasTenant = $unit->currentTenant ? true : false;
        $daysLeft = 0;
        $leaseProgress = 0;
        
        if ($hasTenant && $unit->currentTenant) {
            $tenant = $unit->currentTenant;
            
            // Handle days left
            if ($tenant->lease_end_date) {
                $leaseEndDate = $tenant->lease_end_date;
                if (is_string($leaseEndDate)) {
                    $leaseEndDate = \Carbon\Carbon::parse($leaseEndDate);
                }
                $daysLeft = max(0, (int) now()->startOfDay()->diffInDays($leaseEndDate->startOfDay(), false));
            }
            
            // Handle lease progress
            if ($tenant->lease_start_date && $tenant->lease_end_date) {
                $startDate = $tenant->lease_start_date;
                $endDate = $tenant->lease_end_date;
                
                if (is_string($startDate)) {
                    $startDate = \Carbon\Carbon::parse($startDate);
                }
                if (is_string($endDate)) {
                    $endDate = \Carbon\Carbon::parse($endDate);
                }
                
                $totalDays = $startDate->diffInDays($endDate);
                $elapsedDays = $startDate->diffInDays(now());
                $leaseProgress = $totalDays > 0 ? min(round(($elapsedDays / $totalDays) * 100), 100) : 0;
            }
        }
    @endphp
    
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-value">{{ $unit->bedrooms ?? 0 }}</span>
            <span class="stat-label">Bedrooms</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ $unit->bathrooms ?? 0 }}</span>
            <span class="stat-label">Bathrooms</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ $unit->area ? number_format($unit->area) : 'N/A' }}</span>
            <span class="stat-label">Size (sq ft)</span>
        </div>
        <div class="stat-card">
            @if($hasTenant)
                <span class="stat-value">👤</span>
                <span class="stat-label">Occupied</span>
            @else
                <span class="stat-value">🚪</span>
                <span class="stat-label">Vacant</span>
            @endif
        </div>
    </div>

    <!-- Tab Interface -->
    <div class="tab-container">
        <div class="tab-header">
            <button class="tab-button active" data-tab="overview">Overview</button>
            <button class="tab-button" data-tab="tenant">Current Tenant</button>
            <button class="tab-button" data-tab="maintenance">Maintenance</button>
            <button class="tab-button" data-tab="history">History</button>
        </div>
        
        <div class="tab-content">
            <!-- Overview Tab -->
            <div class="tab-pane active" id="overview">
                <div class="overview-grid">
                    <!-- Box 1: Financial Information -->
                    <div class="overview-box">
                        <div class="overview-box-header">Financial Information</div>
                        <div class="overview-box-content">
                            @if($unit->security_deposit)
                            <div class="info-item">
                                <div class="info-label">Security Deposit</div>
                                <div class="info-value">₱{{ number_format($unit->security_deposit, 0) }}</div>
                            </div>
                            @endif
                            @if($unit->parking_fee)
                            <div class="info-item">
                                <div class="info-label">Parking Fee</div>
                                <div class="info-value">₱{{ number_format($unit->parking_fee, 0) }}/month</div>
                            </div>
                            @endif
                            @if(!$unit->security_deposit && !$unit->parking_fee)
                            <div class="info-item">
                                <div class="info-value">No additional financial information</div>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Box 2: Description -->
                    <div class="overview-box">
                        <div class="overview-box-header">Description</div>
                        <div class="overview-box-content">
                            <p class="description-text">{{ $unit->description ?? 'No description provided.' }}</p>
                        </div>
                    </div>
                    
                    <!-- Box 3: Features & Amenities -->
                    <div class="overview-box">
                        <div class="overview-box-header">Features & Amenities</div>
                        <div class="overview-box-content">
                            @if($unit->features && is_array($unit->features) && count($unit->features) > 0)
                                <div class="features-list">
                                    @foreach($unit->features as $feature)
                                        <span class="feature-tag">{{ ucfirst(str_replace('_', ' ', $feature)) }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="description-text">No features listed.</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                @if($unit->notes)
                <div style="margin-top: 20px;">
                    <div class="overview-box" style="background: #fff3cd;">
                        <div class="overview-box-header" style="background: #ffeaa7;">Internal Notes</div>
                        <div class="overview-box-content">
                            <p class="description-text" style="color: #856404;">{{ $unit->notes }}</p>
                            <div style="margin-top: 10px; color: #856404; font-size: 12px;">
                                Last updated: {{ $unit->updated_at ? $unit->updated_at->format('M d, Y h:i A') : 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Action buttons -->
                <div style="display: flex; gap: 15px; justify-content: center; margin-top: 30px;">
                    @if(!$hasTenant && in_array($unit->status, ['vacant', 'ready']))
                        <a href="{{ route('tenants.create', ['building_id' => $unit->building->id, 'unit_id' => $unit->id]) }}" class="btn">
                            + Add Tenant
                        </a>
                    @endif
                    @if($hasTenant && $unit->currentLease && $unit->currentLease->id)
                        <a href="{{ route('leases.edit', ['lease' => $unit->currentLease->id]) }}" class="btn">
                            Manage Lease
                        </a>
                    @endif
                </div>
            </div>
            
            <!-- Tenant Tab -->
            <div class="tab-pane" id="tenant">
                @if($hasTenant)
                    @php
                        $tenant = $unit->currentTenant;
                        $lease = $unit->currentLease;
                        
                        // Parse dates for tenant
                        $leaseStartDate = $tenant->lease_start_date ? (is_string($tenant->lease_start_date) ? \Carbon\Carbon::parse($tenant->lease_start_date) : $tenant->lease_start_date) : null;
                        $leaseEndDate = $tenant->lease_end_date ? (is_string($tenant->lease_end_date) ? \Carbon\Carbon::parse($tenant->lease_end_date) : $tenant->lease_end_date) : null;
                        
                        $totalLeaseDays = $leaseStartDate && $leaseEndDate ? $leaseStartDate->diffInDays($leaseEndDate) : 0;
                        $elapsedDays = $leaseStartDate ? $leaseStartDate->diffInDays(now()) : 0;
                        $progressPercent = $totalLeaseDays > 0 ? min(round(($elapsedDays / $totalLeaseDays) * 100), 100) : 0;
                    @endphp
                    
                    <div class="tenant-card">
                        <div class="tenant-info">
                            <div class="tenant-name">
                                {{ $tenant->full_name }}
                                @if($daysLeft <= 30 && $daysLeft > 0)
                                    <span class="days-badge {{ $daysLeft <= 7 ? 'days-danger' : 'days-warning' }}">
                                        {{ $daysLeft }} days remaining
                                    </span>
                                @elseif($daysLeft <= 0)
                                    <span class="days-badge days-danger">
                                        Expired
                                    </span>
                                @else
                                    <span class="days-badge days-success">
                                        Active
                                    </span>
                                @endif
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
                                <div>
                                    <div class="tenant-detail-item">
                                        <a href="mailto:{{ $tenant->email }}">{{ $tenant->email }}</a>
                                    </div>
                                    <div class="tenant-detail-item">
                                        <a href="tel:{{ $tenant->phone }}">{{ $tenant->phone }}</a>
                                    </div>
                                    @if($tenant->alternate_phone)
                                    <div class="tenant-detail-item">
                                        <a href="tel:{{ $tenant->alternate_phone }}">{{ $tenant->alternate_phone }}</a>
                                    </div>
                                    @endif
                                </div>
                                <div>
                                    @if($leaseStartDate)
                                    <div class="tenant-detail-item">
                                        Tenant since: {{ $leaseStartDate->format('F d, Y') }}
                                    </div>
                                    @endif
                                    @if($leaseEndDate)
                                    <div class="tenant-detail-item">
                                        Lease ends: {{ $leaseEndDate->format('F d, Y') }}
                                    </div>
                                    @endif
                                    <div class="tenant-detail-item">
                                        Monthly Rent: <strong style="color: #4a5568;">₱{{ number_format($tenant->monthly_rent, 0) }}</strong>
                                    </div>
                                </div>
                            </div>
                            
                            @if($tenant->emergency_contact_name)
                            <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                                <div style="margin-bottom: 12px;">
                                    <strong style="font-size: 15px; color: #2c3e50;">Emergency Contact</strong>
                                </div>
                                <div style="display: flex; gap: 30px; margin-top: 10px; flex-wrap: wrap;">
                                    <span style="color: #495057;">{{ $tenant->emergency_contact_name }}</span>
                                    @if($tenant->emergency_contact_relation)
                                        <span style="color: #495057;">{{ $tenant->emergency_contact_relation }}</span>
                                    @endif
                                    @if($tenant->emergency_contact_phone)
                                        <span style="color: #495057;">{{ $tenant->emergency_contact_phone }}</span>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            <!-- Lease Progress Bar -->
                            @if($leaseStartDate && $leaseEndDate)
                            <div class="lease-progress">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                    <span style="font-weight: 600; color: #2c3e50;">Lease Progress</span>
                                    <span style="color: #4a5568; font-weight: 600;">{{ $progressPercent }}%</span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar-fill" style="width: {{ $progressPercent }}%;"></div>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-top: 12px; color: #6c757d; font-size: 13px;">
                                    <span>Started: {{ $leaseStartDate->format('M d, Y') }}</span>
                                    <span>Ends: {{ $leaseEndDate->format('M d, Y') }}</span>
                                </div>
                            </div>
                            @endif
                            
                            <!-- Action buttons -->
                            <div style="display: flex; gap: 12px; margin-top: 25px; flex-wrap: wrap;">
                                <a href="{{ route('tenants.show', $tenant) }}" class="btn">
                                    View Full Profile
                                </a>
                                <a href="{{ route('tenants.edit', $tenant) }}" class="btn">
                                    Edit Tenant
                                </a>
                                @if($lease && $lease->id)
                                    <a href="{{ route('leases.edit', ['lease' => $lease->id]) }}" class="btn">
                                        Manage Lease
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tenant Documents -->
                    @if($tenant->government_id)
                    <div class="overview-box" style="margin-top: 20px;">
                        <div class="overview-box-header">Identification Document</div>
                        <div class="overview-box-content">
                            <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                                <span style="font-size: 32px;">🆔</span>
                                <div>
                                    <strong style="font-size: 16px; color: #2c3e50;">{{ $tenant->id_type_label ?? ucfirst($tenant->id_type) }}</strong>
                                    <div style="color: #6c757d; font-size: 13px; margin-top: 4px;">Uploaded on {{ $tenant->created_at ? $tenant->created_at->format('M d, Y') : 'N/A' }}</div>
                                </div>
                                <div style="margin-left: auto;">
                                    <button onclick="viewDocument('{{ Storage::url($tenant->government_id) }}', '{{ $tenant->id_type_label ?? ucfirst($tenant->id_type) }}')" class="btn">
                                        View Document
                                    </button>
                                </div>
                            </div>
                            @php
                                $extension = pathinfo($tenant->government_id, PATHINFO_EXTENSION);
                                $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            @endphp
                            @if(!$isImage)
                            <div style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 6px; border: 1px solid #dee2e6;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <span style="font-size: 24px;">📄</span>
                                    <div>
                                        <span style="font-weight: 500; color: #2c3e50;">PDF Document</span>
                                        <div style="color: #6c757d; font-size: 12px;">Click the View Document button to open in modal</div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                @else
                    <!-- No Tenant Message -->
                    <div class="no-data">
                        <div class="no-data-icon">👤</div>
                        <h3>No Current Tenant</h3>
                        <p>This unit is currently vacant and ready for occupancy</p>
                        @if(in_array($unit->status, ['vacant', 'ready']))
                            <a href="{{ route('tenants.create', ['building_id' => $unit->building->id, 'unit_id' => $unit->id]) }}" class="btn" style="margin-top: 15px;">
                                + Add New Tenant
                            </a>
                        @else
                            <p style="margin-top: 10px; color: #856404; background: #fff3cd; padding: 10px 20px; border-radius: 6px; display: inline-block;">
                                ⚠️ Unit status must be "Vacant" or "Ready" to add a tenant
                            </p>
                        @endif
                    </div>
                @endif
            </div>
            
            <!-- Maintenance Tab -->
            <div class="tab-pane" id="maintenance">
                @if($unit->maintenanceRequests && $unit->maintenanceRequests->count() > 0)
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="font-size: 18px; color: #2c3e50;">Maintenance Requests</h3>
                        <a href="{{ route('maintenance-requests.create', ['unit_id' => $unit->id]) }}" class="btn">
                            + New Request
                        </a>
                    </div>
                    
                    <div class="maintenance-table-container">
                        <table class="maintenance-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unit->maintenanceRequests as $request)
                                <tr>
                                    <td>
                                        <a href="{{ route('maintenance-requests.show', $request) }}" style="color: #4a5568; text-decoration: none; font-weight: 500;">
                                            {{ Str::limit($request->title, 40) }}
                                        </a>
                                    </td>
                                    <td>
                                        @php
                                            $priorityClass = match($request->priority ?? '') {
                                                'emergency' => 'priority-emergency',
                                                'high' => 'priority-high',
                                                'medium' => 'priority-medium',
                                                'low' => 'priority-low',
                                                default => ''
                                            };
                                        @endphp
                                        <span class="status-badge {{ $priorityClass }}" style="min-width: 80px;">
                                            {{ ucfirst($request->priority ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($request->status ?? '') {
                                                'submitted' => 'status-submitted',
                                                'assigned' => 'status-assigned',
                                                'in_progress' => 'status-in_progress',
                                                'completed' => 'status-completed',
                                                'cancelled' => 'status-cancelled',
                                                default => ''
                                            };
                                        @endphp
                                        <span class="status-badge {{ $statusClass }}" style="min-width: 100px;">
                                            {{ ucfirst(str_replace('_', ' ', $request->status ?? 'N/A')) }}
                                        </span>
                                    </td>
                                    <td>{{ $request->created_at ? $request->created_at->format('M d, Y') : 'N/A' }}</td>
                                    <td>
                                        <div style="display: flex; gap: 5px; justify-content: center;">
                                            <a href="{{ route('maintenance-requests.show', $request) }}" class="btn-sm">
                                                View
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                <div class="no-data">
                    <div class="no-data-icon">🔧</div>
                    <h3>No Maintenance Requests</h3>
                    <p>No maintenance requests have been submitted for this unit</p>
                    <a href="{{ route('maintenance-requests.create', ['unit_id' => $unit->id]) }}" class="btn" style="margin-top: 15px;">
                        + Submit Maintenance Request
                    </a>
                </div>
                @endif
            </div>
            
            <!-- History Tab -->
            <div class="tab-pane" id="history">
                <div class="no-data">
                    <div class="no-data-icon">📜</div>
                    <h3>Unit History</h3>
                    <p>Lease history and other historical data will appear here</p>
                    <div style="margin-top: 30px;">
                        <h4 style="font-size: 16px; color: #2c3e50; margin-bottom: 15px; text-align: left;">Previous Tenants</h4>
                        @if($unit->tenants && $unit->tenants->count() > 1)
                            <div class="maintenance-table-container">
                                <table class="maintenance-table">
                                    <thead>
                                        <tr>
                                            <th>Tenant Name</th>
                                            <th>Lease Period</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($unit->tenants as $pastTenant)
                                            @if(!$hasTenant || ($hasTenant && $pastTenant->id !== $unit->currentTenant->id))
                                            <tr>
                                                <td style="text-align: left;">{{ $pastTenant->full_name }}</td>
                                                <td>
                                                    @php
                                                        $startDate = $pastTenant->lease_start_date ? (is_string($pastTenant->lease_start_date) ? \Carbon\Carbon::parse($pastTenant->lease_start_date) : $pastTenant->lease_start_date) : null;
                                                        $endDate = $pastTenant->lease_end_date ? (is_string($pastTenant->lease_end_date) ? \Carbon\Carbon::parse($pastTenant->lease_end_date) : $pastTenant->lease_end_date) : null;
                                                    @endphp
                                                    {{ $startDate ? $startDate->format('M Y') : 'N/A' }} - 
                                                    {{ $endDate ? $endDate->format('M Y') : 'Present' }}
                                                </td>
                                                <td>
                                                    <span class="status-badge status-terminated">
                                                        {{ $pastTenant->lease_status_label ?? ucfirst($pastTenant->lease_status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p style="color: #6c757d;">No previous tenants found</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document Modal -->
<div id="documentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Document Viewer</h3>
            <button onclick="closeDocumentModal()" class="modal-close">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab Switching
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabPanes = document.querySelectorAll('.tab-pane');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabPanes.forEach(pane => pane.classList.remove('active'));
                
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
                
                // Update URL hash for bookmarking
                window.location.hash = tabId;
            });
        });
        
        // Check for hash in URL
        if (window.location.hash) {
            const hash = window.location.hash.substring(1);
            const activeTab = document.querySelector(`.tab-button[data-tab="${hash}"]`);
            if (activeTab) {
                activeTab.click();
            }
        }
    });

    // Document Modal Functions
    function viewDocument(url, documentType = 'Document') {
        const modal = document.getElementById('documentModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalBody = document.getElementById('modalBody');
        
        modalTitle.textContent = `${documentType} - Viewer`;
        
        // Clear previous content
        modalBody.innerHTML = '';
        
        // Check if it's an image
        const isImage = url.match(/\.(jpg|jpeg|png|gif|webp)(\?.*)?$/i);
        
        if (isImage) {
            // Display image
            const img = document.createElement('img');
            img.src = url;
            img.alt = documentType;
            modalBody.appendChild(img);
        } else {
            // Display PDF or other document
            const isPDF = url.match(/\.pdf(\?.*)?$/i);
            
            if (isPDF) {
                // Embed PDF
                const iframe = document.createElement('iframe');
                iframe.src = url;
                modalBody.appendChild(iframe);
            } else {
                // Show download option for other file types
                const previewDiv = document.createElement('div');
                previewDiv.className = 'no-data';
                previewDiv.style.padding = '40px';
                previewDiv.innerHTML = `
                    <div style="font-size: 64px; margin-bottom: 20px;">📄</div>
                    <h3>${documentType}</h3>
                    <p>This file cannot be previewed in the browser.</p>
                    <a href="${url}" download class="btn" style="margin-top: 20px;">
                        ⬇️ Download Document
                    </a>
                `;
                modalBody.appendChild(previewDiv);
            }
        }
        
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeDocumentModal() {
        const modal = document.getElementById('documentModal');
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
        
        // Clear content
        const modalBody = document.getElementById('modalBody');
        modalBody.innerHTML = '';
    }

    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDocumentModal();
        }
    });

    // Close modal on background click
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('documentModal');
        if (e.target === modal) {
            closeDocumentModal();
        }
    });

    // Show session messages as toasts using layout's Utilities
    @if(session('success'))
        document.addEventListener('DOMContentLoaded', function() {
            Utilities.showToast('{{ session('success') }}', 'success');
        });
    @endif
    
    @if(session('error'))
        document.addEventListener('DOMContentLoaded', function() {
            Utilities.showToast('{{ session('error') }}', 'error');
        });
    @endif
    
    @if(session('warning'))
        document.addEventListener('DOMContentLoaded', function() {
            Utilities.showToast('{{ session('warning') }}', 'warning');
        });
    @endif
    
    @if(session('info'))
        document.addEventListener('DOMContentLoaded', function() {
            Utilities.showToast('{{ session('info') }}', 'info');
        });
    @endif
</script>
@endpush