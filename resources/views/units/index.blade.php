@extends('layouts.app')

@section('content')
<div class="dashboard-wrapper" style="background-color: #121212; min-height: 100vh; padding: 2rem; color: #ffffff; font-family: 'Inter', sans-serif;">
    
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #2d2d2d; padding-bottom: 1.5rem; margin-bottom: 2rem;">
        <div class="header-left">
            <h1 class="page-title" style="font-size: 1.75rem; font-weight: 700; margin: 0; color: #fff;">Units</h1>
            <p class="page-subtitle" style="color: #a0a0a0; margin-top: 0.25rem;">Jennifer Montil • Property Management Overview</p>
        </div>
        <div class="header-right">
            <a href="{{ route('units.create') }}" class="btn-emerald-action">
                + Add Unit
            </a>
        </div>
    </div>

    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">🔑</div>
                <div class="stat-trend positive">Total</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $units->total() }}</span>
                <span class="stat-label">Units</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">🚪</div>
                <div class="stat-trend {{ $units->where('status', 'vacant')->count() > 0 ? 'positive' : '' }}">Available</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $units->where('status', 'vacant')->count() }}</span>
                <span class="stat-label">Vacant Units</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">👤</div>
                <div class="stat-trend positive">Active</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $units->where('status', 'occupied')->count() }}</span>
                <span class="stat-label">Occupied Units</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">💰</div>
                <div class="stat-trend positive">Monthly</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">₱{{ number_format($units->sum('monthly_rent') / 1000, 1) }}K</span>
                <span class="stat-label">Total Revenue</span>
            </div>
        </div>
    </div>

    <div class="content-card" style="margin-bottom: 2rem;">
        <div class="card-header" style="padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #2d2d2d;">
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
                <button onclick="toggleView('grid')" id="gridBtn" class="view-btn active">Grid</button>
                <button onclick="toggleView('table')" id="tableBtn" class="view-btn">Table</button>
            </div>
        </div>

        <div class="card-body" style="padding: 1.5rem;">
            @if($units->count() > 0)
                <div id="gridView" class="buildings-grid">
                    @foreach($units as $unit)
                        <div class="building-item-card">
                            <div class="building-thumb" style="background: #252525; display: flex; align-items: center; justify-content: center; position: relative;">
                                @if($unit->photo_url)
                                    <div style="position: absolute; inset: 0; background-image: url('{{ $unit->photo_url }}'); background-size: cover; background-position: center;"></div>
                                @else
                                    <span style="font-size: 3rem; opacity: 0.3;">🏠</span>
                                @endif
                                <span class="type-badge" style="z-index: 1; background: {{ $unit->status === 'vacant' ? '#10b981' : '#ef4444' }};">
                                    {{ strtoupper($unit->status) }}
                                </span>
                            </div>
                            <div class="building-details">
                                <h3 style="display: flex; justify-content: space-between;">
                                    Unit {{ $unit->unit_number }}
                                    <span style="color: #10b981;">₱{{ number_format($unit->monthly_rent) }}</span>
                                </h3>
                                <p>📍 {{ $unit->building->name }}</p>
                                
                                <div style="display: flex; gap: 15px; margin-bottom: 15px; font-size: 0.8rem; color: #a0a0a0;">
                                    <span>🛏️ {{ $unit->bedrooms ?? 0 }} Bed</span>
                                    <span>🚿 {{ $unit->bathrooms ?? 0 }} Bath</span>
                                </div>

                                <div class="action-footer">
                                    <a href="{{ route('units.show', $unit) }}" class="icon-link">👁️ View</a>
                                    <a href="{{ route('units.edit', $unit) }}" class="icon-link">✏️ Edit</a>
                                    <form action="{{ route('units.destroy', $unit) }}" method="POST" onsubmit="return confirmDelete(event, this, 'Unit {{ $unit->unit_number }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="icon-link delete-btn">🗑️</button>
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
                                <th>Rent</th>
                                <th>Status</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($units as $unit)
                                <tr>
                                    <td style="font-weight: 600; color: #fff;">{{ $unit->unit_number }}</td>
                                    <td style="color: #a0a0a0;">{{ $unit->building->name }}</td>
                                    <td style="color: #10b981;">₱{{ number_format($unit->monthly_rent) }}</td>
                                    <td>
                                        <span class="type-pill" style="color: {{ $unit->status === 'vacant' ? '#10b981' : '#ef4444' }};">
                                            {{ strtoupper($unit->status) }}
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="{{ route('units.show', $unit) }}" style="margin-left: 10px; text-decoration: none;">👁️</a>
                                        <a href="{{ route('units.edit', $unit) }}" style="margin-left: 10px; text-decoration: none;">✏️</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align: center; padding: 3rem; color: #a0a0a0;">
                    <p>No units found matching your criteria.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    /* Ported exact styles from Building Index */
    .stat-card { background: #1d1d1d; border: 1px solid #2d2d2d; border-radius: 12px; padding: 1.5rem; height: 140px; display: flex; flex-direction: column; justify-content: space-between; box-sizing: border-box; transition: 0.3s; }
    .stat-card:hover { border-color: #10b981; transform: translateY(-3px); }
    .stat-header { display: flex; justify-content: space-between; align-items: flex-start; }
    .stat-icon-wrapper { font-size: 1.5rem; background: rgba(16, 185, 129, 0.1); padding: 0.5rem; border-radius: 8px; line-height: 1; }
    .stat-trend { font-size: 0.7rem; color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 2px 8px; border-radius: 10px; font-weight: 700; text-transform: uppercase; }
    .stat-value { display: block; font-size: 1.8rem; font-weight: 700; color: #fff; line-height: 1.1; }
    .stat-label { color: #a0a0a0; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 1px; margin-top: 4px; display: block; }

    .content-card { background: #1d1d1d; border: 1px solid #2d2d2d; border-radius: 12px; overflow: hidden; }
    .dark-filter, .dark-input { background: #181818; border: 1px solid #2d2d2d; color: #fff; padding: 8px 15px; border-radius: 8px; outline: none; }
    .btn-emerald-action { background: #10b981; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 700; transition: 0.2s; border: none; }
    
    .buildings-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
    .building-item-card { background: #181818; border-radius: 12px; border: 1px solid #2d2d2d; overflow: hidden; transition: 0.3s; }
    .building-item-card:hover { border-color: #10b981; transform: translateY(-4px); }
    .building-thumb { height: 150px; background-size: cover; background-position: center; padding: 1rem; }
    .type-badge { color: #fff; font-size: 0.65rem; font-weight: 800; padding: 4px 8px; border-radius: 4px; }
    .building-details { padding: 1.25rem; }
    .building-details h3 { font-size: 1.1rem; margin: 0; color: #fff; font-weight: 700; }
    .building-details p { color: #a0a0a0; font-size: 0.85rem; margin: 5px 0 15px 0; }
    
    .action-footer { display: flex; gap: 10px; border-top: 1px solid #2d2d2d; padding-top: 1rem; }
    .icon-link { background: #2d2d2d; border: none; padding: 8px; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 0.8rem; color: #fff; flex: 1; text-align: center; }
    .delete-btn:hover { background: #ef4444; color: white; }

    .view-switcher { background: #181818; padding: 4px; border-radius: 8px; border: 1px solid #2d2d2d; display: flex; }
    .view-btn { background: transparent; border: none; color: #a0a0a0; padding: 5px 15px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: 600; }
    .view-btn.active { background: #10b981; color: white; }

    .dark-table { width: 100%; border-collapse: collapse; }
    .dark-table th { text-align: left; color: #a0a0a0; font-size: 0.75rem; text-transform: uppercase; padding: 15px; border-bottom: 1px solid #2d2d2d; }
    .dark-table td { padding: 15px; border-bottom: 1px solid #2d2d2d; font-size: 0.9rem; }
    .type-pill { background: #2d2d2d; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; }
    .hidden { display: none; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
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