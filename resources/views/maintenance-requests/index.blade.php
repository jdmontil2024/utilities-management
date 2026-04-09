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
                <select name="status" class="dark-filter" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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
                        <div class="maintenance-item-card">
                            <div class="maintenance-thumb" style="background-image: linear-gradient(rgba(0,0,0,0), rgba(0,0,0,0.7)), url('{{ $request->photo_url ?? asset('images/maintenance-placeholder.jpg') }}')">
                                <span class="priority-badge {{ $request->priority }}">
                                    {{ strtoupper($request->priority) }}
                                </span>
                            </div>
                            <div class="maintenance-details">
                                <div class="maintenance-header">
                                    <h3>#{{ $request->id }} - {{ Str::limit($request->title, 25) }}</h3>
                                    <span class="status-badge {{ $request->status }}">
                                        {{ strtoupper(str_replace('_', ' ', $request->status)) }}
                                    </span>
                                </div>
                                <p>{{ $request->unit->building->name ?? 'N/A' }} (Unit {{ $request->unit->unit_number ?? 'N/A' }})</p>
                                
                                <div class="maintenance-category">
                                    <span>{{ $request->maintenanceCategory->name ?? 'General' }}</span>
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
                                <th>ID</th>
                                <th>Title</th>
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
                                    <td style="font-weight: 600; color: #fff;">#{{ $request->id }}</td>
                                    <td style="color: var(--text-muted);">{{ Str::limit($request->title, 30) }}</td>
                                    <td style="color: var(--text-muted);">
                                        {{ $request->unit->building->name ?? 'N/A' }}
                                        <span style="display:block; font-size: 0.75rem;">Unit {{ $request->unit->unit_number ?? '-' }}</span>
                                    </td>
                                    <td style="color: var(--text-muted);">{{ $request->maintenanceCategory->name ?? 'General' }}</td>
                                    <td>
                                        <span class="priority-pill {{ $request->priority }}">
                                            {{ strtoupper($request->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-pill {{ $request->status }}">
                                            {{ strtoupper(str_replace('_', ' ', $request->status)) }}
                                        </span>
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
                
                <div class="pagination-wrapper">
                    {{ $requests->links() }}
                </div>
            @else
                <div class="empty-state">
                    <i data-lucide="wrench" style="width: 48px; height: 48px; margin-bottom: 1rem;"></i>
                    <p>No maintenance requests found.</p>
                    <a href="{{ route('maintenance-requests.create') }}" class="btn-emerald-action">Create your first request</a>
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

    /* STAT CARDS WITH PULSATING LINE */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    .stat-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; height: 160px; display: flex; flex-direction: column; box-sizing: border-box; position: relative; overflow: hidden; transition: all 0.3s ease; }
    .stat-card::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 3px; background: linear-gradient(90deg, transparent, var(--accent-emerald), transparent); transition: left 0.3s ease; }
    .stat-card:hover::before { left: 100%; animation: pulse 1.5s ease-in-out; }
    @keyframes pulse { 0% { opacity: 0; left: -100%; } 50% { opacity: 1; left: 0%; } 100% { opacity: 0; left: 100%; } }
    .stat-card:hover { border-color: var(--accent-emerald); transform: translateY(-3px); }
    .stat-header { display: flex; justify-content: space-between; align-items: flex-start; }
    .stat-icon-wrapper { background: rgba(16, 185, 129, 0.1); width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
    .stat-icon { width: 20px; height: 20px; color: var(--accent-emerald); stroke-width: 2px; }
    .stat-body { margin-top: 1.25rem; } 
    .stat-value { display: block; font-size: 1.8rem; font-weight: 700; color: #fff; line-height: 1; }
    .stat-label { color: var(--text-muted); text-transform: uppercase; font-size: 0.65rem; letter-spacing: 1px; margin-top: 6px; display: block; }
    .stat-trend { font-size: 0.7rem; color: var(--accent-emerald); background: rgba(16, 185, 129, 0.1); padding: 2px 8px; border-radius: 10px; font-weight: 700; }
    .stat-trend.warning { color: var(--accent-warning); background: rgba(245, 158, 11, 0.1); }
    .stat-trend.negative { color: var(--accent-red); background: rgba(239, 68, 68, 0.1); }

    .content-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; }
    .card-header { padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); }
    .indented-body { padding: 1.5rem 1.5rem 1.5rem 2.5rem; }

    .btn-emerald-action { background: var(--accent-emerald); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; width: fit-content; transition: 0.2s; font-size: 0.9rem; }
    .btn-emerald-action:hover { background: #0d9488; }

    /* Grid Items */
    .maintenance-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }
    .maintenance-item-card { background: var(--bg-surface); border-radius: 12px; border: 1px solid var(--border-color); overflow: hidden; transition: 0.3s; position: relative; }
    .maintenance-item-card:hover { border-color: var(--accent-emerald); transform: translateY(-4px); }
    
    .maintenance-thumb { height: 150px; background-size: cover; background-position: center; padding: 1rem; position: relative; }
    
    .priority-badge { position: absolute; top: 10px; left: 10px; font-size: 0.7rem; font-weight: 600; padding: 4px 10px; border-radius: 6px; color: #fff; letter-spacing: 0.3px; box-shadow: 0 1px 3px rgba(0,0,0,0.2); z-index: 10; text-transform: uppercase; }
    .priority-badge.low { background: var(--accent-emerald); }
    .priority-badge.medium { background: var(--accent-blue); }
    .priority-badge.high { background: var(--accent-warning); color: #000; }
    .priority-badge.emergency { background: var(--accent-red); }
    
    .maintenance-details { padding: 1.25rem; }
    .maintenance-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; gap: 10px; }
    .maintenance-header h3 { font-size: 1rem; margin: 0; color: #fff; font-weight: 700; flex: 1; }
    
    .status-badge { font-size: 0.7rem; font-weight: 600; padding: 4px 10px; border-radius: 6px; color: #fff; letter-spacing: 0.3px; text-transform: uppercase; white-space: nowrap; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); backdrop-filter: blur(4px); }
    .status-badge.submitted { background: #6c757d; }
    .status-badge.assigned { background: var(--accent-blue); }
    .status-badge.in_progress { background: var(--accent-warning); color: #000; }
    .status-badge.completed { background: var(--accent-emerald); }
    .status-badge.cancelled { background: var(--accent-red); }
    
    .maintenance-details p { color: var(--text-muted); font-size: 0.85rem; margin: 0 0 12px 0; }
    .maintenance-category { font-size: 0.7rem; font-weight: 600; padding: 4px 10px; border-radius: 6px; color: #fff; display: inline-block; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); backdrop-filter: blur(4px); margin-bottom: 15px; }

    .action-footer { display: flex; gap: 8px; border-top: 1px solid var(--border-color); padding-top: 1rem; }
    .icon-link { background: #262626; border: none; color: #fff; padding: 8px; border-radius: 6px; cursor: pointer; text-decoration: none; flex: 1; display: flex; justify-content: center; align-items: center; transition: 0.2s; }
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
    
    .priority-pill { padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; display: inline-block; text-transform: uppercase; }
    .priority-pill.low { background: var(--accent-emerald); color: white; }
    .priority-pill.medium { background: var(--accent-blue); color: white; }
    .priority-pill.high { background: var(--accent-warning); color: #000; }
    .priority-pill.emergency { background: var(--accent-red); color: white; }
    
    .status-pill { padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; display: inline-block; text-transform: uppercase; }
    .status-pill.submitted { background: #6c757d; color: white; }
    .status-pill.assigned { background: var(--accent-blue); color: white; }
    .status-pill.in_progress { background: var(--accent-warning); color: #000; }
    .status-pill.completed { background: var(--accent-emerald); color: white; }
    .status-pill.cancelled { background: var(--accent-red); color: white; }
    
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
    if (confirm(`Are you sure you want to delete maintenance request "${name}"? This cannot be undone.`)) {
        form.submit();
    }
}
</script>
@endsection