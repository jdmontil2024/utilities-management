@extends('layouts.app')

@section('content')
<div class="dashboard-wrapper">
    
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="header-left">
            <h1 class="page-title">Tenants</h1>
            <p class="page-subtitle">Jennifer Montil • Resident Community Management</p>
        </div>
        <div class="header-right">
            <a href="{{ route('tenants.create') }}" class="btn-emerald-action">
                <i data-lucide="user-plus" style="width: 18px; height: 18px; margin-right: 8px;"></i> 
                Add Tenant
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="users" class="stat-icon"></i>
                </div>
                <div class="stat-trend">Total</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $tenants->total() }}</span>
                <span class="stat-label">Registered Tenants</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="user-check" class="stat-icon"></i>
                </div>
                <div class="stat-trend">Active</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $activeCount ?? $tenants->where('lease_status', 'active')->count() }}</span>
                <span class="stat-label">Active Leases</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper" style="background: rgba(245, 158, 11, 0.1);">
                    <i data-lucide="alert-circle" class="stat-icon" style="color: #f59e0b;"></i>
                </div>
                <div class="stat-trend warning">Alerts</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $expiringSoonCount ?? 0 }}</span>
                <span class="stat-label">Expiring Soon</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="trending-up" class="stat-icon"></i>
                </div>
                <div class="stat-trend">Monthly</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">₱{{ number_format(($totalMonthlyRent ?? $tenants->sum('monthly_rent')) / 1000, 1) }}K</span>
                <span class="stat-label">Rental Revenue</span>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header">
            <form action="{{ route('tenants.index') }}" method="GET" style="display: flex; gap: 1rem; flex-grow: 1;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email..." class="dark-input">
                <select name="building_id" class="dark-filter" onchange="this.form.submit()">
                    <option value="">All Buildings</option>
                    @foreach($buildings as $building)
                        <option value="{{ $building->id }}" {{ request('building_id') == $building->id ? 'selected' : '' }}>
                            {{ $building->name }}
                        </option>
                    @endforeach
                </select>
                <select name="lease_status" class="dark-filter" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active" {{ request('lease_status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ request('lease_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="expired" {{ request('lease_status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="terminated" {{ request('lease_status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
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
            @if($tenants->count() > 0)
                <div id="gridView" class="tenants-grid">
                    @foreach($tenants as $tenant)
                        <div class="tenant-item-card">
                            <div class="tenant-thumb" style="background-image: linear-gradient(rgba(0,0,0,0), rgba(0,0,0,0.7)), url('{{ $tenant->photo_url ?? asset('images/tenant-placeholder.jpg') }}')">
                                <span class="status-badge {{ $tenant->lease_status }}">
                                    {{ strtoupper($tenant->lease_status) }}
                                </span>
                            </div>
                            <div class="tenant-details">
                                <div class="tenant-header">
                                    <h3>{{ $tenant->full_name }}</h3>
                                    <span class="type-badge">
                                        {{ $tenant->unit->unit_number ?? 'No Unit' }}
                                    </span>
                                </div>
                                <p>{{ $tenant->building->name ?? 'No Building' }}</p>
                                
                                <div class="tenant-contact">
                                    <span>{{ $tenant->phone ?? 'N/A' }}</span>
                                </div>

                                <div class="action-footer">
                                    <a href="{{ route('tenants.show', $tenant) }}" class="icon-link"><i data-lucide="eye" style="width:16px;"></i></a>
                                    <a href="{{ route('tenants.edit', $tenant) }}" class="icon-link"><i data-lucide="pencil" style="width:16px;"></i></a>
                                    <form action="{{ route('tenants.destroy', $tenant) }}" method="POST" onsubmit="return confirmDelete(event, this, '{{ $tenant->full_name }}')" style="flex: 1;">
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
                                <th>Name</th>
                                <th>Building</th>
                                <th>Unit</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tenants as $tenant)
                                <tr>
                                    <td style="font-weight: 600; color: #fff;">
                                        {{ $tenant->full_name }}
                                        <span class="type-pill">
                                            {{ $tenant->unit->unit_number ?? 'No Unit' }}
                                        </span>
                                    </td>
                                    <td style="color: var(--text-muted);">{{ $tenant->building->name ?? 'N/A' }}</td>
                                    <td style="color: var(--text-muted);">{{ $tenant->unit->unit_number ?? '-' }}</td>
                                    <td style="color: var(--text-muted);">{{ $tenant->phone ?? 'N/A' }}</td>
                                    <td>
                                        <span class="status-pill {{ $tenant->lease_status }}">
                                            {{ strtoupper($tenant->lease_status) }}
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: flex; justify-content: flex-end; gap: 12px;">
                                            <a href="{{ route('tenants.show', $tenant) }}" style="color: var(--text-muted);"><i data-lucide="eye" style="width:18px;"></i></a>
                                            <a href="{{ route('tenants.edit', $tenant) }}" style="color: var(--text-muted);"><i data-lucide="pencil" style="width:18px;"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="pagination-wrapper">
                    {{ $tenants->links() }}
                </div>
            @else
                <div class="empty-state">
                    <i data-lucide="users" style="width: 48px; height: 48px; margin-bottom: 1rem;"></i>
                    <p>No tenants found.</p>
                    <a href="{{ route('tenants.create') }}" class="btn-emerald-action">Add your first tenant</a>
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
        --accent-blue: #3b82f6;
        --accent-purple: #8b5cf6;
        --accent-pink: #ec4899;
    }

    .dashboard-wrapper { background-color: var(--bg-deep); min-height: 100vh; padding: 2rem; color: var(--text-main); font-family: 'Inter', sans-serif; }
    
    .page-header { border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem; margin-bottom: 2rem; }
    .page-title { font-size: 1.75rem; font-weight: 700; margin: 0; color: #fff; }
    .page-subtitle { color: var(--text-muted); margin-top: 0.25rem; }

    /* STAT CARDS WITH PULSATING LINE */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    
    .stat-card { 
        background: var(--bg-card); 
        border: 1px solid var(--border-color); 
        border-radius: 12px; 
        padding: 1.5rem; 
        height: 160px;
        display: flex; 
        flex-direction: column; 
        box-sizing: border-box;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    /* Pulsating horizontal line at the top */
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
        0% {
            opacity: 0;
            left: -100%;
        }
        50% {
            opacity: 1;
            left: 0%;
        }
        100% {
            opacity: 0;
            left: 100%;
        }
    }

    .stat-card:hover {
        border-color: var(--accent-emerald);
        transform: translateY(-3px);
    }

    .stat-header { display: flex; justify-content: space-between; align-items: flex-start; }

    .stat-icon-wrapper { 
        background: rgba(16, 185, 129, 0.1); 
        width: 38px;
        height: 38px;
        border-radius: 8px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
    }

    .stat-icon { width: 20px; height: 20px; color: var(--accent-emerald); stroke-width: 2px; }

    .stat-body { margin-top: 1.25rem; } 
    
    .stat-value { display: block; font-size: 1.8rem; font-weight: 700; color: #fff; line-height: 1; }
    
    .stat-label { 
        color: var(--text-muted); 
        text-transform: uppercase; 
        font-size: 0.65rem; 
        letter-spacing: 1px; 
        margin-top: 6px; 
        display: block; 
    }

    .stat-trend { 
        font-size: 0.7rem; 
        color: var(--accent-emerald); 
        background: rgba(16, 185, 129, 0.1); 
        padding: 2px 8px; 
        border-radius: 10px; 
        font-weight: 700; 
    }

    /* Content Styling & Indentation */
    .content-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; }
    .card-header { padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); }
    .indented-body { padding: 1.5rem 1.5rem 1.5rem 2.5rem; }

    /* Add Button Style */
    .btn-emerald-action { 
        background: var(--accent-emerald); 
        color: white; 
        padding: 10px 20px; 
        border-radius: 8px; 
        text-decoration: none; 
        font-weight: 600; 
        display: inline-flex; 
        align-items: center; 
        gap: 8px;
        width: fit-content; 
        transition: 0.2s; 
        font-size: 0.9rem;
    }
    .btn-emerald-action:hover { background: #0d9488; }

    /* Grid Items */
    .tenants-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }
    .tenant-item-card { background: var(--bg-surface); border-radius: 12px; border: 1px solid var(--border-color); overflow: hidden; transition: 0.3s; position: relative; }
    .tenant-item-card:hover { border-color: var(--accent-emerald); transform: translateY(-4px); }
    
    .tenant-thumb { height: 150px; background-size: cover; background-position: center; padding: 1rem; position: relative; }
    
    /* Status Badge - Top Left */
    .status-badge { 
        position: absolute;
        top: 10px;
        left: 10px;
        font-size: 0.7rem; 
        font-weight: 600; 
        padding: 4px 10px; 
        border-radius: 6px; 
        color: #fff;
        letter-spacing: 0.3px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        z-index: 10;
        text-transform: uppercase;
    }
    .status-badge.active { background: var(--accent-emerald); }
    .status-badge.pending { background: var(--accent-warning); color: #000; }
    .status-badge.expired, .status-badge.terminated { background: var(--accent-red); }
    .status-badge.inactive { background: #6c757d; }
    
    .tenant-details { padding: 1.25rem; }
    
    .tenant-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 5px;
        gap: 10px;
    }
    .tenant-header h3 { 
        font-size: 1.1rem; 
        margin: 0; 
        color: #fff; 
        font-weight: 700; 
        flex: 1;
    }
    
    /* Type Badge (Unit Number) - Glass morphism */
    .type-badge { 
        font-size: 0.7rem; 
        font-weight: 600; 
        padding: 4px 10px; 
        border-radius: 6px; 
        color: #fff;
        text-transform: uppercase;
        display: inline-block;
        white-space: nowrap;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(4px);
    }
    
    .tenant-details p { color: var(--text-muted); font-size: 0.85rem; margin: 0 0 12px 0; }
    
    .tenant-contact { 
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 15px;
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .action-footer { display: flex; gap: 8px; border-top: 1px solid var(--border-color); padding-top: 1rem; }
    .icon-link { 
        background: #262626; 
        border: none; 
        color: #fff; 
        padding: 8px; 
        border-radius: 6px; 
        cursor: pointer; 
        text-decoration: none; 
        flex: 1; 
        display: flex; 
        justify-content: center; 
        align-items: center; 
        transition: 0.2s; 
    }
    .icon-link:hover { background: var(--accent-emerald); }
    .delete-btn:hover { background: var(--accent-red); }

    .dark-filter, .dark-input { background: var(--bg-surface); border: 1px solid var(--border-color); color: #fff; padding: 8px 15px; border-radius: 8px; outline: none; }
    .view-switcher { background: var(--bg-surface); padding: 4px; border-radius: 8px; border: 1px solid var(--border-color); display: flex; gap: 4px; }
    .view-btn { background: transparent; border: none; color: var(--text-muted); padding: 6px 12px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 4px; }
    .view-btn.active { background: var(--accent-emerald); color: white; }

    /* Table Styles */
    .dark-table { width: 100%; border-collapse: collapse; }
    .dark-table th { text-align: left; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; padding: 15px; border-bottom: 1px solid var(--border-color); }
    .dark-table td { padding: 15px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
    
    .type-pill { 
        display: inline-block;
        margin-left: 8px;
        padding: 2px 8px; 
        border-radius: 4px; 
        font-size: 0.65rem; 
        font-weight: 600; 
        text-transform: uppercase;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #fff;
    }
    
    .status-pill { padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; display: inline-block; text-transform: uppercase; }
    .status-pill.active { background: var(--accent-emerald); color: white; }
    .status-pill.pending { background: var(--accent-warning); color: #000; }
    .status-pill.expired, .status-pill.terminated { background: var(--accent-red); color: white; }
    .status-pill.inactive { background: #6c757d; color: white; }
    
    .pagination-wrapper { margin-top: 2rem; display: flex; justify-content: center; }
    .empty-state { text-align: center; padding: 4rem; color: var(--text-muted); }
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
    if (confirm(`Are you sure you want to delete tenant "${name}"? This cannot be undone.`)) {
        form.submit();
    }
}
</script>
@endsection