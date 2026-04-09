@extends('layouts.app')

@section('title', 'Unit ' . $unit->unit_number . ' - Unit Details')

@push('styles')
<style>
    /* MATTE BLACK DESIGN SYSTEM */
    :root {
        --bg-deep: #121212;
        --bg-surface: #181818;
        --bg-card: #1d1d1d;
        --border-color: #2d2d2d;
        --text-main: #ffffff;
        --text-muted: #a0a0a0;
        --accent-emerald: #10b981;
        --accent-red: #ef4444;
        --accent-warning: #f59e0b;
        --accent-blue: #3b82f6;
        --accent-purple: #8b5cf6;
        --accent-pink: #ec4899;
    }

    .dashboard-wrapper { background-color: var(--bg-deep); min-height: 100vh; padding: 2rem; color: var(--text-main); font-family: 'Inter', sans-serif; }
    
    .page-header { border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem; margin-bottom: 2rem; }
    .page-title { font-size: 1.75rem; font-weight: 700; margin: 0; color: #fff; }
    .page-subtitle { color: var(--text-muted); margin-top: 0.25rem; }

    /* UNIT HEADER */
    .unit-header {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.75rem;
        margin-bottom: 2rem;
    }
    
    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 1.5rem;
    }
    
    .header-left { flex: 1; min-width: 280px; }
    
    .unit-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0 0 0.5rem 0;
        color: var(--text-main);
    }
    
    .unit-location {
        color: var(--text-muted);
        margin-bottom: 1rem;
        font-size: 0.9rem;
    }
    
    .unit-location a {
        color: var(--accent-emerald);
        text-decoration: none;
    }
    
    .unit-location a:hover {
        text-decoration: underline;
    }
    
    .unit-meta {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    .meta-item {
        background: rgba(255, 255, 255, 0.05);
        padding: 0.3rem 0.8rem;
        border-radius: 6px;
        font-size: 0.8rem;
        color: var(--text-muted);
        border: 1px solid var(--border-color);
    }
    
    /* STATS GRID */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        text-align: center;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, transparent, var(--accent-emerald), transparent);
        transition: left 0.3s ease;
    }
    
    .stat-card:hover::before {
        left: 100%;
        animation: pulse 1.5s ease-in-out;
    }
    
    @keyframes pulse {
        0% { opacity: 0; left: -100%; }
        50% { opacity: 1; left: 0%; }
        100% { opacity: 0; left: 100%; }
    }
    
    .stat-card:hover {
        border-color: var(--accent-emerald);
        transform: translateY(-3px);
    }
    
    .stat-value {
        display: block;
        font-size: 2rem;
        font-weight: 700;
        color: #fff;
        line-height: 1;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        color: var(--text-muted);
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 1px;
    }
    
    /* TAB CONTAINER */
    .tab-container {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .tab-header {
        display: flex;
        border-bottom: 1px solid var(--border-color);
        background: var(--bg-surface);
        overflow-x: auto;
    }
    
    .tab-button {
        padding: 1rem 1.75rem;
        background: none;
        border: none;
        cursor: pointer;
        font-weight: 500;
        color: var(--text-muted);
        transition: all 0.3s ease;
        border-bottom: 3px solid transparent;
        font-size: 0.85rem;
        font-family: 'Inter', sans-serif;
        white-space: nowrap;
    }
    
    .tab-button:hover {
        background: rgba(255, 255, 255, 0.05);
        color: var(--text-main);
    }
    
    .tab-button.active {
        color: var(--accent-emerald);
        border-bottom-color: var(--accent-emerald);
    }
    
    .tab-content {
        padding: 1.75rem;
    }
    
    .tab-pane {
        display: none;
    }
    
    .tab-pane.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* OVERVIEW GRID */
    .overview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .overview-box {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: fit-content;
    }
    
    .overview-box-header {
        background: rgba(255, 255, 255, 0.03);
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border-color);
        font-weight: 600;
        color: var(--text-main);
        font-size: 0.9rem;
    }
    
    .overview-box-content {
        padding: 1.25rem;
        flex: 1;
    }
    
    .info-item {
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border-color);
    }
    
    .info-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    
    .info-label {
        font-size: 0.7rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }
    
    .info-value {
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--text-main);
        word-break: break-word;
    }
    
    .description-text {
        color: var(--text-muted);
        font-size: 0.85rem;
        line-height: 1.6;
        margin: 0;
    }
    
    /* FEATURES */
    .features-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }
    
    .feature-tag {
        background: rgba(255, 255, 255, 0.05);
        color: var(--accent-emerald);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.7rem;
        border: 1px solid var(--border-color);
    }
    
    /* TENANT CARD */
    .tenant-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.75rem;
        margin-bottom: 1.5rem;
    }
    
    .tenant-name {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .tenant-detail-item {
        margin-bottom: 0.75rem;
        color: var(--text-muted);
        font-size: 0.85rem;
    }
    
    .tenant-detail-item a {
        color: var(--accent-emerald);
        text-decoration: none;
    }
    
    .tenant-detail-item a:hover {
        text-decoration: underline;
    }
    
    .lease-progress {
        margin-top: 1.5rem;
        padding: 1.25rem;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 10px;
        border: 1px solid var(--border-color);
    }
    
    .progress-bar-container {
        height: 8px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
        overflow: hidden;
        margin: 0.75rem 0;
    }
    
    .progress-bar-fill {
        height: 100%;
        background: var(--accent-emerald);
        border-radius: 4px;
        transition: width 0.3s ease;
    }
    
    /* BUTTONS */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.6rem 1.25rem;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--text-main);
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: 'Inter', sans-serif;
    }
    
    .btn:hover {
        border-color: var(--accent-emerald);
        color: var(--accent-emerald);
        transform: translateY(-1px);
    }
    
    .btn-sm {
        padding: 0.4rem 0.8rem;
        font-size: 0.7rem;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        color: var(--text-main);
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    
    .btn-sm:hover {
        border-color: var(--accent-emerald);
        color: var(--accent-emerald);
    }
    
    .action-buttons {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    
    /* BADGES */
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 500;
        text-align: center;
        background: transparent;
        border: 1px solid;
        min-width: 100px;
    }
    
    .status-occupied,
    .status-active,
    .status-ready,
    .status-completed {
        border-color: var(--accent-emerald);
        color: var(--accent-emerald);
    }
    
    .status-vacant,
    .status-inactive {
        border-color: #6c757d;
        color: #6c757d;
    }
    
    .status-maintenance,
    .status-pending,
    .status-assigned,
    .status-submitted,
    .status-renovation {
        border-color: var(--accent-warning);
        color: var(--accent-warning);
    }
    
    .status-in_progress {
        border-color: var(--accent-blue);
        color: var(--accent-blue);
    }
    
    .status-expired,
    .status-terminated,
    .status-cancelled {
        border-color: var(--accent-red);
        color: var(--accent-red);
    }
    
    .status-reserved {
        border-color: var(--accent-purple);
        color: var(--accent-purple);
    }
    
    .priority-emergency {
        border-color: var(--accent-red);
        color: var(--accent-red);
    }
    .priority-high {
        border-color: #e67e22;
        color: #e67e22;
    }
    .priority-medium {
        border-color: var(--accent-warning);
        color: var(--accent-warning);
    }
    .priority-low {
        border-color: var(--accent-emerald);
        color: var(--accent-emerald);
    }
    
    .days-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        margin-left: 0.5rem;
        border: 1px solid;
    }
    .days-warning {
        border-color: var(--accent-warning);
        color: var(--accent-warning);
    }
    .days-danger {
        border-color: var(--accent-red);
        color: var(--accent-red);
    }
    .days-success {
        border-color: var(--accent-emerald);
        color: var(--accent-emerald);
    }
    
    /* TABLES */
    .maintenance-table-container {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        overflow-x: auto;
        margin-top: 1rem;
    }
    
    .maintenance-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
    }
    
    .maintenance-table th {
        padding: 1rem;
        text-align: left;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border-color);
        background: rgba(255, 255, 255, 0.02);
    }
    
    .maintenance-table td {
        padding: 1rem;
        font-size: 0.85rem;
        color: var(--text-main);
        border-bottom: 1px solid var(--border-color);
    }
    
    .maintenance-table tbody tr:hover {
        background: rgba(255, 255, 255, 0.03);
    }
    
    /* NO DATA */
    .no-data {
        text-align: center;
        padding: 3rem;
        color: var(--text-muted);
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 10px;
    }
    
    .no-data h3 {
        margin-bottom: 0.5rem;
        color: var(--text-main);
        font-weight: 500;
    }
    
    .no-data-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    /* MODAL */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.95);
        z-index: 10000;
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        width: 90%;
        max-width: 900px;
        max-height: 90vh;
        overflow: hidden;
    }
    
    .modal-header {
        padding: 1rem 1.5rem;
        background: var(--bg-surface);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-header h3 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-main);
        margin: 0;
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--text-muted);
        transition: all 0.2s ease;
    }
    
    .modal-close:hover {
        color: var(--accent-emerald);
    }
    
    .modal-body {
        padding: 1.5rem;
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
        border-radius: 8px;
    }
    
    .modal-body iframe {
        width: 100%;
        height: 70vh;
        border: none;
        border-radius: 8px;
    }
    
    /* RESPONSIVE */
    @media (max-width: 768px) {
        .dashboard-wrapper { padding: 1rem; }
        .tab-content { padding: 1rem; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .action-buttons { width: 100%; justify-content: flex-start; }
        .overview-grid { grid-template-columns: 1fr; }
        .tenant-name { flex-direction: column; align-items: flex-start; gap: 0.75rem; }
        .days-badge { margin-left: 0; }
    }
    
    @media (max-width: 480px) {
        .stats-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Unit Details</h1>
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
            
            if ($tenant->lease_end_date) {
                $leaseEndDate = $tenant->lease_end_date;
                if (is_string($leaseEndDate)) {
                    $leaseEndDate = \Carbon\Carbon::parse($leaseEndDate);
                }
                $daysLeft = max(0, (int) now()->startOfDay()->diffInDays($leaseEndDate->startOfDay(), false));
            }
            
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
                <div style="margin-top: 1.5rem;">
                    <div class="overview-box" style="border-color: var(--accent-warning);">
                        <div class="overview-box-header" style="color: var(--accent-warning);">Internal Notes</div>
                        <div class="overview-box-content">
                            <p class="description-text" style="color: var(--accent-warning);">{{ $unit->notes }}</p>
                            <div style="margin-top: 0.75rem; color: var(--text-muted); font-size: 0.7rem;">
                                Last updated: {{ $unit->updated_at ? $unit->updated_at->format('M d, Y h:i A') : 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Action buttons -->
                <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
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
                            
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 1.25rem;">
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
                                        Monthly Rent: <strong style="color: var(--accent-emerald);">₱{{ number_format($tenant->monthly_rent, 0) }}</strong>
                                    </div>
                                </div>
                            </div>
                            
                            @if($tenant->emergency_contact_name)
                            <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--border-color);">
                                <div style="margin-bottom: 0.75rem;">
                                    <strong style="font-size: 0.85rem; color: var(--text-main);">Emergency Contact</strong>
                                </div>
                                <div style="display: flex; gap: 1.5rem; margin-top: 0.5rem; flex-wrap: wrap;">
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">{{ $tenant->emergency_contact_name }}</span>
                                    @if($tenant->emergency_contact_relation)
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">{{ $tenant->emergency_contact_relation }}</span>
                                    @endif
                                    @if($tenant->emergency_contact_phone)
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">{{ $tenant->emergency_contact_phone }}</span>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            <!-- Lease Progress Bar -->
                            @if($leaseStartDate && $leaseEndDate)
                            <div class="lease-progress">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span style="font-weight: 600; color: var(--text-main); font-size: 0.8rem;">Lease Progress</span>
                                    <span style="color: var(--accent-emerald); font-weight: 600; font-size: 0.8rem;">{{ $progressPercent }}%</span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar-fill" style="width: {{ $progressPercent }}%;"></div>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-top: 0.75rem; color: var(--text-muted); font-size: 0.7rem;">
                                    <span>Started: {{ $leaseStartDate->format('M d, Y') }}</span>
                                    <span>Ends: {{ $leaseEndDate->format('M d, Y') }}</span>
                                </div>
                            </div>
                            @endif
                            
                            <!-- Action buttons -->
                            <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem; flex-wrap: wrap;">
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
                    <div class="overview-box">
                        <div class="overview-box-header">Identification Document</div>
                        <div class="overview-box-content">
                            <div style="display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap;">
                                <span style="font-size: 2rem;">🆔</span>
                                <div>
                                    <strong style="font-size: 0.9rem; color: var(--text-main);">{{ $tenant->id_type_label ?? ucfirst($tenant->id_type) }}</strong>
                                    <div style="color: var(--text-muted); font-size: 0.7rem; margin-top: 0.25rem;">Uploaded on {{ $tenant->created_at ? $tenant->created_at->format('M d, Y') : 'N/A' }}</div>
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
                            <div style="margin-top: 1rem; padding: 1rem; background: rgba(255, 255, 255, 0.03); border-radius: 8px; border: 1px solid var(--border-color);">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <span style="font-size: 1.5rem;">📄</span>
                                    <div>
                                        <span style="font-weight: 500; color: var(--text-main); font-size: 0.8rem;">PDF Document</span>
                                        <div style="color: var(--text-muted); font-size: 0.65rem;">Click the View Document button to open in modal</div>
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
                            <a href="{{ route('tenants.create', ['building_id' => $unit->building->id, 'unit_id' => $unit->id]) }}" class="btn" style="margin-top: 1rem;">
                                + Add New Tenant
                            </a>
                        @else
                            <p style="margin-top: 0.75rem; color: var(--accent-warning); background: rgba(245, 158, 11, 0.1); padding: 0.75rem 1.25rem; border-radius: 8px; display: inline-block; font-size: 0.8rem;">
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
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
                        <h3 style="font-size: 1rem; font-weight: 600; margin: 0;">Maintenance Requests</h3>
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
                                        <a href="{{ route('maintenance-requests.show', $request) }}" style="color: var(--accent-emerald); text-decoration: none; font-weight: 500;">
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
                                        <div style="display: flex; gap: 0.5rem;">
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
                    <a href="{{ route('maintenance-requests.create', ['unit_id' => $unit->id]) }}" class="btn" style="margin-top: 1rem;">
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
                    <div style="margin-top: 2rem;">
                        <h4 style="font-size: 0.9rem; color: var(--text-main); margin-bottom: 1rem; text-align: left;">Previous Tenants</h4>
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
                                                <td style="text-align: left; font-weight: 500;">{{ $pastTenant->full_name }}</td>
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
                            <p style="color: var(--text-muted); font-size: 0.85rem;">No previous tenants found</p>
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
                previewDiv.style.padding = '2rem';
                previewDiv.innerHTML = `
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📄</div>
                    <h3 style="margin-bottom: 0.5rem;">${documentType}</h3>
                    <p style="margin-bottom: 1.25rem;">This file cannot be previewed in the browser.</p>
                    <a href="${url}" download class="btn">
                        ⬇️ Download Document
                    </a>
                `;
                modalBody.appendChild(previewDiv);
            }
        }
        
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeDocumentModal() {
        const modal = document.getElementById('documentModal');
        modal.style.display = 'none';
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
            if (typeof Utilities !== 'undefined' && Utilities.showToast) {
                Utilities.showToast('{{ session('success') }}', 'success');
            }
        });
    @endif
    
    @if(session('error'))
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Utilities !== 'undefined' && Utilities.showToast) {
                Utilities.showToast('{{ session('error') }}', 'error');
            }
        });
    @endif
    
    @if(session('warning'))
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Utilities !== 'undefined' && Utilities.showToast) {
                Utilities.showToast('{{ session('warning') }}', 'warning');
            }
        });
    @endif
    
    @if(session('info'))
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Utilities !== 'undefined' && Utilities.showToast) {
                Utilities.showToast('{{ session('info') }}', 'info');
            }
        });
    @endif
</script>
@endpush