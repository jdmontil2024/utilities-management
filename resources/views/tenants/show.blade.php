@extends('layouts.app')

@section('title', $tenant->full_name . ' - Tenant Details')

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

    /* TENANT HEADER */
    .tenant-header {
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
    
    .tenant-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0 0 0.5rem 0;
        color: var(--text-main);
    }
    
    .tenant-location {
        color: var(--text-muted);
        margin-bottom: 1rem;
        font-size: 0.9rem;
    }
    
    .tenant-location a {
        color: var(--accent-emerald);
        text-decoration: none;
    }
    
    .tenant-location a:hover {
        text-decoration: underline;
    }
    
    .tenant-meta {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 0.5rem;
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
    
    .info-value-sm {
        font-size: 0.8rem;
        font-weight: 400;
        color: var(--text-muted);
    }
    
    .description-text {
        color: var(--text-muted);
        font-size: 0.85rem;
        line-height: 1.6;
        margin: 0;
    }
    
    /* BADGES */
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 500;
        text-align: center;
        background: transparent;
        border: 1px solid;
        min-width: 90px;
    }
    
    .badge-success {
        border-color: var(--accent-emerald);
        color: var(--accent-emerald);
    }
    
    .badge-warning {
        border-color: var(--accent-warning);
        color: var(--accent-warning);
    }
    
    .badge-danger {
        border-color: var(--accent-red);
        color: var(--accent-red);
    }
    
    .badge-secondary {
        border-color: #6c757d;
        color: #6c757d;
    }
    
    .badge-info {
        border-color: var(--accent-blue);
        color: var(--accent-blue);
    }
    
    /* STATUS BADGES for maintenance */
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
    
    .status-submitted {
        border-color: var(--text-muted);
        color: var(--text-muted);
    }
    
    .status-assigned {
        border-color: var(--accent-warning);
        color: var(--accent-warning);
    }
    
    .status-in_progress {
        border-color: var(--accent-blue);
        color: var(--accent-blue);
    }
    
    .status-completed {
        border-color: var(--accent-emerald);
        color: var(--accent-emerald);
    }
    
    .status-cancelled {
        border-color: var(--accent-red);
        color: var(--accent-red);
    }
    
    /* PRIORITY BADGES */
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
    
    .overdue-badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 500;
        margin-left: 0.5rem;
        border: 1px solid var(--accent-red);
        color: var(--accent-red);
    }
    
    .previous-unit-badge {
        display: inline-block;
        padding: 0.15rem 0.5rem;
        border-radius: 12px;
        font-size: 0.6rem;
        font-weight: 500;
        margin-left: 0.5rem;
        border: 1px solid var(--accent-warning);
        color: var(--accent-warning);
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
    
    /* DROPDOWN */
    .dropdown {
        position: relative;
        display: inline-block;
    }
    
    .dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        z-index: 1000;
        display: none;
        min-width: 200px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        margin-top: 0.25rem;
    }
    
    .dropdown-menu.show {
        display: block;
    }
    
    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1rem;
        color: var(--text-main);
        text-decoration: none;
        font-size: 0.8rem;
        transition: all 0.2s ease;
        background: transparent;
        border: none;
        width: 100%;
        text-align: left;
        cursor: pointer;
    }
    
    .dropdown-item:hover {
        background: rgba(255, 255, 255, 0.05);
        color: var(--accent-emerald);
    }
    
    .dropdown-item.text-danger:hover {
        background: rgba(239, 68, 68, 0.1);
        color: var(--accent-red);
    }
    
    .dropdown-divider {
        margin: 0.5rem 0;
        border: 0;
        border-top: 1px solid var(--border-color);
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
        min-width: 900px;
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
        vertical-align: middle;
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
    
    .modal.show {
        display: flex;
    }
    
    .modal-content {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        width: 90%;
        max-width: 800px;
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
    
    .modal-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-main);
        margin: 0;
    }
    
    .modal-close, .btn-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--text-muted);
        transition: all 0.2s ease;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .modal-close:hover, .btn-close:hover {
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
    
    .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }
    
    /* FORM CONTROLS */
    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--text-main);
        font-size: 0.8rem;
    }
    
    .form-control {
        width: 100%;
        padding: 0.6rem 0.75rem;
        background: var(--bg-deep);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 0.8rem;
        font-family: 'Inter', sans-serif;
        color: var(--text-main);
        transition: all 0.2s ease;
    }
    
    .form-control:focus {
        outline: none;
        border-color: var(--accent-emerald);
    }
    
    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }
    
    .mb-3 {
        margin-bottom: 1rem;
    }
    
    /* UTILITY */
    .text-muted {
        color: var(--text-muted);
    }
    
    .mt-3 {
        margin-top: 1rem;
    }
    
    /* RESPONSIVE */
    @media (max-width: 768px) {
        .dashboard-wrapper { padding: 1rem; }
        .tab-content { padding: 1rem; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .action-buttons { width: 100%; justify-content: flex-start; }
        .overview-grid { grid-template-columns: 1fr; }
        .tenant-meta { flex-direction: column; gap: 0.5rem; }
        .days-badge { margin-left: 0; }
        .maintenance-table { min-width: 750px; }
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
            <h1 class="page-title">Tenant Details</h1>
            <p class="page-subtitle">View and manage tenant information</p>
        </div>
    </div>

    <!-- Tenant Header -->
    @php
        $statusColors = [
            'active' => 'badge-success',
            'pending' => 'badge-warning',
            'expired' => 'badge-danger',
            'terminated' => 'badge-secondary',
        ];
        $statusLabels = [
            'active' => 'Active',
            'pending' => 'Pending',
            'expired' => 'Expired',
            'terminated' => 'Terminated',
        ];
        $daysLeft = $tenant->lease_end_date ? (int) now()->diffInDays(\Carbon\Carbon::parse($tenant->lease_end_date), false) : 0;
    @endphp

    <div class="tenant-header">
        <div class="header-content">
            <div class="header-left">
                <h1 class="tenant-title">{{ $tenant->full_name }}</h1>
                <div class="tenant-location">
                    @if($tenant->unit && $tenant->building)
                        <a href="{{ route('units.show', $tenant->unit) }}">
                            Unit {{ $tenant->unit->unit_number }}
                        </a> 
                        • {{ $tenant->building->name }}
                    @else
                        No unit assigned
                    @endif
                </div>
                <div class="tenant-meta">
                    <div class="meta-item">
                        {{ $tenant->email }}
                    </div>
                    <div class="meta-item">
                        {{ $tenant->phone }}
                    </div>
                    <div class="meta-item">
                        <span class="badge {{ $statusColors[$tenant->lease_status] ?? 'badge-secondary' }}">
                            {{ $statusLabels[$tenant->lease_status] ?? ucfirst($tenant->lease_status) }}
                        </span>
                    </div>
                    @if($tenant->lease_status === 'active' && $daysLeft <= 30 && $daysLeft > 0)
                        <div class="meta-item">
                            <span class="days-badge days-warning">{{ $daysLeft }} days left</span>
                        </div>
                    @endif
                    @if($tenant->lease_start_date)
                    <div class="meta-item">
                        Since {{ \Carbon\Carbon::parse($tenant->lease_start_date)->format('M Y') }}
                    </div>
                    @endif
                </div>
            </div>
            <div class="action-buttons">
                <a href="{{ route('tenants.edit', $tenant) }}" class="btn">
                    Edit Tenant
                </a>
                <div class="dropdown">
                    <button class="btn" id="dropdownMenuButton">
                        More
                    </button>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <a class="dropdown-item" href="{{ route('tenants.lease-history', $tenant) }}">
                            Lease History
                        </a>
                        <a class="dropdown-item" href="{{ route('tenants.payment-methods', $tenant) }}">
                            Payment Methods
                        </a>
                        <a class="dropdown-item" href="{{ route('tenants.maintenance-requests', $tenant) }}">
                            Maintenance Requests
                        </a>
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('tenants.destroy', $tenant) }}" method="POST" 
                              onsubmit="return confirmDeleteTenant(this, '{{ addslashes($tenant->full_name) }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger">
                                Delete Tenant
                            </button>
                        </form>
                    </div>
                </div>
                @if($tenant->unit)
                <a href="{{ route('units.show', $tenant->unit) }}" class="btn">
                    Back to Unit
                </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    @php
        $maintenanceRequests = $tenant->maintenanceRequests ?? collect([]);
    @endphp
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-value">₱{{ number_format($tenant->monthly_rent ?? 0, 0) }}</span>
            <span class="stat-label">Monthly Rent</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">
                @if($tenant->lease_start_date && $tenant->lease_end_date)
                    @php
                        $start = \Carbon\Carbon::parse($tenant->lease_start_date);
                        $end = \Carbon\Carbon::parse($tenant->lease_end_date);
                        $months = $start->floatDiffInMonths($end);
                        echo number_format($months, 2);
                    @endphp
                @else
                    0
                @endif
            </span>
            <span class="stat-label">Months</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ $tenant->number_of_occupants ?? 1 }}</span>
            <span class="stat-label">Occupants</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">
                {{ $maintenanceRequests->count() }}
            </span>
            <span class="stat-label">Requests</span>
        </div>
    </div>

    <!-- Tab Interface -->
    <div class="tab-container">
        <div class="tab-header">
            <button class="tab-button active" data-tab="overview">Overview</button>
            <button class="tab-button" data-tab="lease">Lease Details</button>
            <button class="tab-button" data-tab="unit">Unit</button>
            <button class="tab-button" data-tab="maintenance">Maintenance</button>
            <button class="tab-button" data-tab="documents">Documents</button>
        </div>
        
        <div class="tab-content">
            <!-- Overview Tab -->
            <div class="tab-pane active" id="overview">
                <div class="overview-grid">
                    <!-- Box 1: Emergency Contact -->
                    <div class="overview-box">
                        <div class="overview-box-header">Emergency Contact</div>
                        <div class="overview-box-content">
                            @if($tenant->emergency_contact_name)
                                <div class="info-item">
                                    <div class="info-label">Name</div>
                                    <div class="info-value">{{ $tenant->emergency_contact_name }}</div>
                                </div>
                                @if($tenant->emergency_contact_phone)
                                <div class="info-item">
                                    <div class="info-label">Phone</div>
                                    <div class="info-value">{{ $tenant->emergency_contact_phone }}</div>
                                </div>
                                @endif
                                @if($tenant->emergency_contact_relation)
                                <div class="info-item">
                                    <div class="info-label">Relation</div>
                                    <div class="info-value">{{ $tenant->emergency_contact_relation }}</div>
                                </div>
                                @endif
                            @else
                                <div class="info-item">
                                    <div class="info-value">No emergency contact information provided.</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Box 2: Employment & Income -->
                    <div class="overview-box">
                        <div class="overview-box-header">Employment & Income</div>
                        <div class="overview-box-content">
                            @if($tenant->occupation || $tenant->employer || $tenant->annual_income)
                                @if($tenant->occupation)
                                <div class="info-item">
                                    <div class="info-label">Occupation</div>
                                    <div class="info-value">{{ $tenant->occupation }}</div>
                                </div>
                                @endif
                                @if($tenant->employer)
                                <div class="info-item">
                                    <div class="info-label">Employer</div>
                                    <div class="info-value">{{ $tenant->employer }}</div>
                                </div>
                                @endif
                                @if($tenant->annual_income)
                                <div class="info-item">
                                    <div class="info-label">Annual Income</div>
                                    <div class="info-value">₱{{ number_format($tenant->annual_income, 2) }}</div>
                                </div>
                                @endif
                            @else
                                <div class="info-item">
                                    <div class="info-value">No employment information provided.</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Box 3: Additional Occupants -->
                    <div class="overview-box">
                        <div class="overview-box-header">Additional Occupants</div>
                        <div class="overview-box-content">
                            @if($tenant->additional_occupants && count($tenant->additional_occupants) > 0)
                                @foreach($tenant->additional_occupants as $occupant)
                                    <div class="info-item">
                                        <div class="info-label">{{ $occupant['name'] ?? 'N/A' }}</div>
                                        <div class="info-value-sm">
                                            {{ ucfirst($occupant['relation'] ?? 'N/A') }} • Age: {{ $occupant['age'] ?? 'N/A' }}
                                        </div>
                                    </div>
                                @endforeach
                                <div class="feature-tag" style="margin-top: 0.5rem;">
                                    Total: {{ $tenant->number_of_occupants }} occupants
                                </div>
                            @else
                                <div class="info-item">
                                    <div class="info-value">No additional occupants listed.</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Box 4: Internal Notes -->
                    <div class="overview-box">
                        <div class="overview-box-header">Internal Notes</div>
                        <div class="overview-box-content">
                            @if($tenant->notes)
                                <div class="info-item" style="border-bottom: none;">
                                    <div class="info-value" style="color: var(--accent-warning); line-height: 1.6;">{{ $tenant->notes }}</div>
                                </div>
                                <div style="margin-top: 1rem; color: var(--text-muted); font-size: 0.65rem; text-align: right;">
                                    Last updated: {{ $tenant->updated_at ? $tenant->updated_at->format('M d, Y h:i A') : 'N/A' }}
                                </div>
                            @else
                                <div class="info-item">
                                    <div class="info-value">No internal notes available.</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Lease Details Tab -->
            <div class="tab-pane" id="lease">
                <div class="overview-grid">
                    <!-- Box 1: Lease Information -->
                    <div class="overview-box">
                        <div class="overview-box-header">Lease Information</div>
                        <div class="overview-box-content">
                            <div class="info-item">
                                <div class="info-label">Status</div>
                                <div class="info-value">
                                    <span class="badge {{ $statusColors[$tenant->lease_status] ?? 'badge-secondary' }}">
                                        {{ $statusLabels[$tenant->lease_status] ?? ucfirst($tenant->lease_status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Start Date</div>
                                <div class="info-value">{{ $tenant->lease_start_date ? \Carbon\Carbon::parse($tenant->lease_start_date)->format('M d, Y') : 'N/A' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">End Date</div>
                                <div class="info-value">{{ $tenant->lease_end_date ? \Carbon\Carbon::parse($tenant->lease_end_date)->format('M d, Y') : 'N/A' }}</div>
                            </div>
                            @if($tenant->lease_start_date && $tenant->lease_end_date)
                            <div class="info-item">
                                <div class="info-label">Lease Term</div>
                                <div class="info-value">{{ \Carbon\Carbon::parse($tenant->lease_start_date)->diffInMonths(\Carbon\Carbon::parse($tenant->lease_end_date)) }} months</div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Box 2: Financial Details -->
                    <div class="overview-box">
                        <div class="overview-box-header">Financial Details</div>
                        <div class="overview-box-content">
                            <div class="info-item">
                                <div class="info-label">Monthly Rent</div>
                                <div class="info-value" style="font-size: 1.25rem; color: var(--accent-emerald);">₱{{ number_format($tenant->monthly_rent ?? 0, 2) }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Security Deposit</div>
                                <div class="info-value">₱{{ number_format($tenant->security_deposit ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Box 3: Lease Expiry Alert -->
                    @if($tenant->lease_status === 'active' && $daysLeft <= 30 && $daysLeft > 0)
                    <div class="overview-box" style="border-color: var(--accent-warning);">
                        <div class="overview-box-header" style="color: var(--accent-warning);">Lease Expiry Alert</div>
                        <div class="overview-box-content">
                            <div class="info-item">
                                <div class="info-value" style="color: var(--accent-warning); text-align: center; font-size: 0.9rem;">
                                    ⚠️ Lease expires in <strong>{{ $daysLeft }} days</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Box 4: Lease Agreement -->
                    @if($tenant->lease_agreement_path)
                    <div class="overview-box">
                        <div class="overview-box-header">Lease Agreement</div>
                        <div class="overview-box-content" style="text-align: center;">
                            <div class="info-item">
                                <span style="font-size: 2.5rem; display: block; margin-bottom: 0.75rem;">📄</span>
                                <a href="{{ Storage::url($tenant->lease_agreement_path) }}" target="_blank" class="btn btn-sm">
                                    View Agreement
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Lease History -->
                <div style="margin-top: 2rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                        <h3 style="font-size: 1rem; font-weight: 600; margin: 0;">Lease History</h3>
                        <a href="{{ route('tenants.lease-history', $tenant) }}" class="btn btn-sm">
                            View Full History
                        </a>
                    </div>
                    @if($tenant->leases && $tenant->leases->count() > 1)
                        <div class="maintenance-table-container">
                            <table class="maintenance-table">
                                <thead>
                                    <tr>
                                        <th>Unit</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tenant->leases as $lease)
                                    @if($lease->id != ($tenant->currentLease->id ?? null))
                                    <tr>
                                        <td>Unit {{ $lease->unit->unit_number ?? 'N/A' }}</td>
                                        <td>{{ $lease->start_date ? \Carbon\Carbon::parse($lease->start_date)->format('M d, Y') : 'N/A' }}</td>
                                        <td>{{ $lease->end_date ? \Carbon\Carbon::parse($lease->end_date)->format('M d, Y') : 'N/A' }}</td>
                                        <td>
                                            <span class="badge {{ $statusColors[$lease->lease_status] ?? 'badge-secondary' }}">
                                                {{ $statusLabels[$lease->lease_status] ?? ucfirst($lease->lease_status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('leases.show', $lease) }}" class="btn-sm">View</a>
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="no-data" style="padding: 2rem;">
                            <div class="no-data-icon">📋</div>
                            <h3>No Lease History</h3>
                            <p>Previous leases will appear here</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Unit Tab -->
            <div class="tab-pane" id="unit">
                @if($tenant->unit)
                <div class="overview-grid">
                    <!-- Box 1: Unit Information -->
                    <div class="overview-box">
                        <div class="overview-box-header">Unit Information</div>
                        <div class="overview-box-content">
                            <div class="info-item">
                                <div class="info-label">Building</div>
                                <div class="info-value">{{ $tenant->building->name ?? 'N/A' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Address</div>
                                <div class="info-value">{{ $tenant->building->address ?? '' }}, {{ $tenant->building->city ?? '' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Unit Number</div>
                                <div class="info-value" style="font-size: 1.25rem;">{{ $tenant->unit->unit_number }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Floor</div>
                                <div class="info-value">{{ $tenant->unit->floor ?? 'N/A' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Type</div>
                                <div class="info-value">{{ $tenant->unit->unit_type_label ?? ucfirst($tenant->unit->unit_type) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Box 2: Unit Specifications -->
                    <div class="overview-box">
                        <div class="overview-box-header">Specifications</div>
                        <div class="overview-box-content">
                            <div class="info-item">
                                <div class="info-label">Bedrooms</div>
                                <div class="info-value">{{ $tenant->unit->bedrooms ?? 0 }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Bathrooms</div>
                                <div class="info-value">{{ $tenant->unit->bathrooms ?? 0 }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Area</div>
                                <div class="info-value">{{ $tenant->unit->area ? number_format($tenant->unit->area) . ' sq ft' : 'N/A' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Box 3: Unit Features -->
                    @if($tenant->unit->features && is_array($tenant->unit->features) && count($tenant->unit->features) > 0)
                    <div class="overview-box">
                        <div class="overview-box-header">Features</div>
                        <div class="overview-box-content">
                            <div class="features-list">
                                @foreach($tenant->unit->features as $feature)
                                    <span class="feature-tag">
                                        {{ ucfirst(str_replace('_', ' ', $feature)) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Box 4: Actions -->
                    <div class="overview-box">
                        <div class="overview-box-header">Actions</div>
                        <div class="overview-box-content" style="text-align: center;">
                            <div class="info-item">
                                <a href="{{ route('units.show', $tenant->unit) }}" class="btn">
                                    View Unit Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="no-data">
                    <div class="no-data-icon">🏠</div>
                    <h3>No Unit Assigned</h3>
                    <p>This tenant doesn't have a unit assigned yet</p>
                </div>
                @endif
            </div>

            <!-- Maintenance Tab -->
            <div class="tab-pane" id="maintenance">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <h3 style="font-size: 1rem; font-weight: 600; margin: 0;">Maintenance Requests</h3>
                        <p style="color: var(--text-muted); font-size: 0.7rem; margin-top: 0.25rem;">
                            Showing maintenance requests for all units this tenant has occupied
                        </p>
                    </div>
                    @if($tenant->unit)
                    <a href="{{ route('maintenance-requests.create', ['tenant_id' => $tenant->id, 'unit_id' => $tenant->unit_id]) }}" class="btn">
                        New Request
                    </a>
                    @endif
                </div>
                
                @if($maintenanceRequests && $maintenanceRequests->count() > 0)
                <div class="maintenance-table-container">
                    <table class="maintenance-table">
                        <thead>
                            <tr>
                                <th>Title / Unit</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Request Date</th>
                                <th>Assigned To</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($maintenanceRequests as $request)
                            <tr>
                                <td>
                                    <a href="{{ route('maintenance-requests.show', $request) }}" style="color: var(--accent-emerald); text-decoration: none; font-weight: 500; display: block;">
                                        {{ Str::limit($request->title, 25) }}
                                    </a>
                                    @if($request->unit)
                                        <span style="color: var(--text-muted); font-size: 0.65rem; display: block; margin-top: 0.25rem;">
                                            <a href="{{ route('units.show', $request->unit) }}" style="color: var(--text-muted); text-decoration: none;">
                                                Unit {{ $request->unit->unit_number }}
                                            </a>
                                            @if($request->unit->id != $tenant->unit_id)
                                                <span class="previous-unit-badge">Previous Unit</span>
                                            @endif
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $request->maintenanceCategory->name ?? 'N/A' }}</td>
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
                                    @if(method_exists($request, 'isOverdue') && $request->isOverdue() && !in_array($request->status, ['submitted', 'completed', 'cancelled']))
                                        <span class="overdue-badge">Overdue</span>
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
                                <td>
                                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
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
                
                @if($tenant->unit && method_exists($tenant->unit, 'maintenanceRequests') && $tenant->unit->maintenanceRequests()->count() > 10)
                <div style="text-align: center; margin-top: 1.25rem;">
                    <a href="{{ route('maintenance-requests.index', ['unit_id' => $tenant->unit_id]) }}" class="btn">
                        View All Maintenance Requests
                    </a>
                </div>
                @endif
                
                @else
                <div class="no-data">
                    <div class="no-data-icon">🔧</div>
                    <h3>No Maintenance Requests</h3>
                    <p>This tenant hasn't submitted any maintenance requests yet</p>
                    @if($tenant->unit)
                    <a href="{{ route('maintenance-requests.create', ['tenant_id' => $tenant->id, 'unit_id' => $tenant->unit_id]) }}" class="btn" style="margin-top: 1rem;">
                        Create First Request
                    </a>
                    @endif
                </div>
                @endif
            </div>

            <!-- Documents Tab -->
            <div class="tab-pane" id="documents">
                <div class="overview-grid">
                    <!-- Identification Document -->
                    @if($tenant->government_id)
                    <div class="overview-box">
                        <div class="overview-box-header">Identification Document</div>
                        <div class="overview-box-content" style="text-align: center;">
                            <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">🆔</div>
                            <div class="info-item">
                                <div class="info-label">Document Type</div>
                                <div class="info-value">{{ $tenant->id_type_label ?? ucfirst($tenant->id_type) }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Uploaded On</div>
                                <div class="info-value">{{ $tenant->created_at ? $tenant->created_at->format('M d, Y') : 'N/A' }}</div>
                            </div>
                            <button onclick="openDocumentModal('{{ Storage::url($tenant->government_id) }}', '{{ $tenant->id_type_label ?? ucfirst($tenant->id_type) }}')" class="btn btn-sm">
                                View Document
                            </button>
                        </div>
                    </div>
                    @endif

                    <!-- Lease Agreement -->
                    @if($tenant->lease_agreement_path)
                    <div class="overview-box">
                        <div class="overview-box-header">Lease Agreement</div>
                        <div class="overview-box-content" style="text-align: center;">
                            <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">📄</div>
                            <div class="info-item">
                                <div class="info-label">Document</div>
                                <div class="info-value">Lease Agreement</div>
                            </div>
                            <a href="{{ Storage::url($tenant->lease_agreement_path) }}" target="_blank" class="btn btn-sm">
                                View Document
                            </a>
                        </div>
                    </div>
                    @endif
                </div>

                @if(!$tenant->government_id && !$tenant->lease_agreement_path)
                <div class="no-data">
                    <div class="no-data-icon">📎</div>
                    <h3>No Documents</h3>
                    <p>Lease agreements, identification documents, and other files will appear here</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Document Viewer Modal -->
<div class="modal" id="documentViewerModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="documentModalTitle">Identification Document</h3>
            <button type="button" class="btn-close" onclick="closeDocumentModal()">&times;</button>
        </div>
        <div class="modal-body" id="documentViewerContainer">
            <div id="documentLoadingSpinner">
                <div class="spinner"></div>
                <p style="margin-top: 1rem; color: var(--text-muted);">Loading document...</p>
            </div>
            <div id="documentContent" style="display: none;">
                <!-- Content will be injected here -->
            </div>
            <div id="documentError" style="display: none; color: var(--accent-red); padding: 1.25rem; text-align: center;">
                <span style="font-size: 2.5rem;">⚠️</span>
                <p style="margin-top: 1rem; font-size: 0.85rem;">Failed to load document. Please try again.</p>
            </div>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal" id="addNoteModal">
    <div class="modal-content">
        <form action="{{ route('tenants.update', $tenant) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h3 class="modal-title">Add Note</h3>
                <button type="button" class="btn-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="first_name" value="{{ $tenant->first_name }}">
                <input type="hidden" name="last_name" value="{{ $tenant->last_name }}">
                <input type="hidden" name="email" value="{{ $tenant->email }}">
                <input type="hidden" name="phone" value="{{ $tenant->phone }}">
                <input type="hidden" name="unit_id" value="{{ $tenant->unit_id }}">
                <input type="hidden" name="building_id" value="{{ $tenant->building_id }}">
                <input type="hidden" name="lease_start_date" value="{{ $tenant->lease_start_date ? \Carbon\Carbon::parse($tenant->lease_start_date)->format('Y-m-d') : '' }}">
                <input type="hidden" name="lease_end_date" value="{{ $tenant->lease_end_date ? \Carbon\Carbon::parse($tenant->lease_end_date)->format('Y-m-d') : '' }}">
                <input type="hidden" name="monthly_rent" value="{{ $tenant->monthly_rent }}">
                <input type="hidden" name="lease_status" value="{{ $tenant->lease_status }}">
                <input type="hidden" name="number_of_occupants" value="{{ $tenant->number_of_occupants }}">
                <input type="hidden" name="is_active" value="{{ $tenant->is_active ? 1 : 0 }}">
                
                <div class="mb-3">
                    <label for="note" class="form-label">Note</label>
                    <textarea name="notes" id="note" class="form-control" rows="5" placeholder="Enter your note here...">{{ $tenant->notes }}</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn">Save Note</button>
            </div>
        </form>
    </div>
</div>

<style>
    .spinner {
        display: inline-block;
        width: 40px;
        height: 40px;
        border: 3px solid rgba(255, 255, 255, 0.1);
        border-top: 3px solid var(--accent-emerald);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
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

        // Dropdown Menu
        const dropdownButton = document.getElementById('dropdownMenuButton');
        const dropdownMenu = document.getElementById('dropdownMenu');
        
        if (dropdownButton && dropdownMenu) {
            dropdownButton.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
            });

            document.addEventListener('click', function() {
                dropdownMenu.classList.remove('show');
            });
        }
    });

    // Modal functions for Add Note
    window.openModal = function() {
        document.getElementById('addNoteModal').classList.add('show');
        document.body.style.overflow = 'hidden';
    };

    window.closeModal = function() {
        document.getElementById('addNoteModal').classList.remove('show');
        document.body.style.overflow = '';
    };

    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('addNoteModal');
        if (e.target === modal) {
            closeModal();
        }
    });

    // Document Modal functions
    window.openDocumentModal = function(url, title) {
        const modal = document.getElementById('documentViewerModal');
        const modalTitle = document.getElementById('documentModalTitle');
        const loadingSpinner = document.getElementById('documentLoadingSpinner');
        const contentDiv = document.getElementById('documentContent');
        const errorDiv = document.getElementById('documentError');
        
        // Reset and show modal
        modalTitle.textContent = `${title || 'Identification Document'}`;
        loadingSpinner.style.display = 'block';
        contentDiv.style.display = 'none';
        contentDiv.innerHTML = '';
        errorDiv.style.display = 'none';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Determine file type and display accordingly
        const fileExtension = url.split('.').pop().toLowerCase();
        
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Failed to load document');
                return response;
            })
            .then(() => {
                loadingSpinner.style.display = 'none';
                
                if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(fileExtension)) {
                    // Display image
                    contentDiv.innerHTML = `<img src="${url}" alt="Document" style="max-width: 100%; max-height: 70vh;">`;
                    contentDiv.style.display = 'block';
                } else if (['pdf'].includes(fileExtension)) {
                    // Display PDF
                    contentDiv.innerHTML = `<embed src="${url}" type="application/pdf" width="100%" height="600px">`;
                    contentDiv.style.display = 'block';
                } else {
                    // Unknown file type
                    contentDiv.innerHTML = `
                        <div style="text-align: center; padding: 2rem;">
                            <span style="font-size: 3rem;">📁</span>
                            <h3 style="margin: 1rem 0 0.5rem; color: var(--text-main);">Cannot Preview File</h3>
                            <p style="color: var(--text-muted); margin-bottom: 1rem;">This file type (${fileExtension}) cannot be previewed.</p>
                            <p style="color: var(--text-muted);">Please download the file to view it.</p>
                        </div>
                    `;
                    contentDiv.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error loading document:', error);
                loadingSpinner.style.display = 'none';
                errorDiv.style.display = 'block';
            });
    };

    window.closeDocumentModal = function() {
        const modal = document.getElementById('documentViewerModal');
        const contentDiv = document.getElementById('documentContent');
        modal.classList.remove('show');
        document.body.style.overflow = '';
        
        // Clear content to prevent memory issues
        setTimeout(() => {
            contentDiv.innerHTML = '';
        }, 300);
    };

    // Close document modal when clicking outside
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('documentViewerModal');
        if (e.target === modal) {
            closeDocumentModal();
        }
    });

    // Delete confirmation
    window.confirmDeleteTenant = function(form, tenantName) {
        event.preventDefault();
        
        if (confirm(`Are you sure you want to delete "${tenantName}"? This action cannot be undone.`)) {
            const button = form.querySelector('button[type="submit"]');
            button.innerHTML = '⏳';
            button.disabled = true;
            form.submit();
        }
        
        return false;
    };

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