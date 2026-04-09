@extends('layouts.app')

@section('content')
<div class="dashboard-wrapper">
    
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="header-left">
            <h1 class="page-title">Buildings</h1>
            <p class="page-subtitle">Jennifer Montil • Property Management Overview</p>
        </div>
        <div class="header-right">
            <a href="{{ route('buildings.create') }}" class="btn-emerald-action">
                <i data-lucide="plus" style="width: 18px; height: 18px; margin-right: 8px;"></i> 
                Add Building
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="building-2" class="stat-icon"></i>
                </div>
                <div class="stat-trend">Total</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $buildings->total() }}</span>
                <span class="stat-label">All Buildings</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="check-circle" class="stat-icon"></i>
                </div>
                <div class="stat-trend">Active</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $buildings->where('status', 'active')->count() }}</span>
                <span class="stat-label">Active Buildings</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="x-circle" class="stat-icon"></i>
                </div>
                <div class="stat-trend">Inactive</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $buildings->where('status', 'inactive')->count() }}</span>
                <span class="stat-label">Inactive Buildings</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="construction" class="stat-icon"></i>
                </div>
                <div class="stat-trend">Renovation</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $buildings->whereIn('status', ['under_construction', 'renovation'])->count() }}</span>
                <span class="stat-label">Under Construction/Renovation</span>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header">
            <form action="{{ route('buildings.index') }}" method="GET" style="display: flex; gap: 1rem; flex-grow: 1;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search buildings..." class="dark-input">
                <select name="building_type" class="dark-filter" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="residential" {{ request('building_type') == 'residential' ? 'selected' : '' }}>Residential</option>
                    <option value="commercial" {{ request('building_type') == 'commercial' ? 'selected' : '' }}>Commercial</option>
                    <option value="mixed" {{ request('building_type') == 'mixed' ? 'selected' : '' }}>Mixed Use</option>
                </select>
                <select name="status" class="dark-filter" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="under_construction" {{ request('status') == 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                    <option value="renovation" {{ request('status') == 'renovation' ? 'selected' : '' }}>Renovation</option>
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
            @if($buildings->count() > 0)
                <div id="gridView" class="buildings-grid">
                    @foreach($buildings as $building)
                        <div class="building-item-card">
                            <div class="building-thumb" style="background-image: linear-gradient(rgba(0,0,0,0), rgba(0,0,0,0.7)), url('{{ $building->photo_url ?? asset('images/building-placeholder.jpg') }}')">
                                <span class="status-badge {{ $building->status }}">
                                    {{ strtoupper(str_replace('_', ' ', $building->status)) }}
                                </span>
                            </div>
                            <div class="building-details">
                                <div class="building-header">
                                    <h3>{{ $building->name }}</h3>
                                    <span class="type-badge {{ $building->building_type ?? $building->type }}">
                                        {{ ucfirst($building->building_type ?? $building->type) }}
                                    </span>
                                </div>
                                <p>{{ $building->address }}, {{ $building->city }}</p>
                                <div class="building-stats">
                                    <span>{{ $building->units_count ?? $building->units()->count() }} Units</span>
                                    <span>{{ $building->active_tenants_count ?? $building->tenants()->whereHas('leases', function($q) {
                                        $q->where('lease_status', 'active');
                                    })->count() }} Tenants</span>
                                </div>
                                <div class="action-footer">
                                    <a href="{{ route('buildings.show', $building) }}" class="icon-link"><i data-lucide="eye" style="width:16px;"></i></a>
                                    <a href="{{ route('buildings.edit', $building) }}" class="icon-link"><i data-lucide="pencil" style="width:16px;"></i></a>
                                    <form action="{{ route('buildings.destroy', $building) }}" method="POST" onsubmit="return confirmDelete(event, this, '{{ $building->name }}')" style="flex: 1;">
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
                                <th>Address</th>
                                <th>Units</th>
                                <th>Tenants</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($buildings as $building)
                                <tr>
                                    <td style="font-weight: 600; color: #fff;">
                                        {{ $building->name }}
                                        <span class="type-pill {{ $building->building_type ?? $building->type }}">
                                            {{ ucfirst($building->building_type ?? $building->type) }}
                                        </span>
                                    </td>
                                    <td style="color: #a0a0a0;">{{ $building->address }}, {{ $building->city }}</td>
                                    <td style="color: #a0a0a0;">{{ $building->units_count ?? $building->units()->count() }}</td>
                                    <td style="color: #a0a0a0;">{{ $building->active_tenants_count ?? $building->tenants()->whereHas('leases', function($q) {
                                        $q->where('lease_status', 'active');
                                    })->count() }}</td>
                                    <td style="text-align: right;">
                                        <div style="display: flex; justify-content: flex-end; gap: 12px;">
                                            <a href="{{ route('buildings.show', $building) }}" style="color: #a0a0a0;"><i data-lucide="eye" style="width:18px;"></i></a>
                                            <a href="{{ route('buildings.edit', $building) }}" style="color: #a0a0a0;"><i data-lucide="pencil" style="width:18px;"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="pagination-wrapper">
                    {{ $buildings->links() }}
                </div>
            @else
                <div class="empty-state">
                    <i data-lucide="building-2" style="width: 48px; height: 48px; margin-bottom: 1rem;"></i>
                    <p>No buildings found.</p>
                    <a href="{{ route('buildings.create') }}" class="btn-emerald-action">Add your first building</a>
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

    /* Add Building Button Style */
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

    /* Grid/Items Styling */
    .buildings-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }
    .building-item-card { background: var(--bg-surface); border-radius: 12px; border: 1px solid var(--border-color); overflow: hidden; transition: 0.3s; position: relative; }
    .building-item-card:hover { border-color: var(--accent-emerald); transform: translateY(-4px); }
    
    .building-thumb { height: 150px; background-size: cover; background-position: center; padding: 1rem; position: relative; }
    
    /* Status Badge - All Caps */
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
    .status-badge.inactive { background: #6c757d; }
    .status-badge.under_construction { background: var(--accent-warning); color: #000; }
    .status-badge.renovation { background: var(--accent-pink); }
    
    .building-details { padding: 1.25rem; }
    
    .building-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 5px;
        gap: 10px;
    }
    .building-header h3 { 
        font-size: 1.1rem; 
        margin: 0; 
        color: #fff; 
        font-weight: 700; 
        flex: 1;
    }
    
    /* Type Badge - Glass morphism */
    .type-badge { 
        font-size: 0.7rem; 
        font-weight: 600; 
        padding: 4px 10px; 
        border-radius: 6px; 
        color: #fff;
        text-transform: capitalize;
        display: inline-block;
        white-space: nowrap;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(4px);
    }
    .type-badge.residential { background: rgba(16, 185, 129, 0.2); border-color: rgba(16, 185, 129, 0.4); color: #10b981; }
    .type-badge.commercial { background: rgba(59, 130, 246, 0.2); border-color: rgba(59, 130, 246, 0.4); color: #60a5fa; }
    .type-badge.mixed { background: rgba(139, 92, 246, 0.2); border-color: rgba(139, 92, 246, 0.4); color: #a78bfa; }
    
    .building-details p { color: var(--text-muted); font-size: 0.85rem; margin: 0 0 12px 0; }
    
    .building-stats { 
        display: flex; 
        gap: 15px; 
        margin-bottom: 15px; 
        font-size: 0.8rem; 
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
        text-transform: capitalize;
    }
    .type-pill.residential { background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
    .type-pill.commercial { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
    .type-pill.mixed { background: rgba(139, 92, 246, 0.2); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.3); }
    
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
    if (confirm(`Are you sure you want to delete "${name}"? This cannot be undone.`)) {
        form.submit();
    }
}
</script>
@endsection