@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="dashboard-wrapper">
    <div class="page-header">
        <div class="header-left">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Welcome back, {{ Auth::user()->name ?? 'Property Manager' }}! Here is your property overview.</p>
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
                <span class="stat-value">{{ number_format($stats['total_buildings'] ?? 0) }}</span>
                <span class="stat-label">Total Buildings</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="home" class="stat-icon"></i>
                </div>
                <div class="stat-trend">{{ number_format($stats['total_units'] ?? 0) }} Total</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ number_format($stats['total_units'] ?? 0) }}</span>
                <span class="stat-label">Total Units</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="users" class="stat-icon"></i>
                </div>
                <div class="stat-trend">Active</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">{{ number_format($stats['total_tenants'] ?? 0) }}</span>
                <span class="stat-label">Total Tenants</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrapper">
                    <i data-lucide="banknote" class="stat-icon"></i>
                </div>
                <div class="stat-trend">↑ {{ number_format($revenueGrowth ?? 0) }}%</div>
            </div>
            <div class="stat-body">
                <span class="stat-value">₱{{ number_format($stats['monthly_revenue'] ?? 0) }}</span>
                <span class="stat-label">Monthly Revenue</span>
            </div>
        </div>
    </div>

    <div class="dashboard-main-grid">
        <div class="content-card chart-span">
            <div class="card-header">
                <h3 class="card-title">Revenue Analytics</h3>
                @if(count($chartMonths) > 0 && $chartMonths[0] != 'No Data')
                    <span class="badge-count">{{ count($chartMonths) }} Months with Data</span>
                @endif
            </div>
            <div class="card-body indented-body">
                <canvas id="revenueChart" style="height: 350px; width: 100%;"></canvas>
            </div>
        </div>

        <div class="side-content-grid">
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Open Maintenance</h3>
                    <span class="badge-count">{{ $stats['pending_maintenance'] ?? 0 }} Pending</span>
                </div>
                <div class="card-body indented-body">
                    @forelse($recentMaintenanceRequests ?? [] as $request)
                        <div class="list-item">
                            <div class="item-dot priority-{{ $request->priority }}"></div>
                            <div class="item-info">
                                <span class="item-title">{{ Str::limit($request->title, 30) }} - Unit {{ $request->unit->unit_number ?? 'N/A' }}</span>
                                <span class="item-sub">{{ ucfirst($request->priority) }} Priority</span>
                            </div>
                            <a href="{{ route('maintenance-requests.show', $request) }}" class="btn-view-link"><i data-lucide="eye" style="width: 14px;"></i> View</a>
                        </div>
                    @empty
                        <div class="list-item no-border">
                            <div class="item-info">
                                <span class="item-title">No open maintenance requests</span>
                                <span class="item-sub">All caught up!</span>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Expiring Leases</h3>
                    <span class="badge-count">{{ $expiringLeasesCount ?? 0 }} Expiring</span>
                </div>
                <div class="card-body indented-body">
                    @forelse($expiringLeases ?? [] as $lease)
                        <div class="list-item">
                            <div class="item-info">
                                <span class="item-title">{{ $lease->tenant->full_name ?? 'N/A' }}</span>
                                <span class="item-sub">Expires: {{ $lease->end_date->format('M d, Y') }} - Unit {{ $lease->unit->unit_number ?? 'N/A' }}</span>
                            </div>
                            <a href="{{ route('leases.show', $lease) }}" class="btn-view-link"><i data-lucide="eye" style="width: 14px;"></i> View</a>
                        </div>
                    @empty
                        <div class="list-item no-border">
                            <div class="item-info">
                                <span class="item-title">No expiring leases</span>
                                <span class="item-sub">All leases are current</span>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Recent Tenants</h3>
                </div>
                <div class="card-body indented-body">
                    @forelse($recentTenants ?? [] as $tenant)
                        <div class="list-item">
                            <div class="item-info">
                                <span class="item-title">{{ $tenant->full_name }}</span>
                                <span class="item-sub">{{ $tenant->building->name ?? 'N/A' }} - Unit {{ $tenant->unit->unit_number ?? 'N/A' }}</span>
                            </div>
                            <a href="{{ route('tenants.show', $tenant) }}" class="btn-view-link"><i data-lucide="eye" style="width: 14px;"></i> View</a>
                        </div>
                    @empty
                        <div class="list-item no-border">
                            <div class="item-info">
                                <span class="item-title">No tenants yet</span>
                                <span class="item-sub">Add your first tenant</span>
                            </div>
                        </div>
                    @endforelse
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
    --accent-red: #ef4444;
    --accent-warning: #f59e0b;
    --accent-blue: #3b82f6;
}

.dashboard-wrapper { background-color: var(--bg-deep); min-height: 100vh; padding: 2rem; color: var(--text-main); font-family: 'Inter', sans-serif; }
.page-header { border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem; margin-bottom: 2rem; }
.page-title { font-size: 1.75rem; font-weight: 700; margin: 0; color: #fff; }
.page-subtitle { color: var(--text-muted); margin-top: 0.25rem; }

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
    0% { opacity: 0; left: -100%; }
    50% { opacity: 1; left: 0%; }
    100% { opacity: 0; left: 100%; }
}

.stat-card:hover {
    border-color: var(--accent-emerald);
    transform: translateY(-3px);
}

.stat-header { display: flex; justify-content: space-between; align-items: flex-start; }
.stat-icon-wrapper { background: rgba(16, 185, 129, 0.1); width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
.stat-icon { width: 20px; height: 20px; color: var(--accent-emerald); stroke-width: 2px; }
.stat-body { margin-top: 1.25rem; } 
.stat-value { display: block; font-size: 1.8rem; font-weight: 700; color: #fff; line-height: 1; }
.stat-label { color: var(--text-muted); text-transform: uppercase; font-size: 0.65rem; letter-spacing: 1px; margin-top: 6px; display: block; }
.stat-trend { font-size: 0.7rem; color: var(--accent-emerald); background: rgba(16, 185, 129, 0.1); padding: 2px 8px; border-radius: 10px; font-weight: 700; }

.dashboard-main-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; }
.content-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; }
.card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
.card-title { font-size: 0.95rem; margin: 0; font-weight: 700; letter-spacing: 0.3px; }
.indented-body { padding: 1.5rem 1.5rem 1.5rem 2.5rem; }

.list-item { display: flex; align-items: center; gap: 1rem; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.03); }
.list-item.no-border { border: none; }
.item-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.priority-emergency { background: var(--accent-red); box-shadow: 0 0 8px rgba(239, 68, 68, 0.4); }
.priority-high { background: var(--accent-warning); box-shadow: 0 0 8px rgba(245, 158, 11, 0.4); }
.priority-medium { background: var(--accent-blue); }
.priority-low { background: var(--accent-emerald); }
.item-info { flex: 1; }
.item-title { display: block; font-size: 0.85rem; font-weight: 600; color: #e2e8f0; }
.item-sub { color: var(--text-muted); font-size: 0.75rem; margin-top: 2px; display: block; }

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

.side-content-grid { display: flex; flex-direction: column; gap: 1.5rem; }
.badge-count { font-size: 0.7rem; color: var(--accent-emerald); font-weight: 700; background: rgba(16, 185, 129, 0.1); padding: 2px 8px; border-radius: 6px; }

@media (max-width: 1000px) { .dashboard-main-grid { grid-template-columns: 1fr; } }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.lucide) { lucide.createIcons(); }
    
    // Get data from PHP
    var months = @json($chartMonths);
    var revenueData = @json($chartRevenueData);
    
    console.log('Months:', months);
    console.log('Revenue Data:', revenueData);
    
    var canvas = document.getElementById('revenueChart');
    
    if (canvas && months && months.length > 0 && months[0] !== 'No Data') {
        var ctx = canvas.getContext('2d');
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Monthly Revenue (₱)',
                    data: revenueData,
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderColor: '#10b981',
                    borderWidth: 1,
                    borderRadius: 8,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: { color: '#a0a0a0' }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₱' + context.raw.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: {
                            color: '#a0a0a0',
                            callback: function(value) {
                                return '₱' + (value / 1000).toFixed(0) + 'K';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Revenue (₱)',
                            color: '#a0a0a0'
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#a0a0a0',
                            rotation: 0
                        },
                        title: {
                            display: true,
                            text: 'Month',
                            color: '#a0a0a0'
                        }
                    }
                }
            }
        });
        
        console.log('Chart created successfully');
    } else {
        canvas.style.display = 'none';
        var emptyMessage = document.createElement('div');
        emptyMessage.className = 'empty-chart';
        emptyMessage.style.cssText = 'text-align: center; padding: 80px 20px; color: var(--text-muted);';
        emptyMessage.innerHTML = `
            <i data-lucide="bar-chart-2" style="width: 48px; height: 48px; margin-bottom: 1rem;"></i>
            <p>No revenue data available yet.</p>
            <p style="font-size: 0.8rem; opacity: 0.7;">Revenue data will appear here once leases are created.</p>
        `;
        canvas.parentNode.appendChild(emptyMessage);
        if (window.lucide) { lucide.createIcons(); }
        console.log('No data available for chart');
    }
});
</script>
@endsection