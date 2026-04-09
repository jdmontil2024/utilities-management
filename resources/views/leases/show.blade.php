@extends('layouts.app')

@section('title', 'Lease ' . $lease->lease_number . ' - Lease Details')

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
    }

    .dashboard-wrapper { background-color: var(--bg-deep); min-height: 100vh; padding: 2rem; color: var(--text-main); font-family: 'Inter', sans-serif; }
    
    .page-header { border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem; margin-bottom: 2rem; }
    .page-title { font-size: 1.75rem; font-weight: 700; margin: 0; color: #fff; }
    .page-subtitle { color: var(--text-muted); margin-top: 0.25rem; }

    /* LEASE HEADER */
    .lease-header {
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
    
    .lease-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0 0 0.5rem 0;
        color: var(--text-main);
    }
    
    .lease-location {
        color: var(--text-muted);
        margin-bottom: 1rem;
        font-size: 0.9rem;
    }
    
    .lease-location a {
        color: var(--accent-emerald);
        text-decoration: none;
    }
    
    .lease-location a:hover {
        text-decoration: underline;
    }
    
    .lease-meta {
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
    
    .info-value a {
        color: var(--accent-emerald);
        text-decoration: none;
    }
    
    .info-value a:hover {
        text-decoration: underline;
    }
    
    /* PROGRESS BAR */
    .progress-container {
        margin-top: 0.5rem;
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
    
    /* FEATURES LIST */
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
    
    .status-active {
        border-color: var(--accent-emerald);
        color: var(--accent-emerald);
    }
    
    .status-pending {
        border-color: var(--accent-warning);
        color: var(--accent-warning);
    }
    
    .status-expired {
        border-color: var(--accent-red);
        color: var(--accent-red);
    }
    
    .status-terminated {
        border-color: #6c757d;
        color: #6c757d;
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
    
    .action-buttons {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    
    /* DOCUMENT PREVIEW */
    .document-preview {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
    }
    .document-icon {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }
    
    /* RESPONSIVE */
    @media (max-width: 768px) {
        .dashboard-wrapper { padding: 1rem; }
        .tab-content { padding: 1rem; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .action-buttons { width: 100%; justify-content: flex-start; }
        .overview-grid { grid-template-columns: 1fr; }
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
            <h1 class="page-title">Lease Details</h1>
            <p class="page-subtitle">{{ $lease->lease_number }} • {{ $lease->tenant->full_name }}</p>
        </div>
    </div>

    <!-- Lease Header -->
    @php
        $daysRemaining = $lease->end_date ? max(0, now()->diffInDays(\Carbon\Carbon::parse($lease->end_date), false)) : 0;
    @endphp
    
    <div class="lease-header">
        <div class="header-content">
            <div class="header-left">
                <h1 class="lease-title">Lease {{ $lease->lease_number }}</h1>
                <div class="lease-location">
                    <a href="{{ route('units.show', $lease->unit) }}">
                        Unit {{ $lease->unit->unit_number }}
                    </a> 
                    • {{ $lease->unit->building->name }}
                </div>
                <div class="lease-meta">
                    <div class="meta-item">
                        {{ $lease->lease_type ?? 'Standard' }}
                    </div>
                    <div class="meta-item">
                        ₱{{ number_format($lease->monthly_rent, 0) }}/month
                    </div>
                    <div class="meta-item">
                        <span class="status-badge status-{{ $lease->lease_status }}">
                            {{ ucfirst($lease->lease_status) }}
                        </span>
                    </div>
                    @if($lease->lease_status === 'active' && $daysRemaining <= 30 && $daysRemaining > 0)
                        <div class="meta-item">
                            <span class="days-badge days-warning">{{ $daysRemaining }} days left</span>
                        </div>
                    @endif
                    @if($lease->start_date)
                    <div class="meta-item">
                        Since {{ \Carbon\Carbon::parse($lease->start_date)->format('M Y') }}
                    </div>
                    @endif
                </div>
            </div>
            <div class="action-buttons">
                <a href="{{ route('leases.edit', $lease) }}" class="btn">
                    Edit Lease
                </a>
                <a href="{{ route('tenants.show', $lease->tenant) }}" class="btn">
                    View Tenant
                </a>
                <a href="{{ route('units.show', $lease->unit) }}" class="btn">
                    View Unit
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    @php
        // Safely calculate payment totals without causing errors
        $totalPaid = 0;
        $totalDue = 0;
        
        try {
            if ($lease->payments && method_exists($lease, 'payments')) {
                $totalPaid = $lease->payments->where('payment_status', 'completed')->sum('amount_paid') ?? 0;
            }
        } catch (\Exception $e) {
            $totalPaid = 0;
        }
        
        try {
            if (method_exists($lease, 'getTotalMonths')) {
                $totalMonths = $lease->getTotalMonths() ?? 0;
            } else {
                if ($lease->start_date && $lease->end_date) {
                    $start = \Carbon\Carbon::parse($lease->start_date);
                    $end = \Carbon\Carbon::parse($lease->end_date);
                    $totalMonths = $start->diffInMonths($end);
                } else {
                    $totalMonths = 0;
                }
            }
            $totalDue = $lease->monthly_rent * max(0, $totalMonths);
        } catch (\Exception $e) {
            $totalDue = $lease->monthly_rent * 12;
        }
        
        $remainingBalance = max(0, $totalDue - $totalPaid);
    @endphp
    
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-value">₱{{ number_format($lease->monthly_rent, 0) }}</span>
            <span class="stat-label">Monthly Rent</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">
                @php
                    try {
                        echo $lease->getTotalMonths() ?? 0;
                    } catch (\Exception $e) {
                        if ($lease->start_date && $lease->end_date) {
                            echo \Carbon\Carbon::parse($lease->start_date)->diffInMonths(\Carbon\Carbon::parse($lease->end_date));
                        } else {
                            echo '0';
                        }
                    }
                @endphp
            </span>
            <span class="stat-label">Months</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">₱{{ number_format($totalPaid, 0) }}</span>
            <span class="stat-label">Total Paid</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">₱{{ number_format($remainingBalance, 0) }}</span>
            <span class="stat-label">Balance</span>
        </div>
    </div>

    <!-- Tab Interface -->
    <div class="tab-container">
        <div class="tab-header">
            <button class="tab-button active" data-tab="overview">Overview</button>
            <button class="tab-button" data-tab="details">Lease Details</button>
            <button class="tab-button" data-tab="documents">Documents</button>
        </div>
        
        <div class="tab-content">
            <!-- Overview Tab -->
            <div class="tab-pane active" id="overview">
                <div class="overview-grid">
                    <!-- Box 1: Lease Period -->
                    <div class="overview-box">
                        <div class="overview-box-header">Lease Period</div>
                        <div class="overview-box-content">
                            <div class="info-item">
                                <div class="info-label">Start Date</div>
                                <div class="info-value">{{ $lease->start_date ? \Carbon\Carbon::parse($lease->start_date)->format('F d, Y') : 'N/A' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">End Date</div>
                                <div class="info-value">{{ $lease->end_date ? \Carbon\Carbon::parse($lease->end_date)->format('F d, Y') : 'N/A' }}</div>
                            </div>
                            @if($lease->move_in_date)
                            <div class="info-item">
                                <div class="info-label">Move-in Date</div>
                                <div class="info-value">{{ \Carbon\Carbon::parse($lease->move_in_date)->format('F d, Y') }}</div>
                            </div>
                            @endif
                            @if($lease->move_out_date)
                            <div class="info-item">
                                <div class="info-label">Move-out Date</div>
                                <div class="info-value">{{ \Carbon\Carbon::parse($lease->move_out_date)->format('F d, Y') }}</div>
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
                                <div class="info-value" style="font-size: 1.1rem; color: var(--accent-emerald);">₱{{ number_format($lease->monthly_rent, 2) }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Security Deposit</div>
                                <div class="info-value">₱{{ number_format($lease->security_deposit ?? 0, 2) }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Payment Due Day</div>
                                <div class="info-value">Day {{ $lease->payment_due_day ?? 1 }} of each month</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Total Lease Value</div>
                                <div class="info-value">₱{{ number_format($totalDue, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Box 3: Lease Progress -->
                    @if($lease->lease_status === 'active' && $lease->start_date && $lease->end_date)
                        @php
                            try {
                                $start = \Carbon\Carbon::parse($lease->start_date);
                                $end = \Carbon\Carbon::parse($lease->end_date);
                                $totalDays = $start->diffInDays($end);
                                $elapsedDays = $start->diffInDays(now());
                                $progressPercent = $totalDays > 0 ? min(round(($elapsedDays / $totalDays) * 100), 100) : 0;
                            } catch (\Exception $e) {
                                $progressPercent = 0;
                                $start = null;
                                $end = null;
                            }
                        @endphp
                        @if(isset($start) && $start)
                        <div class="overview-box">
                            <div class="overview-box-header">Lease Progress</div>
                            <div class="overview-box-content">
                                <div class="progress-container">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                        <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $start->format('M d, Y') }}</span>
                                        <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $end->format('M d, Y') }}</span>
                                    </div>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar-fill" style="width: {{ $progressPercent }}%;"></div>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; margin-top: 0.5rem;">
                                        <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $progressPercent }}% Complete</span>
                                        @if($daysRemaining <= 30 && $daysRemaining > 0)
                                            <span style="font-size: 0.7rem; color: var(--accent-warning);">⚠️ {{ $daysRemaining }} days left</span>
                                        @elseif($daysRemaining <= 0)
                                            <span style="font-size: 0.7rem; color: var(--accent-red);">⚠️ Expired</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endif
                </div>
                
                <!-- Terms & Conditions -->
                @if($lease->terms)
                <div class="overview-box" style="margin-top: 0;">
                    <div class="overview-box-header">Terms & Conditions</div>
                    <div class="overview-box-content">
                        @php
                            $terms = is_array($lease->terms) ? $lease->terms : json_decode($lease->terms, true);
                        @endphp
                        @if($terms && is_array($terms))
                            <div class="features-list">
                                @foreach($terms as $key => $value)
                                    <span class="feature-tag">
                                        {{ ucfirst(str_replace('_', ' ', $key)) }}: {{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <div class="info-value">{{ $lease->terms }}</div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Utilities Included -->
                @if($lease->utilities_included)
                <div class="overview-box" style="margin-top: 0;">
                    <div class="overview-box-header">Utilities Included</div>
                    <div class="overview-box-content">
                        @php
                            $utilities = is_array($lease->utilities_included) ? $lease->utilities_included : json_decode($lease->utilities_included, true);
                        @endphp
                        @if($utilities && is_array($utilities) && count($utilities) > 0)
                            <div class="features-list">
                                @foreach($utilities as $utility)
                                    <span class="feature-tag">{{ ucfirst(str_replace('_', ' ', $utility)) }}</span>
                                @endforeach
                            </div>
                        @else
                            <div class="info-value">No utilities listed</div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Notes -->
                @if($lease->notes)
                <div class="overview-box" style="margin-top: 0;">
                    <div class="overview-box-header">Notes</div>
                    <div class="overview-box-content">
                        <div class="info-value" style="line-height: 1.6; color: var(--text-muted);">
                            {{ $lease->notes }}
                        </div>
                        <div style="margin-top: 0.75rem; font-size: 0.65rem; color: var(--text-muted); text-align: right;">
                            Last updated: {{ $lease->updated_at ? $lease->updated_at->format('M d, Y h:i A') : 'N/A' }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Lease Details Tab -->
            <div class="tab-pane" id="details">
                <div class="overview-grid">
                    <!-- Box 1: Tenant Information -->
                    <div class="overview-box">
                        <div class="overview-box-header">👤 Tenant Information</div>
                        <div class="overview-box-content">
                            <div class="info-item">
                                <div class="info-label">Full Name</div>
                                <div class="info-value">
                                    <a href="{{ route('tenants.show', $lease->tenant) }}">
                                        {{ $lease->tenant->full_name }}
                                    </a>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Email</div>
                                <div class="info-value">
                                    <a href="mailto:{{ $lease->tenant->email }}">{{ $lease->tenant->email }}</a>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Phone</div>
                                <div class="info-value">
                                    <a href="tel:{{ $lease->tenant->phone }}">{{ $lease->tenant->phone }}</a>
                                </div>
                            </div>
                            @if($lease->tenant->emergency_contact_name)
                            <div class="info-item">
                                <div class="info-label">Emergency Contact</div>
                                <div class="info-value">{{ $lease->tenant->emergency_contact_name }} 
                                    @if($lease->tenant->emergency_contact_phone)
                                    ({{ $lease->tenant->emergency_contact_phone }})
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Box 2: Unit Information -->
                    <div class="overview-box">
                        <div class="overview-box-header">🏠 Unit Information</div>
                        <div class="overview-box-content">
                            <div class="info-item">
                                <div class="info-label">Unit Number</div>
                                <div class="info-value">
                                    <a href="{{ route('units.show', $lease->unit) }}">
                                        Unit {{ $lease->unit->unit_number }}
                                    </a>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Building</div>
                                <div class="info-value">
                                    <a href="{{ route('buildings.show', $lease->unit->building) }}">
                                        {{ $lease->unit->building->name }}
                                    </a>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Address</div>
                                <div class="info-value">{{ $lease->unit->building->address }}, {{ $lease->unit->building->city }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Unit Type</div>
                                <div class="info-value">{{ $lease->unit->unit_type_label ?? ucfirst($lease->unit->unit_type) }}</div>
                            </div>
                            @if($lease->unit->bedrooms || $lease->unit->bathrooms)
                            <div class="info-item">
                                <div class="info-label">Specifications</div>
                                <div class="info-value">{{ $lease->unit->bedrooms ?? 0 }} bed / {{ $lease->unit->bathrooms ?? 0 }} bath</div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Box 3: Lease Information -->
                    <div class="overview-box">
                        <div class="overview-box-header">📋 Lease Information</div>
                        <div class="overview-box-content">
                            <div class="info-item">
                                <div class="info-label">Lease Number</div>
                                <div class="info-value">{{ $lease->lease_number }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Lease Type</div>
                                <div class="info-value">{{ $lease->lease_type ?? 'Standard' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Status</div>
                                <div class="info-value">
                                    <span class="status-badge status-{{ $lease->lease_status }}">
                                        {{ ucfirst($lease->lease_status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Payment Due Day</div>
                                <div class="info-value">Day {{ $lease->payment_due_day ?? 1 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Documents Tab -->
            <div class="tab-pane" id="documents">
                <div class="overview-grid">
                    <!-- Lease Agreement -->
                    @if($lease->lease_agreement_path)
                    <div class="overview-box">
                        <div class="overview-box-header">Lease Agreement</div>
                        <div class="overview-box-content">
                            <div class="document-preview">
                                <div class="document-icon">📄</div>
                                <div class="info-value" style="margin-bottom: 0.75rem;">Lease Agreement Document</div>
                                <a href="{{ Storage::url($lease->lease_agreement_path) }}" target="_blank" class="btn">
                                    View Document
                                </a>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="overview-box">
                        <div class="overview-box-header">Lease Agreement</div>
                        <div class="overview-box-content">
                            <div class="info-value" style="text-align: center; color: var(--text-muted);">
                                No lease agreement uploaded
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
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
</script>
@endpush