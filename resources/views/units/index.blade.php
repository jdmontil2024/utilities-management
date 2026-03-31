@extends('layouts.app')

@section('content')
<div class="dashboard-wrapper">
    
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="header-left">
            <h1 class="page-title">Units</h1>
            <p class="page-subtitle">Jennifer Montil • Property Management Overview</p>
        </div>
        <div class="header-right">
            <a href="{{ route('units.create') }}" class="btn-emerald-action">
                <i data-lucide="plus" style="width: 18px; height: 18px; margin-right: 8px;"></i> 
                Add Unit
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="key" class="stat-icon"></i>
                </div>
                <div class="stat-trend">Total</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $units->total() }}</span>
                <span class="stat-label">Units</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="door-open" class="stat-icon"></i>
                </div>
                <div class="stat-trend">Available</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $units->where('status', 'vacant')->count() }}</span>
                <span class="stat-label">Vacant Units</span>
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
                <span class="stat-value">{{ $units->where('status', 'occupied')->count() }}</span>
                <span class="stat-label">Occupied Units</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="banknote" class="stat-icon"></i>
                </div>
                <div class="stat-trend">Monthly</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">₱{{ number_format($units->sum('monthly_rent') / 1000, 1) }}K</span>
                <span class="stat-label">Total Revenue</span>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header">
            <form action="{{ route('units.index') }}" method="GET" style="display: flex; gap: 1rem; flex-grow: 1;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search units..." class="dark-input">
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
            @if($units->count() > 0)
                <div id="gridView" class="units-grid">
                    @foreach($units as $unit)
                        <div class="unit-item-card">
                            <div class="unit-thumb" style="background-image: linear-gradient(rgba(0,0,0,0), rgba(0,0,0,0.7)), url('{{ $unit->photo_url ?? asset('images/unit-placeholder.jpg') }}')">
                                <span class="status-badge {{ $unit->status }}">
                                    {{ strtoupper($unit->status) }}
                                </span>
                            </div>
                            <div class="unit-details">
                                <h3 style="display: flex; justify-content: space-between; align-items: center;">
                                    Unit {{ $unit->unit_number }}
                                    <span style="color: var(--accent-emerald); font-size: 1rem;">₱{{ number_format($unit->monthly_rent) }}</span>
                                </h3>
                                <p>📍 {{ $unit->building->name }}</p>
                                
                                <div class="unit-specs">
                                    <span><i data-lucide="bed" style="width:14px; margin-right:4px;"></i> {{ $unit->bedrooms ?? 0 }} Bed</span>
                                    <span><i data-lucide="bath" style="width:14px; margin-right:4px;"></i> {{ $unit->bathrooms ?? 0 }} Bath</span>
                                </div>

                                <div class="action-footer">
                                    <a href="{{ route('units.show', $unit) }}" class="icon-link"><i data-lucide="eye" style="width:16px;"></i></a>
                                    <a href="{{ route('units.edit', $unit) }}" class="icon-link"><i data-lucide="pencil" style="width:16px;"></i></a>
                                    <form action="{{ route('units.destroy', $unit) }}" method="POST" onsubmit="return confirmDelete(event, this, 'Unit {{ $unit->unit_number }}')" style="flex: 1;">
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
                                <th>Unit #</th>
                                <th>Building</th>
                                <th>Monthly Rent</th>
                                <th>Status</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($units as $unit)
                                <tr>
                                    <td style="font-weight: 600; color: #fff;">{{ $unit->unit_number }}</td>
                                    <td style="color: var(--text-muted);">{{ $unit->building->name }}</td>
                                    <td style="color: var(--accent-emerald); font-weight: 600;">₱{{ number_format($unit->monthly_rent) }}</td>
                                    <td>
                                        <span class="status-pill {{ $unit->status }}">
                                            {{ strtoupper($unit->status) }}
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: flex; justify-content: flex-end; gap: 12px;">
                                            <a href="{{ route('units.show', $unit) }}" style="color: var(--text-muted);"><i data-lucide="eye" style="width:18px;"></i></a>
                                            <a href="{{ route('units.edit', $unit) }}" style="color: var(--text-muted);"><i data-lucide="pencil" style="width:18px;"></i></a>
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
    }

    .dashboard-wrapper { background-color: var(--bg-deep); min-height: 100vh; padding: 2rem; color: var(--text-main); font-family: 'Inter', sans-serif; }
    .page-header { border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem; margin-bottom: 2rem; }
    .page-title { font-size: 1.75rem; font-weight: 700; margin: 0; color: #fff; }
    .page-subtitle { color: var(--text-muted); margin-top: 0.25rem; }

    /* EXACT MATCH STAT CARDS */
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

    /* Content Styling & Indentation */
    .content-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; }
    .card-header { padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); }
    .indented-body { padding: 1.5rem 1.5rem 1.5rem 2.5rem; }

    /* Action Buttons */
    .btn-emerald-action { 
        background: var(--accent-emerald); color: white; padding: 10px 20px; border-radius: 8px; 
        text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; transition: 0.2s; 
    }

    /* Grid Items */
    .units-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
    .unit-item-card { background: var(--bg-surface); border-radius: 12px; border: 1px solid var(--border-color); overflow: hidden; transition: 0.3s; }
    .unit-item-card:hover { border-color: var(--accent-emerald); transform: translateY(-4px); }
    .unit-thumb { height: 150px; background-size: cover; background-position: center; padding: 1rem; position: relative; }
    .status-badge { font-size: 0.65rem; font-weight: 800; padding: 4px 8px; border-radius: 4px; color: #fff; }
    .status-badge.vacant { background: var(--accent-emerald); }
    .status-badge.occupied { background: var(--accent-red); }
    
    .unit-details { padding: 1.25rem; }
    .unit-details h3 { font-size: 1.1rem; margin: 0; color: #fff; font-weight: 700; }
    .unit-details p { color: var(--text-muted); font-size: 0.85rem; margin: 5px 0 10px 0; }
    
    .unit-specs { display: flex; gap: 12px; margin-bottom: 15px; font-size: 0.8rem; color: var(--text-muted); }
    .unit-specs span { display: flex; align-items: center; }

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
    .status-pill { padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; background: #2d2d2d; }
    .status-pill.vacant { color: var(--accent-emerald); }
    .status-pill.occupied { color: var(--accent-red); }
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