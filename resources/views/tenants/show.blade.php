@extends('layouts.app')

@section('title', $tenant->full_name . ' - Tenant Details')

@push('styles')
<style>
    /* TENANT HEADER */
    .tenant-header {
        background: white;
        color: #2c3e50;
        padding: 30px;
        border-radius: 8px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        border: 1px solid #dee2e6;
    }

    .tenant-title {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 10px;
        color: #2c3e50;
    }

    .tenant-location {
        font-size: 15px;
        color: #6c757d;
        margin-bottom: 20px;
    }

    .tenant-location a {
        color: #4a5568;
        text-decoration: none;
    }

    .tenant-location a:hover {
        color: #2d3748;
        text-decoration: underline;
    }

    .tenant-meta {
        display: flex;
        gap: 15px;
        font-size: 14px;
        flex-wrap: wrap;
        margin-top: 20px;
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

    /* OVERVIEW GRID - 4-box layout */
    .overview-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
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

    .info-value-sm {
        font-size: 13px;
        font-weight: 500;
        color: #495057;
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

    /* BADGES - No background */
    .badge {
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

    .badge-success {
        border-color: #155724;
        color: #155724;
    }

    .badge-warning {
        border-color: #856404;
        color: #856404;
    }

    .badge-danger {
        border-color: #721c24;
        color: #721c24;
    }

    .badge-secondary {
        border-color: #495057;
        color: #495057;
    }

    .badge-info {
        border-color: #0c5460;
        color: #0c5460;
    }

    /* STATUS BADGES for maintenance */
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

    /* Overdue badge */
    .overdue-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        text-align: center;
        min-width: 70px;
        border: 1px solid #e74c3c;
        color: #e74c3c;
        margin-left: 5px;
    }

    /* Previous Unit Badge */
    .previous-unit-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 500;
        margin-left: 5px;
        border: 1px solid #e67e22;
        color: #e67e22;
        background: transparent;
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

    /* Dropdown */
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
        min-width: 220px;
        padding: 8px 0;
        margin-top: 5px;
        background: white;
        border-radius: 6px;
        box-shadow: 0 8px 16px rgba(0,0,0,.1);
        border: 1px solid #dee2e6;
    }

    .dropdown-menu.show {
        display: block;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 20px;
        color: #2c3e50;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .dropdown-item:hover {
        background: #f8f9fa;
        text-decoration: none;
        color: #2c3e50;
    }

    .dropdown-item.text-danger:hover {
        background: #fef2f2;
        color: #dc3545;
    }

    .dropdown-divider {
        margin: 8px 0;
        border: 0;
        border-top: 1px solid #dee2e6;
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

    /* DAYS BADGE - No background */
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

    /* TABLES */
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
        min-width: 1000px;
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

    .maintenance-table td:nth-child(1) {
        text-align: left;
    }

    .maintenance-table tbody tr:hover td {
        background: #f8f9fa;
        border-color: #cfe2ff;
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

    /* MODAL */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,.5);
        z-index: 10000;
        align-items: center;
        justify-content: center;
    }

    .modal.show {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 8px;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,.2);
        animation: modalSlideIn 0.3s ease;
    }

    .modal-header {
        padding: 20px 30px;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-title {
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }

    .modal-body {
        padding: 20px;
        max-height: calc(90vh - 140px);
        overflow: auto;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .modal-footer {
        padding: 20px 30px;
        border-top: 1px solid #dee2e6;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-close {
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
    }

    .btn-close:hover {
        background: #f8f9fa;
        color: #2c3e50;
    }

    /* FORM CONTROLS */
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #2c3e50;
        font-size: 14px;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        font-size: 14px;
        font-family: 'Inter', sans-serif;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #4a5568;
        box-shadow: 0 0 0 3px rgba(74,85,104,0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 120px;
    }

    /* DOCUMENT MODAL */
    #documentViewerContainer {
        width: 100%;
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #documentContent img {
        max-width: 100%;
        max-height: 70vh;
        object-fit: contain;
        border-radius: 4px;
    }

    #documentContent embed,
    #documentContent iframe {
        width: 100%;
        height: 70vh;
        border: none;
        border-radius: 4px;
    }

    #documentLoadingSpinner {
        text-align: center;
        padding: 40px;
    }

    .spinner {
        display: inline-block;
        width: 50px;
        height: 50px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #4a5568;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* UTILITY CLASSES */
    .text-muted {
        color: #6c757d;
    }

    .fw-bold {
        font-weight: 600;
    }

    .small {
        font-size: 12px;
    }

    .mb-0 {
        margin-bottom: 0;
    }

    .mt-3 {
        margin-top: 15px;
    }

    .mb-3 {
        margin-bottom: 15px;
    }

    .me-2 {
        margin-right: 8px;
    }

    .me-3 {
        margin-right: 15px;
    }

    .ms-auto {
        margin-left: auto;
    }

    .bg-light {
        background: #f8f9fa;
    }

    .p-3 {
        padding: 15px;
    }

    .rounded {
        border-radius: 6px;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .page-content {
            padding: 20px 15px !important;
        }

        .tenant-header {
            padding: 25px;
        }
        
        .tenant-title {
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
        
        .tenant-meta {
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

        .maintenance-table {
            min-width: 900px;
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
            <!-- Overview Tab - 4-box layout with internal note in 4th box -->
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
                                <div class="feature-tag" style="margin-top: 10px;">
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
                                    <div class="info-value" style="color: #856404; line-height: 1.6;">{{ $tenant->notes }}</div>
                                </div>
                                <div style="margin-top: 15px; color: #6c757d; font-size: 11px; text-align: right;">
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
                                <div class="info-value" style="font-size: 20px; color: #4a5568;">₱{{ number_format($tenant->monthly_rent ?? 0, 2) }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Security Deposit</div>
                                <div class="info-value">₱{{ number_format($tenant->security_deposit ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Box 3: Lease Expiry Alert -->
                    @if($tenant->lease_status === 'active' && $daysLeft <= 30 && $daysLeft > 0)
                    <div class="overview-box" style="background: #fff3cd;">
                        <div class="overview-box-header" style="background: #ffeaa7;">Lease Expiry Alert</div>
                        <div class="overview-box-content">
                            <div class="info-item">
                                <div class="info-value" style="color: #856404; text-align: center; font-size: 16px;">
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
                                <span style="font-size: 48px; display: block; margin-bottom: 15px;">📄</span>
                                <a href="{{ Storage::url($tenant->lease_agreement_path) }}" target="_blank" class="btn btn-sm">
                                    View Agreement
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Lease History -->
                <div style="margin-top: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="font-size: 18px; color: #2c3e50;">Lease History</h3>
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
                        <div class="no-data" style="padding: 40px 20px;">
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
                                <div class="info-value" style="font-size: 20px;">{{ $tenant->unit->unit_number }}</div>
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

                    <!-- Box 4: View Unit -->
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
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div>
                        <h3 style="font-size: 18px; color: #2c3e50; margin-bottom: 5px;">Maintenance Requests</h3>
                        <p style="color: #6c757d; font-size: 13px;">
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
                                    <a href="{{ route('maintenance-requests.show', $request) }}" style="color: #4a5568; text-decoration: none; font-weight: 500; display: block;">
                                        {{ Str::limit($request->title, 25) }}
                                    </a>
                                    @if($request->unit)
                                        <span style="color: #6c757d; font-size: 11px; display: block; margin-top: 2px;">
                                            <a href="{{ route('units.show', $request->unit) }}" style="color: #6c757d; text-decoration: none;">
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
                                        <span style="color: #6c757d;">Unassigned</span>
                                    @endif
                                </td>
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
                
                @if($tenant->unit && method_exists($tenant->unit, 'maintenanceRequests') && $tenant->unit->maintenanceRequests()->count() > 10)
                <div style="text-align: center; margin-top: 20px;">
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
                    <a href="{{ route('maintenance-requests.create', ['tenant_id' => $tenant->id, 'unit_id' => $tenant->unit_id]) }}" class="btn" style="margin-top: 15px;">
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
                            <div style="font-size: 48px; margin-bottom: 15px;">🆔</div>
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
                            <div style="font-size: 48px; margin-bottom: 15px;">📄</div>
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
                <p style="margin-top: 15px; color: #6c757d;">Loading document...</p>
            </div>
            <div id="documentContent" style="display: none;">
                <!-- Content will be injected here -->
            </div>
            <div id="documentError" style="display: none; color: #dc3545; padding: 20px; text-align: center;">
                <span style="font-size: 48px;">⚠️</span>
                <p style="margin-top: 15px; font-size: 16px;">Failed to load document. Please try again.</p>
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
                        <div style="text-align: center; padding: 40px;">
                            <span style="font-size: 64px;">📁</span>
                            <h3 style="margin: 20px 0 10px; color: #2c3e50;">Cannot Preview File</h3>
                            <p style="color: #6c757d; margin-bottom: 20px;">This file type (${fileExtension}) cannot be previewed.</p>
                            <p style="color: #6c757d;">Please download the file to view it.</p>
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