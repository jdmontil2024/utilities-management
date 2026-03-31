@extends('layouts.app')

@section('content')
<div class="dashboard-wrapper">
    
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="header-left">
            <h1 class="page-title">Lease Agreements</h1>
            <p class="page-subtitle">Jennifer Montil • Resident Community Management</p>
        </div>
        <div class="header-right">
            <a href="{{ route('leases.create') }}" class="btn-emerald-action">
                <i data-lucide="file-plus" style="width: 18px; height: 18px; margin-right: 8px;"></i> 
                New Lease
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="file-text" class="stat-icon"></i>
                </div>
                <div class="stat-trend">Total</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $leases->total() }}</span>
                <span class="stat-label">Total Contracts</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="check-circle" class="stat-icon"></i>
                </div>
                <div class="stat-trend">Current</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $leases->where('lease_status', 'active')->count() }}</span>
                <span class="stat-label">Active Leases</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper" style="background: rgba(245, 158, 11, 0.1);">
                    <i data-lucide="clock" class="stat-icon" style="color: #f59e0b;"></i>
                </div>
                <div class="stat-trend warning">Attention</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $expiringSoonCount ?? 0 }}</span>
                <span class="stat-label">Expiring Soon</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="landmark" class="stat-icon"></i>
                </div>
                <div class="stat-trend">Monthly</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">₱{{ number_format(($totalMonthlyRent ?? $leases->sum('monthly_rent')) / 1000, 1) }}K</span>
                <span class="stat-label">Projected Revenue</span>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header">
            <form action="{{ route('leases.index') }}" method="GET" style="display: flex; gap: 1rem; flex-grow: 1;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Lease # or Tenant..." class="dark-input">
                <select name="building_id" class="dark-filter" onchange="this.form.submit()">
                    <option value="">All Buildings</option>
                    @foreach($buildings as $building)
                        <option value="{{ $building->id }}" {{ request('building_id') == $building->id ? 'selected' : '' }}>
                            {{ $building->name }}
                        </option>
                    @endforeach
                </select>
            </form>
            <div class="view-switcher">
                <button onclick="toggleView('grid')" id="gridBtn" class="view-btn active">
                    <i data-lucide="layout-grid" style="width: 16px;"></i>
                </button>
                <button onclick="toggleView('list')" id="tableBtn" class="view-btn">
                    <i data-lucide="list" style="width: 16px;"></i>
                </button>
            </div>
        </div>

        <div class="card-body indented-body">
            @if($leases->count() > 0)
                <div id="gridView" class="leases-grid">
                    @foreach($leases as $lease)
                        <div class="lease-item-card">
                            <div class="lease-thumb">
                                <div class="unit-badge-circle">
                                    {{ $lease->unit->unit_number ?? '?' }}
                                </div>
                                <span class="status-badge {{ $lease->lease_status }}">
                                    {{ strtoupper($lease->lease_status) }}
                                </span>
                            </div>
                            <div class="lease-details">
                                <h3 style="display: flex; justify-content: space-between; align-items: center;">
                                    {{ $lease->lease_number }}
                                    <span style="color: var(--accent-emerald); font-size: 0.95rem;">₱{{ number_format($lease->monthly_rent) }}</span>
                                </h3>
                                <p style="color: #fff; font-weight: 600; margin-top: 4px;">
                                    <i data-lucide="user" style="width:14px; vertical-align: middle; margin-right: 4px; color: var(--text-muted);"></i>
                                    {{ $lease->tenant->full_name ?? 'No Tenant' }}
                                </p>
                                
                                <div class="lease-info-stack">
                                    <span><i data-lucide="building-2"></i> {{ $lease->unit->building->name ?? 'N/A' }}</span>
                                    <span><i data-lucide="calendar"></i> {{ \Carbon\Carbon::parse($lease->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($lease->end_date)->format('M d, Y') }}</span>
                                </div>

                                <div class="action-footer">
                                    <a href="{{ route('leases.show', $lease) }}" class="icon-link"><i data-lucide="eye" style="width:16px;"></i></a>
                                    <a href="{{ route('leases.edit', $lease) }}" class="icon-link"><i data-lucide="pencil" style="width:16px;"></i></a>
                                    <form action="{{ route('leases.destroy', $lease) }}" method="POST" onsubmit="return confirmDelete(event, this, '{{ $lease->lease_number }}')" style="flex: 1;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="icon-link delete-btn" style="width: 100%;"><i data-lucide="trash-2" style="width:16px;"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div id="tableView" class="table-responsive hidden">
                    <table class="dark-table">
                        <thead>
                            <tr>
                                <th>Lease Details</th>
                                <th>Unit & Building</th>
                                <th>Rent</th>
                                <th>Status</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leases as $lease)
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: #fff;">{{ $lease->lease_number }}</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $lease->tenant->full_name ?? 'N/A' }}</div>
                                    </td>
                                    <td style="color: var(--text-muted);">
                                        {{ $lease->unit->building->name ?? 'N/A' }}
                                        <span style="display:block; font-size: 0.75rem;">Unit {{ $lease->unit->unit_number ?? '-' }}</span>
                                    </td>
                                    <td style="color: var(--accent-emerald); font-weight: 600;">₱{{ number_format($lease->monthly_rent) }}</td>
                                    <td>
                                        <span class="status-pill {{ $lease->lease_status }}">
                                            {{ strtoupper($lease->lease_status) }}
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: flex; justify-content: flex-end; gap: 12px;">
                                            <a href="{{ route('leases.show', $lease) }}" style="color: var(--text-muted);"><i data-lucide="eye" style="width:18px;"></i></a>
                                            <a href="{{ route('leases.edit', $lease) }}" style="color: var(--text-muted);"><i data-lucide="pencil" style="width:18px;"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
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
    }

    .dashboard-wrapper { background-color: var(--bg-deep); min-height: 100vh; padding: 2rem; color: var(--text-main); font-family: 'Inter', sans-serif; }
    .page-header { border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem; margin-bottom: 2rem; }
    .page-title { font-size: 1.75rem; font-weight: 700; margin: 0; color: #fff; }
    .page-subtitle { color: var(--text-muted); margin-top: 0.25rem; }

    /* STAT CARDS - SYSTEM STANDARD */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    .stat-card { 
        background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; 
        padding: 1.5rem; height: 160px; display: flex; flex-direction: column; box-sizing: border-box; 
    }
    .stat-header { display: flex; justify-content: space-between; align-items: flex-start; }
    .stat-icon-wrapper { 
        background: rgba(16, 185, 129, 0.1); width: 38px; height: 38px; border-radius: 8px; 
        display: flex; align-items: center; justify-content: center; 
    }
    .stat-icon { width: 20px; height: 20px; color: var(--accent-emerald); stroke-width: 2px; }
    .stat-body { margin-top: 1.25rem; } 
    .stat-value { display: block; font-size: 1.8rem; font-weight: 700; color: #fff; line-height: 1; }
    .stat-label { color: var(--text-muted); text-transform: uppercase; font-size: 0.65rem; letter-spacing: 1px; margin-top: 6px; display: block; }
    .stat-trend { font-size: 0.7rem; color: var(--accent-emerald); background: rgba(16, 185, 129, 0.1); padding: 2px 8px; border-radius: 10px; font-weight: 700; }
    .stat-trend.warning { color: var(--accent-warning); background: rgba(245, 158, 11, 0.1); }

    /* Content Card & Indentation */
    .content-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; }
    .card-header { padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); }
    .indented-body { padding: 1.5rem 1.5rem 1.5rem 2.5rem; }

    .btn-emerald-action { 
        background: var(--accent-emerald); color: white; padding: 10px 20px; border-radius: 8px; 
        text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; transition: 0.2s; 
    }

    /* Grid Items */
    .leases-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
    .lease-item-card { background: var(--bg-surface); border-radius: 12px; border: 1px solid var(--border-color); overflow: hidden; transition: 0.3s; }
    .lease-item-card:hover { border-color: var(--accent-emerald); transform: translateY(-4px); }
    
    .lease-thumb { 
        height: 120px; background: #222; display: flex; flex-direction: column; 
        align-items: center; justify-content: center; position: relative; 
    }
    .unit-badge-circle { 
        width: 60px; height: 60px; background: rgba(16, 185, 129, 0.05); 
        border: 2px dashed var(--border-color); border-radius: 50%; 
        display: flex; align-items: center; justify-content: center; 
        font-size: 1.1rem; color: #fff; font-weight: 700; 
    }
    
    .status-badge { font-size: 0.6rem; font-weight: 800; padding: 3px 8px; border-radius: 4px; color: #fff; margin-top: 10px; }
    .status-badge.active { background: var(--accent-emerald); }
    .status-badge.expired, .status-badge.inactive { background: var(--accent-red); }
    
    .lease-details { padding: 1.25rem; }
    .lease-details h3 { font-size: 1.05rem; margin: 0; color: #fff; font-weight: 700; }
    .lease-details p { color: var(--text-muted); font-size: 0.85rem; margin: 5px 0 12px 0; }
    
    .lease-info-stack { display: flex; flex-direction: column; gap: 6px; margin-bottom: 15px; font-size: 0.8rem; color: var(--text-muted); }
    .lease-info-stack span { display: flex; align-items: center; }
    .lease-info-stack i { width: 14px; height: 14px; margin-right: 8px; opacity: 0.7; }

    .action-footer { display: flex; gap: 8px; border-top: 1px solid var(--border-color); padding-top: 1rem; }
    .icon-link { background: #262626; border: none; color: #fff; padding: 8px; border-radius: 6px; cursor: pointer; text-decoration: none; flex: 1; display: flex; justify-content: center; align-items: center; transition: 0.2s; }
    .icon-link:hover { background: var(--accent-emerald); }
    .delete-btn:hover { background: var(--accent-red); }

    .dark-filter, .dark-input { background: var(--bg-surface); border: 1px solid var(--border-color); color: #fff; padding: 8px 15px; border-radius: 8px; outline: none; }
    .view-switcher { background: var(--bg-surface); padding: 4px; border-radius: 8px; border: 1px solid var(--border-color); display: flex; }
    .view-btn { background: transparent; border: none; color: var(--text-muted); padding: 6px 12px; border-radius: 6px; cursor: pointer; }
    .view-btn.active { background: var(--accent-emerald); color: white; }

    /* Table Styles */
    .dark-table { width: 100%; border-collapse: collapse; }
    .dark-table th { text-align: left; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; padding: 15px; border-bottom: 1px solid var(--border-color); }
    .dark-table td { padding: 15px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
    .status-pill { padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; background: #2d2d2d; }
    .status-pill.active { color: var(--accent-emerald); }
    .status-pill.expired, .status-pill.inactive { color: var(--accent-red); }
    .hidden { display: none; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.lucide) { lucide.createIcons(); }

    window.toggleView = function(view) {
        const grid = document.getElementById('gridView');
        const table = document.getElementById('tableView');
        const gBtn = document.getElementById('gridBtn');
        const tBtn = document.getElementById('tableBtn');

        if(view === 'grid') {
            grid.classList.remove('hidden');
            table.classList.add('hidden');
            gBtn.classList.add('active');
            tBtn.classList.remove('active');
        } else {
            grid.classList.add('hidden');
            table.classList.remove('hidden');
            gBtn.classList.remove('active');
            tBtn.classList.add('active');
        }
        lucide.createIcons();
    };
});

function confirmDelete(event, form, name) {
    event.preventDefault();
    if (confirm(`Are you sure you want to delete lease "${name}"? This cannot be undone.`)) {
        form.submit();
    }
}
</script>
@endsection