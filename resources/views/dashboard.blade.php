@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="dashboard-wrapper">
    <div class="page-header">
        <div class="header-left">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Welcome back, Jennifer Montil! Here is your property overview.</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="building-2" class="stat-icon"></i>
                </div>
                <div class="stat-trend positive">Total</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">24</span>
                <span class="stat-label">Buildings</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="home" class="stat-icon"></i>
                </div>
                <div class="stat-trend positive">156 Total</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">94%</span>
                <span class="stat-label">Occupancy Rate</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="users" class="stat-icon"></i>
                </div>
                <div class="stat-trend positive">Active</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">142</span>
                <span class="stat-label">Tenants</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="banknote" class="stat-icon"></i>
                </div>
                <div class="stat-trend positive">↑ 23%</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">₱245.8K</span>
                <span class="stat-label">Est. Revenue</span>
            </div>
        </div>
    </div>

    <div class="dashboard-main-grid">
        <div class="content-card chart-span">
            <div class="card-header">
                <h3 class="card-title">Revenue Analytics</h3>
                <select class="dark-filter">
                    <option>Last 6 Months</option>
                    <option>Year to Date</option>
                </select>
            </div>
            <div class="card-body indented-body">
                <canvas id="revenueChart" style="height: 350px;"></canvas>
            </div>
        </div>

        <div class="side-content-grid">
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Open Maintenance</h3>
                    <span class="badge-count">8 Pending</span>
                </div>
                <div class="card-body indented-body">
                    <div class="list-item">
                        <div class="item-dot priority-high"></div>
                        <div class="item-info">
                            <span class="item-title">Critical Plumbing - Unit 7A</span>
                            <span class="item-sub">High Priority</span>
                        </div>
                        <a href="#" class="btn-view-link"><i data-lucide="eye" style="width: 14px;"></i> View</a>
                    </div>
                    <div class="list-item">
                        <div class="item-dot priority-med"></div>
                        <div class="item-info">
                            <span class="item-title">Electrical Check - Lobby</span>
                            <span class="item-sub">Medium Priority</span>
                        </div>
                        <a href="#" class="btn-view-link"><i data-lucide="eye" style="width: 14px;"></i> View</a>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Critical Alerts</h3>
                </div>
                <div class="card-body indented-body">
                    <div class="list-item no-border">
                        <div class="item-info">
                            <span class="item-title" style="color: #ef4444;">3 Overdue Bills</span>
                            <span class="item-sub">Follow up required immediately</span>
                        </div>
                        <a href="#" class="btn-view-link alert-variant"><i data-lucide="chevron-right" style="width: 14px;"></i> View</a>
                    </div>
                </div>
            </div>
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
}

/* Base Layout */
.dashboard-wrapper { background-color: var(--bg-deep); min-height: 100vh; padding: 2rem; color: var(--text-main); font-family: 'Inter', sans-serif; }
.page-header { border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem; margin-bottom: 2rem; }
.page-title { font-size: 1.75rem; font-weight: 700; margin: 0; color: #fff; }
.page-subtitle { color: var(--text-muted); margin-top: 0.25rem; }

/* Stats Cards */
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.stat-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; height: 160px; display: flex; flex-direction: column; box-sizing: border-box; }
.stat-header { display: flex; justify-content: space-between; align-items: flex-start; }
.stat-icon-wrapper { background: rgba(16, 185, 129, 0.1); width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
.stat-icon { width: 20px; height: 20px; color: var(--accent-emerald); stroke-width: 2px; }

.stat-body { margin-top: 1.25rem; } 
.stat-value { display: block; font-size: 1.8rem; font-weight: 700; color: #fff; line-height: 1; }
.stat-label { color: var(--text-muted); text-transform: uppercase; font-size: 0.65rem; letter-spacing: 1px; margin-top: 6px; display: block; }
.stat-trend { font-size: 0.7rem; color: var(--accent-emerald); background: rgba(16, 185, 129, 0.1); padding: 2px 8px; border-radius: 10px; font-weight: 700; }

/* Main Grid & Indentation */
.dashboard-main-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; }
.content-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; }
.card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
.card-title { font-size: 0.95rem; margin: 0; font-weight: 700; letter-spacing: 0.3px; }

.indented-body { padding: 1.5rem 1.5rem 1.5rem 2.5rem; } 

.dark-filter { background: var(--bg-surface); border: 1px solid var(--border-color); color: #fff; padding: 5px 10px; border-radius: 6px; font-size: 0.8rem; }

/* List Item Styling */
.list-item { display: flex; align-items: center; gap: 1rem; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.03); }
.list-item.no-border { border: none; }
.item-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.priority-high { background: #ef4444; box-shadow: 0 0 8px rgba(239, 68, 68, 0.4); }
.priority-med { background: #f59e0b; }
.item-info { flex: 1; }
.item-title { display: block; font-size: 0.85rem; font-weight: 600; color: #e2e8f0; }
.item-sub { color: var(--text-muted); font-size: 0.75rem; margin-top: 2px; display: block; }

/* View Button Styling */
.btn-view-link {
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: 0.2s;
    border: 1px solid rgba(255, 255, 255, 0.03);
}
.btn-view-link:hover {
    background: var(--accent-emerald);
    color: #fff;
    border-color: var(--accent-emerald);
}
.btn-view-link.alert-variant:hover {
    background: #ef4444;
    border-color: #ef4444;
}

.side-content-grid { display: flex; flex-direction: column; gap: 1.5rem; }
.badge-count { font-size: 0.7rem; color: var(--accent-emerald); font-weight: 700; background: rgba(16, 185, 129, 0.1); padding: 2px 8px; border-radius: 6px; }

@media (max-width: 1000px) { .dashboard-main-grid { grid-template-columns: 1fr; } }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.lucide) { lucide.createIcons(); }

    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                data: [185000, 210000, 195000, 225000, 240000, 245800],
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.08)',
                tension: 0.4,
                fill: true,
                pointRadius: 0,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#64748b', font: { size: 11 } } },
                x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 11 } } }
            }
        }
    });
});
</script>
@endsection