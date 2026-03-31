@extends('layouts.app')

@section('content')
<div class="dashboard-wrapper">
    
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="header-left">
            <h1 class="page-title">Maintenance Requests</h1>
            <p class="page-subtitle">Facility & Community Upkeep Management</p>
        </div>
        <div class="header-right">
            <a href="{{ route('maintenance-requests.create') }}" class="btn-emerald-action">
                <i data-lucide="plus-circle" style="width: 18px; height: 18px; margin-right: 8px;"></i> 
                New Request
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="wrench" class="stat-icon"></i>
                </div>
                <div class="stat-trend">Total</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $requests->total() }}</span>
                <span class="stat-label">Total Requests</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper" style="background: rgba(245, 158, 11, 0.1);">
                    <i data-lucide="clock" class="stat-icon" style="color: #f59e0b;"></i>
                </div>
                <div class="stat-trend warning">Pending</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $requests->whereIn('status', ['submitted', 'assigned'])->count() }}</span>
                <span class="stat-label">Open Issues</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper" style="background: rgba(239, 68, 68, 0.1);">
                    <i data-lucide="alert-circle" class="stat-icon" style="color: #ef4444;"></i>
                </div>
                <div class="stat-trend negative">Critical</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $requests->where('priority', 'emergency')->count() }}</span>
                <span class="stat-label">Emergency Priority</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="check-square" class="stat-icon"></i>
                </div>
                <div class="stat-trend">Success</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ $requests->where('status', 'completed')->count() }}</span>
                <span class="stat-label">Resolved This Month</span>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header">
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
                <button onclick="toggleView('grid')" id="gridBtn" class="view-btn active">
                    <i data-lucide="layout-grid" style="width: 16px;"></i>
                </button>
                <button onclick="toggleView('list')" id="tableBtn" class="view-btn">
                    <i data-lucide="list" style="width: 16px;"></i>
                </button>
            </div>
        </div>

        <div class="card-body indented-body">
            @if($requests->count() > 0)
                <div id="gridView" class="maintenance-grid">
                    @foreach($requests as $request)
                        @php
                            $daysOpen = (int) $request->created_at->diffInDays(now());
                            $priorityClass = $request->priority;
                        @endphp
                        <div class="maintenance-item-card">
                            <div class="maintenance-thumb">
                                <div class="id-badge-circle {{ $priorityClass }}">
                                    #{{ $request->id }}
                                </div>
                                <span class="priority-tag {{ $priorityClass }}">
                                    {{ strtoupper($request->priority) }}
                                </span>
                            </div>
                            <div class="maintenance-details">
                                <h3 style="display: flex; justify-content: space-between; align-items: baseline;">
                                    {{ Str::limit($request->title, 22) }}
                                    <span class="days-indicator">{{ $daysOpen }}d</span>
                                </h3>
                                <p style="color: #fff; font-weight: 600; margin-top: 4px;">
                                    <i data-lucide="map-pin" style="width:14px; vertical-align: middle; margin-right: 4px; color: var(--text-muted);"></i>
                                    {{ $request->unit->unit_number ?? 'Common Area' }}
                                </p>
                                
                                <div class="maintenance-info-stack">
                                    <span><i data-lucide="building-2"></i> {{ $request->unit->building->name ?? 'N/A' }}</span>
                                    <span><i data-lucide="tag"></i> {{ $request->maintenanceCategory->name ?? 'General' }}</span>
                                </div>

                                <div class="action-footer">
                                    <a href="{{ route('maintenance-requests.show', $request) }}" class="icon-link"><i data-lucide="eye" style="width:16px;"></i></a>
                                    <a href="{{ route('maintenance-requests.edit', $request) }}" class="icon-link"><i data-lucide="pencil" style="width:16px;"></i></a>
                                    <form action="{{ route('maintenance-requests.destroy', $request) }}" method="POST" onsubmit="return confirmDelete(event, this, '#{{ $request->id }}')" style="flex: 1;">
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
                                    <td>
                                        <div style="font-weight: 600; color: #fff;">#{{ $request->id }} - {{ Str::limit($request->title, 30) }}</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">Opened: {{ $request->created_at->format('M d, Y') }}</div>
                                    </td>
                                    <td style="color: var(--text-muted);">
                                        {{ $request->unit->building->name ?? 'N/A' }}
                                        <span style="display:block; font-size: 0.75rem;">Unit {{ $request->unit->unit_number ?? '-' }}</span>
                                    </td>
                                    <td style="color: #fff;">{{ $request->maintenanceCategory->name ?? 'General' }}</td>
                                    <td>
                                        <span class="priority-pill {{ $request->priority }}">
                                            {{ strtoupper($request->priority) }}
                                        </span>
                                    </td>
                                    <td style="color: var(--text-muted); font-size: 0.85rem;">
                                        {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: flex; justify-content: flex-end; gap: 12px;">
                                            <a href="{{ route('maintenance-requests.show', $request) }}" style="color: var(--text-muted);"><i data-lucide="eye" style="width:18px;"></i></a>
                                            <a href="{{ route('maintenance-requests.edit', $request) }}" style="color: var(--text-muted);"><i data-lucide="pencil" style="width:18px;"></i></a>
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
        --accent-blue: #3b82f6;
    }

    .dashboard-wrapper { background-color: var(--bg-deep); min-height: 100vh; padding: 2rem; color: var(--text-main); font-family: 'Inter', sans-serif; }
    .page-header { border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem; margin-bottom: 2rem; }
    .page-title { font-size: 1.75rem; font-weight: 700; margin: 0; color: #fff; }
    .page-subtitle { color: var(--text-muted); margin-top: 0.25rem; }

    /* STAT CARDS */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    .stat-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; height: 160px; display: flex; flex-direction: column; transition: 0.3s; }
    .stat-card:hover { border-color: var(--accent-emerald); transform: translateY(-3px); }
    .stat-icon-wrapper { background: rgba(16, 185, 129, 0.1); width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
    .stat-icon { width: 20px; height: 20px; color: var(--accent-emerald); stroke-width: 2px; }
    .stat-value { display: block; font-size: 1.8rem; font-weight: 700; color: #fff; margin-top: 1.25rem; line-height: 1; }
    .stat-label { color: var(--text-muted); text-transform: uppercase; font-size: 0.65rem; letter-spacing: 1px; margin-top: 6px; display: block; }
    .stat-trend { font-size: 0.7rem; color: var(--accent-emerald); background: rgba(16, 185, 129, 0.1); padding: 2px 8px; border-radius: 10px; font-weight: 700; }
    .stat-trend.warning { color: var(--accent-warning); background: rgba(245, 158, 11, 0.1); }
    .stat-trend.negative { color: var(--accent-red); background: rgba(239, 68, 68, 0.1); }

    /* CONTENT CARD */
    .content-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; }
    .card-header { padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); }
    .indented-body { padding: 1.5rem 1.5rem 1.5rem 2.5rem; }

    /* GRID LAYOUT */
    .maintenance-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
    .maintenance-item-card { background: var(--bg-surface); border-radius: 12px; border: 1px solid var(--border-color); overflow: hidden; transition: 0.3s; }
    .maintenance-item-card:hover { border-color: var(--accent-emerald); transform: translateY(-4px); }
    
    .maintenance-thumb { height: 110px; background: #1a1a1a; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; }
    .id-badge-circle { 
        width: 54px; height: 54px; background: rgba(255, 255, 255, 0.03); 
        border: 2px solid var(--border-color); border-radius: 50%; 
        display: flex; align-items: center; justify-content: center; 
        font-size: 1rem; color: #fff; font-weight: 700; 
    }
    /* Priority Variants */
    .id-badge-circle.emergency { border-color: var(--accent-red); color: var(--accent-red); box-shadow: 0 0 15px rgba(239, 68, 68, 0.1); }
    .id-badge-circle.high { border-color: var(--accent-warning); color: var(--accent-warning); }
    .id-badge-circle.medium { border-color: var(--accent-blue); color: var(--accent-blue); }

    .priority-tag { font-size: 0.55rem; font-weight: 900; padding: 2px 8px; border-radius: 4px; color: #fff; margin-top: 8px; letter-spacing: 0.5px; }
    .priority-tag.emergency { background: var(--accent-red); }
    .priority-tag.high { background: var(--accent-warning); }
    .priority-tag.medium { background: var(--accent-blue); }
    .priority-tag.low { background: var(--accent-emerald); }

    .days-indicator { color: var(--text-muted); font-size: 0.7rem; font-weight: 400; background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; }
    
    .maintenance-details { padding: 1.25rem; }
    .maintenance-details h3 { font-size: 1rem; margin: 0; color: #fff; font-weight: 700; }
    
    .maintenance-info-stack { display: flex; flex-direction: column; gap: 6px; margin-top: 12px; margin-bottom: 15px; font-size: 0.8rem; color: var(--text-muted); }
    .maintenance-info-stack span { display: flex; align-items: center; }
    .maintenance-info-stack i { width: 14px; height: 14px; margin-right: 8px; opacity: 0.6; }

    .action-footer { display: flex; gap: 8px; border-top: 1px solid var(--border-color); padding-top: 1rem; }
    .icon-link { background: #262626; border: none; color: #fff; padding: 8px; border-radius: 6px; cursor: pointer; text-decoration: none; flex: 1; display: flex; justify-content: center; align-items: center; transition: 0.2s; }
    .icon-link:hover { background: var(--accent-emerald); }
    .delete-btn:hover { background: var(--accent-red); }

    /* TABLE STYLES */
    .dark-table { width: 100%; border-collapse: collapse; }
    .dark-table th { text-align: left; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; padding: 15px; border-bottom: 1px solid var(--border-color); }
    .dark-table td { padding: 15px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
    .priority-pill { padding: 2px 8px; border-radius: 4px; font-size: 0.65rem; font-weight: 800; border: 1px solid rgba(255,255,255,0.1); }
    .priority-pill.emergency { color: var(--accent-red); background: rgba(239, 68, 68, 0.05); }
    .priority-pill.high { color: var(--accent-warning); }
    .priority-pill.medium { color: var(--accent-blue); }

    .btn-emerald-action { background: var(--accent-emerald); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; transition: 0.2s; }
    .dark-filter, .dark-input { background: var(--bg-surface); border: 1px solid var(--border-color); color: #fff; padding: 8px 15px; border-radius: 8px; outline: none; }
    .view-switcher { background: var(--bg-surface); padding: 4px; border-radius: 8px; border: 1px solid var(--border-color); display: flex; }
    .view-btn { background: transparent; border: none; color: var(--text-muted); padding: 6px 12px; border-radius: 6px; cursor: pointer; }
    .view-btn.active { background: var(--accent-emerald); color: white; }
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
    if (confirm(`Are you sure you want to delete maintenance request "${name}"? This cannot be undone.`)) {
        form.submit();
    }
}
</script>
@endsection