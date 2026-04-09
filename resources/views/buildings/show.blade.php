@extends('layouts.app')

@section('title', $building->name . ' - Building Details')

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

    /* BUILDING HEADER */
    .building-header {
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
    
    .building-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0 0 0.5rem 0;
        color: var(--text-main);
    }
    
    .building-address {
        color: var(--text-muted);
        margin-bottom: 1rem;
        font-size: 0.9rem;
    }
    
    .building-meta {
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
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
    
    @media (max-width: 768px) {
        .overview-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .overview-box {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        overflow: hidden;
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
    
    /* TABLES */
    .units-table-container,
    .tenants-table-container,
    .maintenance-table-container {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        overflow-x: auto;
        margin-top: 1rem;
    }
    
    .units-table,
    .tenants-table,
    .maintenance-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }
    
    .units-table th,
    .tenants-table th,
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
    
    .units-table td,
    .tenants-table td,
    .maintenance-table td {
        padding: 1rem;
        font-size: 0.85rem;
        color: var(--text-main);
        border-bottom: 1px solid var(--border-color);
    }
    
    .units-table tbody tr:hover,
    .tenants-table tbody tr:hover,
    .maintenance-table tbody tr:hover {
        background: rgba(255, 255, 255, 0.03);
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
    .status-submitted {
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
        padding: 0.15rem 0.5rem;
        border-radius: 12px;
        font-size: 0.65rem;
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
    
    .overdue-badge {
        display: inline-block;
        padding: 0.25rem 0.6rem;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 500;
        margin-left: 0.5rem;
        border: 1px solid var(--accent-red);
        color: var(--accent-red);
    }
    
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
    
    /* PHOTO SECTION */
    .photo-section {
        margin-top: 0;
    }
    
    .photo-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .photo-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-main);
        margin: 0 0 0.25rem 0;
    }
    
    .photo-upload-panel {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        display: none;
    }
    
    .photo-upload-panel.active {
        display: block;
    }
    
    .photo-upload-form {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: flex-end;
    }
    
    .photo-upload-field {
        flex: 1;
        min-width: 180px;
    }
    
    .photo-upload-field label {
        display: block;
        margin-bottom: 0.25rem;
        font-size: 0.7rem;
        font-weight: 500;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .photo-upload-field input,
    .photo-upload-field select {
        width: 100%;
        padding: 0.6rem;
        background: var(--bg-deep);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        color: var(--text-main);
        font-size: 0.8rem;
        font-family: 'Inter', sans-serif;
    }
    
    .photo-upload-field input:focus,
    .photo-upload-field select:focus {
        outline: none;
        border-color: var(--accent-emerald);
    }
    
    .file-input-wrapper {
        position: relative;
    }
    
    .file-input-wrapper input[type="file"] {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    .file-input-button {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem;
        background: var(--bg-deep);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 0.8rem;
        color: var(--text-muted);
        cursor: pointer;
    }
    
    .file-input-button:hover {
        border-color: var(--accent-emerald);
        color: var(--accent-emerald);
    }
    
    .selected-files {
        font-size: 0.7rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
    }
    
    .category-filter {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    
    .category-btn {
        padding: 0.4rem 1rem;
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: 20px;
        font-size: 0.75rem;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .category-btn:hover {
        border-color: var(--accent-emerald);
        color: var(--accent-emerald);
    }
    
    .category-btn.active {
        background: var(--accent-emerald);
        border-color: var(--accent-emerald);
        color: white;
    }
    
    .photo-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.25rem;
    }
    
    .photo-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .photo-card:hover {
        transform: translateY(-3px);
        border-color: var(--accent-emerald);
    }
    
    .photo-image-container {
        height: 180px;
        width: 100%;
        overflow: hidden;
        position: relative;
        background: var(--bg-deep);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .photo-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .photo-card:hover .photo-image {
        transform: scale(1.05);
    }
    
    .primary-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: var(--accent-emerald);
        color: white;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 600;
    }
    
    .photo-card-content {
        padding: 0.75rem;
    }
    
    .photo-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    
    .photo-category-badge {
        font-size: 0.65rem;
        font-weight: 600;
        color: var(--accent-emerald);
        background: rgba(16, 185, 129, 0.1);
        padding: 0.2rem 0.6rem;
        border-radius: 4px;
        text-transform: uppercase;
    }
    
    .photo-date {
        font-size: 0.6rem;
        color: var(--text-muted);
    }
    
    .photo-description {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 0.75rem;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .photo-card-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .photo-card-actions .btn-sm {
        flex: 1;
        justify-content: center;
    }
    
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
        position: relative;
        max-width: 90%;
        max-height: 90%;
    }
    
    .modal-close {
        position: absolute;
        top: -40px;
        right: 0;
        background: none;
        border: none;
        color: white;
        font-size: 28px;
        cursor: pointer;
    }
    
    .modal-image {
        max-width: 100%;
        max-height: 80vh;
        border-radius: 8px;
    }
    
    .modal-description {
        color: var(--text-muted);
        text-align: center;
        margin-top: 1rem;
        font-size: 0.9rem;
    }
    
    .alert {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        font-size: 0.8rem;
        margin-bottom: 1rem;
        position: relative;
    }
    .alert-success {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid var(--accent-emerald);
        color: var(--accent-emerald);
    }
    .alert-error {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid var(--accent-red);
        color: var(--accent-red);
    }
    
    @media (max-width: 768px) {
        .dashboard-wrapper { padding: 1rem; }
        .tab-content { padding: 1rem; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .photo-gallery { grid-template-columns: 1fr; }
        .action-buttons { width: 100%; justify-content: flex-start; }
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
            <h1 class="page-title">Building Details</h1>
            <p class="page-subtitle">{{ $building->name }} • Property Management Overview</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">
            {{ session('success') }}
            <button onclick="this.parentElement.remove()" style="float: right; background: none; border: none; color: inherit; cursor: pointer;">&times;</button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-error" style="margin-bottom: 1.5rem;">
            {{ session('error') }}
            <button onclick="this.parentElement.remove()" style="float: right; background: none; border: none; color: inherit; cursor: pointer;">&times;</button>
        </div>
    @endif

    <div class="building-header">
        <div class="header-content">
            <div class="header-left">
                <h1 class="building-title">{{ $building->name }}</h1>
                <div class="building-address">{{ $building->full_address }}</div>
                <div class="building-meta">
                    @if($building->building_type)
                    <div class="meta-item">{{ $building->building_type_label }}</div>
                    @endif
                    @if($building->total_floors)
                    <div class="meta-item">{{ $building->total_floors }} floors</div>
                    @endif
                    <div class="meta-item">Status: {{ $building->status_label }}</div>
                    @if($building->year_built)
                    <div class="meta-item">Built: {{ $building->year_built }}</div>
                    @endif
                    <div class="meta-item">{{ $building->active_tenants_count ?? 0 }} Active Tenants</div>
                </div>
            </div>
            <div class="action-buttons">
                <a href="{{ route('buildings.edit', $building) }}" class="btn">Edit Building</a>
                <a href="{{ route('buildings.index') }}" class="btn">Back to Buildings</a>
            </div>
        </div>
    </div>

    @php
        $totalUnits = $building->units_count ?? $building->units->count();
        $occupiedUnits = $building->occupied_units_count ?? $building->units->where('status', 'occupied')->count();
        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100) : 0;
        $monthlyRevenue = $building->monthly_revenue ?? $building->total_monthly_tenant_revenue ?? 0;
        $totalTenants = $building->active_tenants_count ?? $building->currentTenants->count() ?? 0;
        
        $pendingMaintenanceCount = 0;
        if (method_exists($building, 'maintenanceRequests') && $building->maintenanceRequests) {
            $pendingMaintenanceCount = $building->maintenanceRequests->whereNotIn('status', ['completed', 'cancelled'])->count();
        }
    @endphp
    
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-value">{{ $totalUnits }}</span>
            <span class="stat-label">Total Units</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ $occupancyRate }}%</span>
            <span class="stat-label">Occupancy Rate</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">₱{{ number_format($monthlyRevenue, 0) }}</span>
            <span class="stat-label">Monthly Revenue</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ $totalTenants }}</span>
            <span class="stat-label">Active Tenants</span>
        </div>
    </div>

    <div class="tab-container">
        <div class="tab-header">
            <button class="tab-button active" data-tab="overview">Overview</button>
            <button class="tab-button" data-tab="units">Units ({{ $totalUnits }})</button>
            <button class="tab-button" data-tab="tenants">Tenants ({{ $totalTenants }})</button>
            <button class="tab-button" data-tab="maintenance">Maintenance 
                <span style="background: {{ $pendingMaintenanceCount > 0 ? 'var(--accent-red)' : 'var(--text-muted)' }}; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; margin-left: 5px;">
                    {{ $pendingMaintenanceCount }}
                </span>
            </button>
            <button class="tab-button" data-tab="photos">Photos</button>
        </div>
        
        <div class="tab-content">
            <!-- Overview Tab -->
            <div class="tab-pane active" id="overview">
                <div class="overview-grid">
                    <div class="overview-box">
                        <div class="overview-box-header">Contact Information</div>
                        <div class="overview-box-content">
                            <div class="info-item">
                                <div class="info-label">Phone</div>
                                <div class="info-value">{{ $building->contact_phone ?? 'Not provided' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Email</div>
                                <div class="info-value">{{ $building->contact_email ?? 'Not provided' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="overview-box">
                        <div class="overview-box-header">Building Specifications</div>
                        <div class="overview-box-content">
                            @if($building->total_area)
                            <div class="info-item">
                                <div class="info-label">Total Area</div>
                                <div class="info-value">{{ number_format($building->total_area) }} sq ft</div>
                            </div>
                            @endif
                            <div class="info-item">
                                <div class="info-label">Elevator</div>
                                <div class="info-value">{{ $building->has_elevator ? 'Available' : 'Not available' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Parking</div>
                                <div class="info-value">{{ $building->has_parking ? 'Available' : 'Not available' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="overview-box" style="grid-column: span 2;">
                        <div class="overview-box-header">Description</div>
                        <div class="overview-box-content">
                            <p class="description-text">{{ $building->description ?? 'No description provided.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Units Tab -->
            <div class="tab-pane" id="units">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                    <h3 style="font-size: 1rem; font-weight: 600; margin: 0;">Units in {{ $building->name }}</h3>
                    <a href="{{ route('units.create', ['building' => $building->id]) }}" class="btn">Add Unit</a>
                </div>
                
                @if($building->units && $building->units->count() > 0)
                <div class="units-table-container">
                    <table class="units-table">
                        <thead>
                            <tr><th>Unit #</th><th>Type</th><th>Bed/Bath</th><th>Rent</th><th>Status</th><th>Current Tenant</th><th>Lease End</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            @foreach($building->units as $unit)
                            <tr>
                                <td style="font-weight: 600;">{{ $unit->unit_number }}</td>
                                <td>{{ $unit->unit_type_label ?? ucfirst($unit->unit_type ?? 'N/A') }}</td>
                                <td>{{ $unit->bedrooms ?? '-' }} / {{ $unit->bathrooms ?? '-' }}</td>
                                <td style="font-weight: 600;">₱{{ number_format($unit->monthly_rent ?? 0, 0) }}</td>
                                <td>
                                    @if($unit->status === 'occupied')
                                        <span class="status-badge status-occupied">Occupied</span>
                                    @elseif($unit->status === 'vacant')
                                        <span class="status-badge status-vacant">Vacant</span>
                                    @elseif($unit->status === 'maintenance' || $unit->status === 'under_maintenance')
                                        <span class="status-badge status-maintenance">Maintenance</span>
                                    @elseif($unit->status === 'ready')
                                        <span class="status-badge status-ready">Ready</span>
                                    @elseif($unit->status === 'reserved')
                                        <span class="status-badge status-reserved">Reserved</span>
                                    @else
                                        <span class="status-badge">{{ ucfirst($unit->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($unit->currentTenant)
                                        <a href="{{ route('tenants.show', $unit->currentTenant) }}" style="color: var(--accent-emerald); text-decoration: none;">
                                            {{ $unit->currentTenant->full_name }}
                                        </a>
                                    @else
                                        <span style="color: var(--text-muted);">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($unit->currentTenant && $unit->currentTenant->currentLease)
                                        @php
                                            $leaseEndDate = $unit->currentTenant->currentLease->end_date;
                                            $daysLeft = $unit->currentTenant->currentLease->days_remaining ?? null;
                                        @endphp
                                        {{ $leaseEndDate ? \Carbon\Carbon::parse($leaseEndDate)->format('M d, Y') : 'N/A' }}
                                        @if($daysLeft && $daysLeft > 0 && $daysLeft <= 30)
                                            <span class="days-badge days-warning">{{ $daysLeft }} days</span>
                                        @elseif($daysLeft && $daysLeft <= 0)
                                            <span class="days-badge days-danger">Expired</span>
                                        @endif
                                    @else
                                        <span style="color: var(--text-muted);">—</span>
                                    @endif
                                </td>
                                <td><a href="{{ route('units.show', $unit) }}" class="btn-sm">View</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="no-data">
                    <h3>No Units Added Yet</h3>
                    <p>Add units to start managing this building</p>
                    <a href="{{ route('units.create', ['building' => $building->id]) }}" class="btn" style="margin-top: 1rem;">Add Your First Unit</a>
                </div>
                @endif
            </div>
            
            <!-- Tenants Tab -->
            <div class="tab-pane" id="tenants">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                    <h3 style="font-size: 1rem; font-weight: 600; margin: 0;">Current Tenants in {{ $building->name }}</h3>
                    <a href="{{ route('tenants.create', ['building_id' => $building->id]) }}" class="btn">Add Tenant</a>
                </div>
                
                @php
                    $allTenants = $building->tenants()->with('unit')->orderBy('created_at', 'desc')->get();
                    $currentTenants = [];
                    foreach($allTenants as $tenant) {
                        $activeLease = \App\Models\Lease::where('tenant_id', $tenant->id)
                            ->where('lease_status', 'active')
                            ->latest('start_date')
                            ->first();
                        if ($activeLease) {
                            $currentTenants[] = ['tenant' => $tenant, 'lease' => $activeLease];
                        }
                    }
                @endphp
                
                @if(count($currentTenants) > 0)
                <div class="tenants-table-container">
                    <table class="tenants-table">
                        <thead>
                            <tr><th>Tenant</th><th>Contact</th><th>Unit</th><th>Lease Period</th><th>Monthly Rent</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            @foreach($currentTenants as $item)
                                @php $tenant = $item['tenant']; $lease = $item['lease']; @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('tenants.show', $tenant) }}" style="font-weight: 600; color: var(--accent-emerald); text-decoration: none;">
                                            {{ $tenant->full_name }}
                                        </a>
                                        @if($lease && $lease->start_date)
                                            <div style="color: var(--text-muted); font-size: 0.7rem;">Since {{ \Carbon\Carbon::parse($lease->start_date)->format('M Y') }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $tenant->phone ?? 'N/A' }}</div>
                                        <div style="color: var(--text-muted); font-size: 0.7rem;">{{ $tenant->email }}</div>
                                    </td>
                                    <td>
                                        @if($tenant->unit)
                                            <span style="font-weight: 500;">Unit {{ $tenant->unit->unit_number }}</span>
                                            <div style="color: var(--text-muted); font-size: 0.7rem;">{{ $tenant->unit->unit_type_label ?? '' }}</div>
                                        @else
                                            <span style="color: var(--text-muted);">No unit assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $lease->start_date ? \Carbon\Carbon::parse($lease->start_date)->format('M d, Y') : 'N/A' }}</div>
                                        <div>→ {{ $lease->end_date ? \Carbon\Carbon::parse($lease->end_date)->format('M d, Y') : 'N/A' }}</div>
                                    </td>
                                    <td style="font-weight: 600;">₱{{ number_format($lease->monthly_rent ?? 0, 0) }}</td>
                                    <td>
                                        <span class="status-badge {{ match($lease->lease_status) {
                                            'active' => 'status-active',
                                            'pending' => 'status-pending',
                                            'expired' => 'status-expired',
                                            'terminated' => 'status-terminated',
                                            default => 'status-vacant'
                                        } }}">
                                            {{ ucfirst($lease->lease_status) }}
                                        </span>
                                    </td>
                                    <td><a href="{{ route('tenants.show', $tenant) }}" class="btn-sm">View</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="no-data">
                    <h3>No Current Tenants</h3>
                    <p>This building has no active tenants at the moment</p>
                    <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 1rem; flex-wrap: wrap;">
                        <a href="{{ route('tenants.create', ['building_id' => $building->id]) }}" class="btn">Add Your First Tenant</a>
                        @if($totalUnits == 0)
                        <a href="{{ route('units.create', ['building' => $building->id]) }}" class="btn">Add Unit First</a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Maintenance Tab -->
            <div class="tab-pane" id="maintenance">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                    <h3 style="font-size: 1rem; font-weight: 600; margin: 0;">Maintenance Requests for {{ $building->name }}</h3>
                    <a href="{{ route('maintenance-requests.create', ['building_id' => $building->id]) }}" class="btn">New Request</a>
                </div>
                
                @php
                    $maintenanceRequests = collect([]);
                    if (method_exists($building, 'maintenanceRequests')) {
                        try {
                            $maintenanceRequests = $building->maintenanceRequests()
                                ->with(['unit', 'tenant', 'maintenanceCategory', 'assignedVendor', 'assignedStaff'])
                                ->orderBy('created_at', 'desc')
                                ->get();
                        } catch (\Exception $e) {
                            $maintenanceRequests = collect([]);
                        }
                    }
                @endphp
                
                @if($maintenanceRequests->count() > 0)
                <div class="maintenance-table-container">
                    <table class="maintenance-table">
                        <thead>
                            <tr><th>Title / Unit</th><th>Category</th><th>Priority</th><th>Status</th><th>Requested By</th><th>Request Date</th><th>Assigned To</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            @foreach($maintenanceRequests as $request)
                            <tr>
                                <td>
                                    <a href="{{ route('maintenance-requests.show', $request) }}" style="color: var(--accent-emerald); text-decoration: none; font-weight: 500;">
                                        {{ Str::limit($request->title, 25) }}
                                    </a>
                                    @if($request->unit)
                                        <div style="color: var(--text-muted); font-size: 0.7rem;">Unit {{ $request->unit->unit_number }}</div>
                                    @endif
                                </td>
                                <td>{{ $request->maintenanceCategory->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="status-badge {{ match($request->priority ?? '') {
                                        'emergency' => 'priority-emergency',
                                        'high' => 'priority-high',
                                        'medium' => 'priority-medium',
                                        'low' => 'priority-low',
                                        default => ''
                                    } }}">
                                        {{ ucfirst($request->priority ?? 'N/A') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge {{ match($request->status ?? '') {
                                        'submitted' => 'status-submitted',
                                        'assigned' => 'status-assigned',
                                        'in_progress' => 'status-in_progress',
                                        'completed' => 'status-completed',
                                        'cancelled' => 'status-cancelled',
                                        default => ''
                                    } }}">
                                        {{ ucfirst(str_replace('_', ' ', $request->status ?? 'N/A')) }}
                                    </span>
                                    @if(method_exists($request, 'isOverdue') && $request->isOverdue() && !in_array($request->status, ['submitted', 'completed', 'cancelled']))
                                        <span class="overdue-badge">Overdue</span>
                                    @endif
                                </td>
                                <td>
                                    @if($request->tenant)
                                        <a href="{{ route('tenants.show', $request->tenant) }}" style="color: var(--accent-emerald); text-decoration: none;">
                                            {{ $request->tenant->full_name }}
                                        </a>
                                    @else
                                        <span style="color: var(--text-muted);">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $request->request_date ? $request->request_date->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    @if($request->assignedVendor)
                                        <span>{{ $request->assignedVendor->company_name }}</span>
                                    @elseif($request->assignedStaff)
                                        <span>{{ $request->assignedStaff->name }}</span>
                                    @else
                                        <span style="color: var(--text-muted);">Unassigned</span>
                                    @endif
                                </td>
                                <td><a href="{{ route('maintenance-requests.show', $request) }}" class="btn-sm">View</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="no-data">
                    <h3>No Maintenance Requests</h3>
                    <p>This building has no maintenance requests yet</p>
                    <a href="{{ route('maintenance-requests.create', ['building_id' => $building->id]) }}" class="btn" style="margin-top: 1rem;">Create First Request</a>
                </div>
                @endif
            </div>
            
            <!-- Photos Tab -->
            <div class="tab-pane" id="photos">
                <div class="photo-section">
                    <div class="photo-header">
                        <div>
                            <h3 class="photo-title">Building Photos</h3>
                            <p style="color: var(--text-muted); font-size: 0.75rem;">Upload and manage photos of {{ $building->name }}</p>
                        </div>
                        <div>
                            <button class="btn" onclick="toggleUploadPanel()">+ Upload Photos</button>
                        </div>
                    </div>
                    
                    <div id="photo-messages"></div>
                    
                    <div id="photoUploadPanel" class="photo-upload-panel">
                        <form id="photoUploadForm" enctype="multipart/form-data">
                            @csrf
                            <div class="photo-upload-form">
                                <div class="photo-upload-field">
                                    <label>Photos</label>
                                    <div class="file-input-wrapper">
                                        <input type="file" id="photoInput" name="photos[]" accept="image/*" multiple>
                                        <div class="file-input-button">📁 Choose Files</div>
                                    </div>
                                    <div id="selectedFiles" class="selected-files"></div>
                                </div>
                                <div class="photo-upload-field">
                                    <label>Category</label>
                                    <select id="photoCategory" name="category">
                                        <option value="exterior">Exterior</option>
                                        <option value="lobby">Lobby</option>
                                        <option value="amenities">Amenities</option>
                                        <option value="unit_sample">Unit Sample</option>
                                        <option value="floor_plans">Floor Plans</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="photo-upload-field">
                                    <label>Description</label>
                                    <input type="text" id="photoDescription" name="description" placeholder="Brief description">
                                </div>
                                <div class="photo-upload-field">
                                    <button type="submit" class="btn" id="uploadBtn">Upload</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="category-filter">
                        <button class="category-btn active" data-category="all">All Photos</button>
                        <button class="category-btn" data-category="exterior">Exterior</button>
                        <button class="category-btn" data-category="lobby">Lobby</button>
                        <button class="category-btn" data-category="amenities">Amenities</button>
                        <button class="category-btn" data-category="unit_sample">Unit Sample</button>
                        <button class="category-btn" data-category="floor_plans">Floor Plans</button>
                    </div>
                    
                    <div id="photoGallery">
                        @php
                            $categoryLabels = [
                                'exterior' => 'Exterior', 'lobby' => 'Lobby', 'amenities' => 'Amenities',
                                'unit_sample' => 'Unit Sample', 'floor_plans' => 'Floor Plans', 'other' => 'Other'
                            ];
                        @endphp
                        
                        @if($building->photos && $building->photos->count() > 0)
                        <div class="photo-gallery" id="photoGalleryContainer">
                            @foreach($building->photos as $photo)
                            <div class="photo-card" data-category="{{ $photo->category }}" data-photo-id="{{ $photo->id }}" @if($photo->is_primary) style="border-color: var(--accent-emerald);" @endif>
                                <div class="photo-image-container">
                                    <img src="{{ asset('storage/' . $photo->path) }}" alt="{{ $photo->description }}" class="photo-image">
                                    @if($photo->is_primary)
                                    <div class="primary-badge">Primary</div>
                                    @endif
                                </div>
                                <div class="photo-card-content">
                                    <div class="photo-card-header">
                                        <span class="photo-category-badge">{{ $categoryLabels[$photo->category] ?? ucfirst($photo->category) }}</span>
                                        <span class="photo-date">{{ $photo->created_at ? $photo->created_at->format('M d, Y') : 'N/A' }}</span>
                                    </div>
                                    <div class="photo-description">{{ $photo->description ?? 'No description' }}</div>
                                    <div class="photo-card-actions">
                                        <button onclick="setAsPrimary({{ $photo->id }})" class="btn-sm">{{ $photo->is_primary ? '✓ Primary' : 'Set Primary' }}</button>
                                        <button onclick="viewPhoto('{{ asset('storage/' . $photo->path) }}', '{{ addslashes($photo->description ?? '') }}')" class="btn-sm">View</button>
                                        <button onclick="deletePhoto({{ $photo->id }})" class="btn-sm">Delete</button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div id="noPhotosMessage" class="no-data">
                            <h3>No Photos Uploaded Yet</h3>
                            <p>Upload photos to showcase this building</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Photo Modal -->
<div id="photoModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closePhotoModal()">&times;</button>
        <img id="modalImage" src="" alt="" class="modal-image">
        <div id="modalDescription" class="modal-description"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const categoryLabels = {
        'exterior': 'Exterior', 'lobby': 'Lobby', 'amenities': 'Amenities',
        'unit_sample': 'Unit Sample', 'floor_plans': 'Floor Plans', 'other': 'Other'
    };

    const buildingId = {{ $building->id }};

    function toggleUploadPanel() {
        document.getElementById('photoUploadPanel').classList.toggle('active');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabPanes = document.querySelectorAll('.tab-pane');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabPanes.forEach(pane => pane.classList.remove('active'));
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
                window.location.hash = tabId;
            });
        });
        
        if (window.location.hash) {
            const hash = window.location.hash.substring(1);
            const activeTab = document.querySelector(`.tab-button[data-tab="${hash}"]`);
            if (activeTab) activeTab.click();
        }
        
        const photoInput = document.getElementById('photoInput');
        const selectedFiles = document.getElementById('selectedFiles');
        
        if (photoInput && selectedFiles) {
            photoInput.addEventListener('change', function() {
                const files = this.files;
                if (files.length > 0) {
                    let totalSize = 0;
                    let fileNames = [];
                    for (let i = 0; i < Math.min(files.length, 3); i++) {
                        fileNames.push(files[i].name);
                        totalSize += files[i].size;
                    }
                    let message = `Selected ${files.length} file${files.length > 1 ? 's' : ''}`;
                    if (files.length > 3) message += `: ${fileNames.join(', ')} and ${files.length - 3} more`;
                    else message += `: ${fileNames.join(', ')}`;
                    message += ` (${formatBytes(totalSize)})`;
                    selectedFiles.textContent = message;
                } else {
                    selectedFiles.textContent = '';
                }
            });
        }
        
        initializePhotoUpload();
        
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                filterPhotosByCategory(this.getAttribute('data-category'));
            });
        });
        
        filterPhotosByCategory('all');
    });

    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(decimals)) + ' ' + ['Bytes', 'KB', 'MB', 'GB'][i];
    }

    function showMessage(message, type = 'success') {
        const container = document.getElementById('photo-messages');
        if (!container) return;
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `${message}<button onclick="this.parentElement.remove()" style="float: right; background: none; border: none; color: inherit; cursor: pointer;">&times;</button>`;
        container.innerHTML = '';
        container.appendChild(alertDiv);
        setTimeout(() => { if (alertDiv.parentElement) alertDiv.remove(); }, 5000);
    }

    function initializePhotoUpload() {
        const form = document.getElementById('photoUploadForm');
        if (!form) return;
        
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const uploadBtn = document.getElementById('uploadBtn');
            const originalText = uploadBtn.innerHTML;
            uploadBtn.innerHTML = 'Uploading...';
            uploadBtn.disabled = true;
            
            try {
                const response = await fetch('{{ route("buildings.photos.upload", $building->id) }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    document.getElementById('photoInput').value = '';
                    document.getElementById('selectedFiles').textContent = '';
                    document.getElementById('photoDescription').value = '';
                    showMessage(data.message || 'Photos uploaded successfully!', 'success');
                    if (data.photos && data.photos.length > 0) {
                        data.photos.forEach(photo => addPhotoToGallery(photo));
                    }
                } else {
                    showMessage(data.message || 'Upload failed', 'error');
                }
            } catch (error) {
                console.error('Upload error:', error);
                showMessage('An error occurred while uploading photos', 'error');
            } finally {
                uploadBtn.innerHTML = originalText;
                uploadBtn.disabled = false;
            }
        });
    }

    function addPhotoToGallery(photo) {
        let galleryContainer = document.getElementById('photoGalleryContainer');
        let noPhotosMessage = document.getElementById('noPhotosMessage');
        
        if (!galleryContainer) {
            const photoGallery = document.getElementById('photoGallery');
            photoGallery.innerHTML = '<div class="photo-gallery" id="photoGalleryContainer"></div>';
            galleryContainer = document.getElementById('photoGalleryContainer');
        }
        if (noPhotosMessage) noPhotosMessage.remove();
        
        const photoUrl = photo.url || '/storage/' + photo.path;
        const formattedDate = new Date(photo.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        
        const photoCard = document.createElement('div');
        photoCard.className = 'photo-card';
        photoCard.setAttribute('data-category', photo.category);
        photoCard.setAttribute('data-photo-id', photo.id);
        if (photo.is_primary) photoCard.style.borderColor = 'var(--accent-emerald)';
        
        photoCard.innerHTML = `
            <div class="photo-image-container">
                <img src="${photoUrl}" alt="${photo.description || ''}" class="photo-image" onerror="this.src='https://via.placeholder.com/300?text=Image+Not+Found'">
                ${photo.is_primary ? '<div class="primary-badge">Primary</div>' : ''}
            </div>
            <div class="photo-card-content">
                <div class="photo-card-header">
                    <span class="photo-category-badge">${categoryLabels[photo.category] || photo.category}</span>
                    <span class="photo-date">${formattedDate}</span>
                </div>
                <div class="photo-description">${photo.description || 'No description'}</div>
                <div class="photo-card-actions">
                    <button onclick="setAsPrimary(${photo.id})" class="btn-sm">${photo.is_primary ? '✓ Primary' : 'Set Primary'}</button>
                    <button onclick="viewPhoto('${photoUrl}', '${photo.description || ''}')" class="btn-sm">View</button>
                    <button onclick="deletePhoto(${photo.id})" class="btn-sm">Delete</button>
                </div>
            </div>
        `;
        
        galleryContainer.appendChild(photoCard);
        const activeCategory = document.querySelector('.category-btn.active')?.getAttribute('data-category') || 'all';
        filterPhotosByCategory(activeCategory);
    }

    function filterPhotosByCategory(category) {
        document.querySelectorAll('.photo-card').forEach(card => {
            if (category === 'all' || card.getAttribute('data-category') === category) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function closePhotoModal() {
        document.getElementById('photoModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function viewPhoto(url, description) {
        const modal = document.getElementById('photoModal');
        document.getElementById('modalImage').src = url;
        document.getElementById('modalDescription').textContent = description;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    async function setAsPrimary(photoId) {
        try {
            const response = await fetch('{{ route("buildings.photos.set-primary", ["building" => $building->id, "photo" => "__PHOTO_ID__"]) }}'.replace('__PHOTO_ID__', photoId), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            });
            const data = await response.json();
            if (data.success) {
                showMessage('Primary photo updated successfully!', 'success');
                document.querySelectorAll('.photo-card').forEach(card => {
                    card.style.borderColor = '';
                    const badge = card.querySelector('.primary-badge');
                    if (badge) badge.remove();
                    const btn = card.querySelector('.btn-sm:first-child');
                    if (btn && btn.textContent.includes('✓ Primary')) btn.textContent = 'Set Primary';
                });
                const selectedCard = document.querySelector(`.photo-card[data-photo-id="${photoId}"]`);
                if (selectedCard) {
                    selectedCard.style.borderColor = 'var(--accent-emerald)';
                    const imgContainer = selectedCard.querySelector('.photo-image-container');
                    const badge = document.createElement('div');
                    badge.className = 'primary-badge';
                    badge.textContent = 'Primary';
                    imgContainer.appendChild(badge);
                    const btn = selectedCard.querySelector('.btn-sm:first-child');
                    if (btn) btn.textContent = '✓ Primary';
                }
            } else {
                showMessage(data.message || 'Failed to set primary photo', 'error');
            }
        } catch (error) {
            console.error('Set primary error:', error);
            showMessage('An error occurred. Please try again.', 'error');
        }
    }

    async function deletePhoto(photoId) {
        if (!confirm('Are you sure you want to delete this photo?')) return;
        
        try {
            const response = await fetch('{{ route("buildings.photos.destroy", ["building" => $building->id, "photo" => "__PHOTO_ID__"]) }}'.replace('__PHOTO_ID__', photoId), {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            });
            const data = await response.json();
            if (data.success) {
                showMessage('Photo deleted successfully!', 'success');
                const photoCard = document.querySelector(`.photo-card[data-photo-id="${photoId}"]`);
                if (photoCard) {
                    photoCard.remove();
                    if (document.querySelectorAll('.photo-card').length === 0) {
                        const gallery = document.getElementById('photoGallery');
                        gallery.innerHTML = `<div id="noPhotosMessage" class="no-data"><h3>No Photos Uploaded Yet</h3><p>Upload photos to showcase this building</p></div>`;
                    }
                }
            } else {
                showMessage(data.message || 'Failed to delete photo', 'error');
            }
        } catch (error) {
            console.error('Delete error:', error);
            showMessage('An error occurred while deleting the photo', 'error');
        }
    }

    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closePhotoModal(); });
    window.onclick = function(event) { const modal = document.getElementById('photoModal'); if (event.target === modal) closePhotoModal(); };
</script>
@endpush