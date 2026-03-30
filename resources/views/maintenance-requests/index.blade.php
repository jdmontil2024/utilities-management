@extends('layouts.app')

@section('content')
<div class="dashboard-wrapper" style="background-color: #121212; min-height: 100vh; padding: 2rem; color: #ffffff; font-family: 'Inter', sans-serif;">
    
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #2d2d2d; padding-bottom: 1.5rem; margin-bottom: 2rem;">
        <div class="header-left">
            <h1 class="page-title" style="font-size: 1.75rem; font-weight: 700; margin: 0; color: #fff;">Maintenance Requests</h1>
            <p class="page-subtitle" style="color: #a0a0a0; margin-top: 0.25rem;">Facility & Community Upkeep Management</p>
        </div>
        <div class="header-right">
            <a href="{{ route('maintenance-requests.create') }}" class="btn-emerald-action">
                + New Request
            </a>
        </div>
    </div>

    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">🔧</div>
                <div class="stat-trend positive">Total</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $requests->total() }}</span>
                <span class="stat-label">Total Requests</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">🕒</div>
                <div class="stat-trend warning">Pending</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $requests->whereIn('status', ['submitted', 'assigned'])->count() }}</span>
                <span class="stat-label">Open Issues</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">🚨</div>
                <div class="stat-trend negative">Critical</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $requests->where('priority', 'emergency')->count() }}</span>
                <span class="stat-label">Emergency Priority</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">✅</div>
                <div class="stat-trend positive">Success</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $requests->where('status', 'completed')->count() }}</span>
                <span class="stat-label">Resolved This Month</span>
            </div>
        </div>
    </div>

    <div class="content-card" style="margin-bottom: 2rem;">
        <div class="card-header" style="padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #2d2d2d;">
            <form action="{{ route('maintenance-requests.index') }}" method="GET" style="display: flex; gap: 1rem; flex-grow: 1;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ID or Title..." class="dark-input">
                <select name="priority" class="dark-filter" onchange="this.form.submit()">
                    <option value="">All Priorities</option>
                    <option value="emergency" {{ request('priority') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </form>
            <div class="view-switcher">
                <button onclick="toggleView('grid')" id="gridBtn" class="view-btn active">Grid</button>
                <button onclick="toggleView('table')" id="tableBtn" class="view-btn">Table</button>
            </div>
        </div>

        <div class="card-body" style="padding: 1.5rem;">
            @if($requests->count() > 0)
                <div id="gridView" class="buildings-grid">
                    @foreach($requests as $request)
                        @php
                            $daysOpen = (int) $request->created_at->diffInDays(now());
                            $priorityColor = match($request->priority) {
                                'emergency' => '#ef4444',
                                'high' => '#f59e0b',
                                'medium' => '#3b82f6',
                                default => '#10b981'
                            };
                        @endphp
                        <div class="building-item-card">
                            <div class="building-thumb" style="background: #252525; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative;">
                                <div class="tenant-avatar-circle" style="width: 60px; height: 60px; background: rgba(255,255,255,0.05); border: 2px solid {{ $priorityColor }}; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: {{ $priorityColor }}; font-weight: 700;">
                                    #{{ $request->id }}
                                </div>
                                <span class="type-badge" style="z-index: 1; margin-top: 10px; background: {{ $priorityColor }};">
                                    {{ strtoupper($request->priority) }}
                                </span>
                            </div>
                            <div class="building-details">
                                <h3 style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    {{ Str::limit($request->title, 20) }}
                                    <span style="color: #a0a0a0; font-size: 0.75rem;">{{ $daysOpen }}d open</span>
                                </h3>
                                <p style="margin-bottom: 8px; color: #fff; font-weight: 500;">📍 {{ $request->unit->unit_number ?? 'Common Area' }}</p>
                                
                                <div style="display: flex; flex-direction: column; gap: 4px; margin-bottom: 15px; font-size: 0.8rem; color: #a0a0a0;">
                                    <span>🏢 {{ $request->unit->building->name ?? 'N/A' }}</span>
                                    <span>🛠️ {{ $request->maintenanceCategory->name ?? 'General' }}</span>
                                </div>

                                <div class="action-footer">
                                    <a href="{{ route('maintenance-requests.show', $request) }}" class="icon-link">👁️ View</a>
                                    <a href="{{ route('maintenance-requests.edit', $request) }}" class="icon-link">✏️ Edit</a>
                                    <form action="{{ route('maintenance-requests.destroy', $request) }}" method="POST" onsubmit="return confirmDelete(event, this, '#{{ $request->id }}')">
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
                                <th>Request Details</th>
                                <th>Location</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $request)
                                <tr>
                                    <td style="font-weight: 600; color: #fff;">
                                        #{{ $request->id }} - {{ Str::limit($request->title, 30) }}
                                        <div style="font-size: 0.7rem; color: #666; font-weight: 400;">Opened: {{ $request->created_at->format('M d, Y') }}</div>
                                    </td>
                                    <td style="color: #a0a0a0;">{{ $request->unit->building->name ?? 'N/A' }} (Unit {{ $request->unit->unit_number ?? '-' }})</td>
                                    <td style="color: #fff;">{{ $request->maintenanceCategory->name ?? 'General' }}</td>
                                    <td>
                                        <span class="type-pill" style="color: {{ $request->priority === 'emergency' ? '#ef4444' : '#10b981' }}; border: 1px solid rgba(255,255,255,0.1);">
                                            {{ strtoupper($request->priority) }}
                                        </span>
                                    </td>
                                    <td style="color: #a0a0a0;">{{ ucfirst(str_replace('_', ' ', $request->status)) }}</td>
                                    <td style="text-align: right;">
                                        <a href="{{ route('maintenance-requests.show', $request) }}" style="margin-left: 10px; text-decoration: none;">👁️</a>
                                        <a href="{{ route('maintenance-requests.edit', $request) }}" style="margin-left: 10px; text-decoration: none;">✏️</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align: center; padding: 3rem; color: #a0a0a0;">
                    <p>No maintenance requests found matching your criteria.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    /* Exact style port from your Lease page */
    .stat-card { background: #1d1d1d; border: 1px solid #2d2d2d; border-radius: 12px; padding: 1.5rem; height: 140px; display: flex; flex-direction: column; justify-content: space-between; box-sizing: border-box; transition: 0.3s; }
    .stat-card:hover { border-color: #10b981; transform: translateY(-3px); }
    .stat-header { display: flex; justify-content: space-between; align-items: flex-start; }
    .stat-icon-wrapper { font-size: 1.5rem; background: rgba(16, 185, 129, 0.1); padding: 0.5rem; border-radius: 8px; line-height: 1; }
    .stat-trend { font-size: 0.7rem; color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 2px 8px; border-radius: 10px; font-weight: 700; text-transform: uppercase; }
    .stat-trend.warning { color: #f59e0b; background: rgba(245, 158, 11, 0.1); }
    .stat-trend.negative { color: #ef4444; background: rgba(239, 68, 68, 0.1); }
    .stat-value { display: block; font-size: 1.8rem; font-weight: 700; color: #fff; line-height: 1.1; }
    .stat-label { color: #a0a0a0; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 1px; margin-top: 4px; display: block; }

    .content-card { background: #1d1d1d; border: 1px solid #2d2d2d; border-radius: 12px; overflow: hidden; }
    .dark-filter, .dark-input { background: #181818; border: 1px solid #2d2d2d; color: #fff; padding: 8px 15px; border-radius: 8px; outline: none; }
    .btn-emerald-action { background: #10b981; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 700; transition: 0.2s; border: none; display: inline-block; }
    
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
    if (confirm(`Are you sure you want to delete request "${name}"? This cannot be undone.`)) {
        form.submit();
    }
}
</script>
@endsection