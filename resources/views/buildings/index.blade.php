@extends('layouts.app')

@section('content')
<div class="dashboard-wrapper" style="background-color: #121212; min-height: 100vh; padding: 2rem; color: #ffffff; font-family: 'Inter', sans-serif;">
    
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #2d2d2d; padding-bottom: 1.5rem; margin-bottom: 2rem;">
        <div class="header-left">
            <h1 class="page-title" style="font-size: 1.75rem; font-weight: 700; margin: 0; color: #fff;">Buildings</h1>
            <p class="page-subtitle" style="color: #a0a0a0; margin-top: 0.25rem;">Jennifer Montil • Property Management Overview</p>
        </div>
        <div class="header-right">
            <a href="{{ route('buildings.create') }}" class="btn-emerald-action">
                + Add Building
            </a>
        </div>
    </div>

    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">🏢</div>
                <div class="stat-trend positive">Total</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $buildings->total() }}</span>
                <span class="stat-label">Buildings</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">🏠</div>
                <div class="stat-trend positive">Current</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">94%</span>
                <span class="stat-label">Occupancy Rate</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">👥</div>
                <div class="stat-trend positive">Active</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">142</span>
                <span class="stat-label">Tenants</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">💰</div>
                <div class="stat-trend positive">↑ 23%</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">₱245.8K</span>
                <span class="stat-label">Est. Revenue</span>
            </div>
        </div>
    </div>

    <div class="content-card" style="margin-bottom: 2rem;">
        <div class="card-header" style="padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #2d2d2d;">
            <form action="{{ route('buildings.index') }}" method="GET" style="display: flex; gap: 1rem; flex-grow: 1;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search buildings..." class="dark-input">
                <select name="type" class="dark-filter" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="residential">Residential</option>
                    <option value="commercial">Commercial</option>
                </select>
            </form>
            <div class="view-switcher">
                <button onclick="toggleView('grid')" id="gridBtn" class="view-btn active">Grid</button>
                <button onclick="toggleView('table')" id="tableBtn" class="view-btn">Table</button>
            </div>
        </div>

        <div class="card-body" style="padding: 1.5rem;">
            @if($buildings->count() > 0)
                <div id="gridView" class="buildings-grid">
                    @foreach($buildings as $building)
                        <div class="building-item-card">
                            <div class="building-thumb" style="background-image: url('{{ $building->photo_url ?? asset('images/placeholder.jpg') }}')">
                                <span class="type-badge">{{ strtoupper($building->type) }}</span>
                            </div>
                            <div class="building-details">
                                <h3>{{ $building->name }}</h3>
                                <p>📍 {{ $building->address }}</p>
                                <div class="action-footer">
                                    <a href="{{ route('buildings.show', $building) }}" class="icon-link">👁️</a>
                                    <a href="{{ route('buildings.edit', $building) }}" class="icon-link">✏️</a>
                                    <form action="{{ route('buildings.destroy', $building) }}" method="POST" onsubmit="return confirmDelete(event, this, '{{ $building->name }}')">
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
                                <th>Name</th>
                                <th>Address</th>
                                <th>Type</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($buildings as $building)
                                <tr>
                                    <td style="font-weight: 600; color: #fff;">{{ $building->name }}</td>
                                    <td style="color: #a0a0a0;">{{ $building->address }}</td>
                                    <td><span class="type-pill">{{ $building->type }}</span></td>
                                    <td style="text-align: right;">
                                        <a href="{{ route('buildings.show', $building) }}" style="margin-left: 10px; text-decoration: none;">👁️</a>
                                        <a href="{{ route('buildings.edit', $building) }}" style="margin-left: 10px; text-decoration: none;">✏️</a>
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
    .type-badge { background: #10b981; color: #fff; font-size: 0.65rem; font-weight: 800; padding: 4px 8px; border-radius: 4px; }
    .building-details { padding: 1.25rem; }
    .building-details h3 { font-size: 1.1rem; margin: 0; color: #fff; font-weight: 700; }
    .building-details p { color: #a0a0a0; font-size: 0.85rem; margin: 5px 0 15px 0; }
    
    .action-footer { display: flex; gap: 10px; border-top: 1px solid #2d2d2d; padding-top: 1rem; }
    .icon-link { background: #2d2d2d; border: none; padding: 8px; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 0.9rem; flex: 1; text-align: center; }
    .delete-btn:hover { background: #ef4444; }

    .view-switcher { background: #181818; padding: 4px; border-radius: 8px; border: 1px solid #2d2d2d; display: flex; }
    .view-btn { background: transparent; border: none; color: #a0a0a0; padding: 5px 15px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: 600; }
    .view-btn.active { background: #10b981; color: white; }

    .dark-table { width: 100%; border-collapse: collapse; }
    .dark-table th { text-align: left; color: #a0a0a0; font-size: 0.75rem; text-transform: uppercase; padding: 15px; border-bottom: 1px solid #2d2d2d; }
    .dark-table td { padding: 15px; border-bottom: 1px solid #2d2d2d; font-size: 0.9rem; }
    .type-pill { background: #2d2d2d; color: #10b981; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; }
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