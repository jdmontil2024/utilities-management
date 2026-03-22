@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Welcome back, {{ auth()->user()->name }}! Here's what's happening with your properties.</p>
    </div>
    <!-- Date and clock removed -->
</div>

    <!-- Quick Stats Row -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">🏢</div>
            <div class="stat-details">
                <span class="stat-value">24</span>
                <span class="stat-label">Total Buildings</span>
            </div>
            <div class="stat-trend positive">
                <span>↑ 12%</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🏠</div>
            <div class="stat-details">
                <span class="stat-value">156</span>
                <span class="stat-label">Total Units</span>
            </div>
            <div class="stat-trend positive">
                <span>↑ 8%</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-details">
                <span class="stat-value">142</span>
                <span class="stat-label">Active Tenants</span>
            </div>
            <div class="stat-trend positive">
                <span>↑ 15%</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-details">
                <span class="stat-value">₱245.8K</span>
                <span class="stat-label">Monthly Revenue</span>
            </div>
            <div class="stat-trend positive">
                <span>↑ 23%</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🔧</div>
            <div class="stat-details">
                <span class="stat-value">8</span>
                <span class="stat-label">Open Maintenance</span>
            </div>
            <div class="stat-trend negative">
                <span>↑ 2</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-details">
                <span class="stat-value">94%</span>
                <span class="stat-label">Occupancy Rate</span>
            </div>
            <div class="stat-trend positive">
                <span>↑ 5%</span>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="charts-grid">
        <!-- Revenue Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Revenue Overview</h3>
                <div class="chart-actions">
                    <select class="chart-filter" id="revenuePeriod">
                        <option value="week">This Week</option>
                        <option value="month" selected>This Month</option>
                        <option value="quarter">This Quarter</option>
                        <option value="year">This Year</option>
                    </select>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="revenueChart" style="width:100%; height:300px;"></canvas>
            </div>
        </div>

        <!-- Occupancy Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Occupancy by Building</h3>
                <div class="chart-actions">
                    <select class="chart-filter" id="occupancyPeriod">
                        <option value="all">All Buildings</option>
                        <option value="top5">Top 5</option>
                        <option value="bottom5">Bottom 5</option>
                    </select>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="occupancyChart" style="width:100%; height:300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="charts-grid">
        <!-- Maintenance Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Maintenance Requests</h3>
                <div class="legend">
                    <span class="legend-item"><span class="legend-color" style="background: #f39c12;"></span> Pending</span>
                    <span class="legend-item"><span class="legend-color" style="background: #3498db;"></span> In Progress</span>
                    <span class="legend-item"><span class="legend-color" style="background: #27ae60;"></span> Completed</span>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="maintenanceChart" style="width:100%; height:300px;"></canvas>
            </div>
        </div>

        <!-- Expense Distribution Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Expense Distribution</h3>
                <div class="chart-actions">
                    <select class="chart-filter" id="expensePeriod">
                        <option value="month">This Month</option>
                        <option value="quarter">This Quarter</option>
                        <option value="year" selected>This Year</option>
                    </select>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="expenseChart" style="width:100%; height:300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activities and Alerts Row -->
    <div class="activities-grid">
        <!-- Recent Activities -->
        <div class="activities-card">
            <div class="activities-header">
                <h3 class="activities-title">Recent Activities</h3>
                <a href="#" class="view-all">View All →</a>
            </div>
            <div class="activities-list">
                <div class="activity-item">
                    <div class="activity-icon" style="background: #e8f4fc;">💰</div>
                    <div class="activity-details">
                        <div class="activity-title">Payment received from Unit 4B</div>
                        <div class="activity-time">5 minutes ago</div>
                    </div>
                    <div class="activity-amount positive">+₱12,500</div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon" style="background: #fef2f2;">🔧</div>
                    <div class="activity-details">
                        <div class="activity-title">New maintenance request - Unit 7A</div>
                        <div class="activity-time">2 hours ago</div>
                    </div>
                    <div class="activity-status warning">High Priority</div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon" style="background: #f3f4f6;">👤</div>
                    <div class="activity-details">
                        <div class="activity-title">New tenant moved into Unit 12C</div>
                        <div class="activity-time">Yesterday at 2:30 PM</div>
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon" style="background: #e8f5e9;">📄</div>
                    <div class="activity-details">
                        <div class="activity-title">Lease renewed for Unit 3B</div>
                        <div class="activity-time">Yesterday at 11:20 AM</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts & Notifications -->
        <div class="alerts-card">
            <div class="alerts-header">
                <h3 class="alerts-title">Alerts & Notifications</h3>
                <span class="alert-count">3 New</span>
            </div>
            <div class="alerts-list">
                <div class="alert-item alert-warning">
                    <div class="alert-icon">⚠️</div>
                    <div class="alert-content">
                        <div class="alert-title">Lease Expiring Soon</div>
                        <div class="alert-message">5 leases expiring in the next 30 days</div>
                        <div class="alert-time">2 days left</div>
                    </div>
                </div>

                <div class="alert-item alert-danger">
                    <div class="alert-icon">🔴</div>
                    <div class="alert-content">
                        <div class="alert-title">Overdue Maintenance</div>
                        <div class="alert-message">3 maintenance requests overdue</div>
                        <div class="alert-time">Overdue by 2-5 days</div>
                    </div>
                </div>

                <div class="alert-item alert-success">
                    <div class="alert-icon">✅</div>
                    <div class="alert-content">
                        <div class="alert-title">Meter Reading Due</div>
                        <div class="alert-message">12 units need meter readings this week</div>
                        <div class="alert-time">Due in 3 days</div>
                    </div>
                </div>

                <div class="alert-item alert-info">
                    <div class="alert-icon">ℹ️</div>
                    <div class="alert-content">
                        <div class="alert-title">Vendor Contract Renewal</div>
                        <div class="alert-message">3 vendor contracts expiring next month</div>
                        <div class="alert-time">30 days left</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Dashboard Specific Styles */
.container-fluid {
    max-width: 1600px;
    margin: 0 auto;
    padding: 20px 30px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e9ecef;
}

.page-title {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
}

.page-subtitle {
    color: #6c757d;
    font-size: 14px;
}

.date-time-display {
    background: #f8f9fa;
    padding: 10px 20px;
    border-radius: 12px;
    border: 1px solid #dee2e6;
    text-align: center;
    min-width: 220px;
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
}

.current-date {
    font-size: 14px;
    font-weight: 500;
    color: #2c3e50;
    margin-bottom: 5px;
}

.current-time {
    font-size: 24px;
    font-weight: 700;
    color: #3498db;
    font-family: 'Inter', monospace;
    letter-spacing: 1px;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
    border: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,.1);
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: #f8f9fa;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.stat-details {
    flex: 1;
}

.stat-value {
    display: block;
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
    line-height: 1.2;
}

.stat-label {
    font-size: 13px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.stat-trend {
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.stat-trend.positive {
    background: #e8f5e9;
    color: #27ae60;
}

.stat-trend.negative {
    background: #fee9e7;
    color: #e74c3c;
}

/* Charts Grid */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 25px;
    margin-bottom: 25px;
}

.chart-card {
    background: white;
    border-radius: 10px;
    border: 1px solid #e9ecef;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
}

.chart-header {
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
}

.chart-title {
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.chart-actions {
    display: flex;
    gap: 10px;
}

.chart-filter {
    padding: 5px 10px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    font-size: 12px;
    font-family: 'Inter', sans-serif;
    color: #2c3e50;
    background: white;
    cursor: pointer;
}

.chart-filter:focus {
    outline: none;
    border-color: #3498db;
}

.chart-body {
    padding: 20px;
}

.legend {
    display: flex;
    gap: 15px;
    font-size: 12px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #6c757d;
}

.legend-color {
    width: 10px;
    height: 10px;
    border-radius: 3px;
}

/* Activities Grid */
.activities-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 25px;
    margin-top: 25px;
}

.activities-card, .alerts-card {
    background: white;
    border-radius: 10px;
    border: 1px solid #e9ecef;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
}

.activities-header, .alerts-header {
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
}

.activities-title, .alerts-title {
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.view-all {
    color: #3498db;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
}

.view-all:hover {
    text-decoration: underline;
}

.alert-count {
    background: #3498db;
    color: white;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.activities-list, .alerts-list {
    padding: 10px 0;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.2s;
}

.activity-item:hover {
    background: #f8f9fa;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.activity-details {
    flex: 1;
}

.activity-title {
    font-weight: 500;
    color: #2c3e50;
    margin-bottom: 3px;
}

.activity-time {
    font-size: 11px;
    color: #6c757d;
}

.activity-amount {
    font-weight: 600;
    font-size: 14px;
}

.activity-amount.positive {
    color: #27ae60;
}

.activity-status {
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 500;
}

.activity-status.warning {
    background: #fef3c7;
    color: #d97706;
}

/* Alert Items */
.alert-item {
    display: flex;
    gap: 15px;
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
}

.alert-item:last-child {
    border-bottom: none;
}

.alert-icon {
    font-size: 18px;
}

.alert-content {
    flex: 1;
}

.alert-title {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 3px;
    font-size: 14px;
}

.alert-message {
    font-size: 13px;
    color: #6c757d;
    margin-bottom: 3px;
}

.alert-time {
    font-size: 11px;
    font-weight: 500;
}

.alert-warning .alert-time { color: #d97706; }
.alert-danger .alert-time { color: #dc2626; }
.alert-success .alert-time { color: #059669; }
.alert-info .alert-time { color: #2563eb; }

/* Responsive Design */
@media (max-width: 1200px) {
    .charts-grid,
    .activities-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .container-fluid {
        padding: 15px;
    }
    
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .date-time-display {
        width: 100%;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .stat-card {
        padding: 15px;
    }
    
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 20px;
    }
    
    .stat-value {
        font-size: 18px;
    }
    
    .stat-label {
        font-size: 11px;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .chart-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .activity-item {
        flex-wrap: wrap;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Real-time clock update
    function updateClock() {
        const now = new Date();
        const timeElement = document.getElementById('currentTime');
        
        if (timeElement) {
            // Format: HH:MM:SS AM/PM
            let hours = now.getHours();
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            
            hours = hours % 12;
            hours = hours ? hours : 12; // 0 should be 12
            hours = hours.toString().padStart(2, '0');
            
            timeElement.textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
        }
    }
    
    // Update clock immediately and then every second
    updateClock();
    setInterval(updateClock, 1000);

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Revenue 2024',
                data: [185000, 195000, 210000, 225000, 240000, 245800, 252000, 258000, 265000, 272000, 280000, 290000],
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#3498db',
                pointBorderColor: 'white',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }, {
                label: 'Revenue 2023',
                data: [150000, 160000, 170000, 180000, 190000, 195000, 200000, 205000, 210000, 215000, 220000, 230000],
                borderColor: '#95a5a6',
                backgroundColor: 'rgba(149, 165, 166, 0.05)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#95a5a6',
                pointBorderColor: 'white',
                pointBorderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5,
                borderDash: [5, 5]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 6
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += '₱' + context.parsed.y.toLocaleString();
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            return '₱' + value.toLocaleString();
                        }
                    },
                    grid: {
                        color: '#e9ecef'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Occupancy Chart
    const occupancyCtx = document.getElementById('occupancyChart').getContext('2d');
    new Chart(occupancyCtx, {
        type: 'bar',
        data: {
            labels: ['Sunset Tower', 'Riverside Plaza', 'Harbor View', 'City Heights', 'Oakwood', 'Park Avenue'],
            datasets: [{
                label: 'Occupied Units',
                data: [45, 38, 42, 35, 28, 32],
                backgroundColor: '#3498db',
                borderRadius: 6
            }, {
                label: 'Vacant Units',
                data: [5, 2, 3, 5, 2, 3],
                backgroundColor: '#e74c3c',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 6
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    stacked: true,
                    grid: {
                        color: '#e9ecef'
                    }
                },
                x: {
                    stacked: true,
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Maintenance Chart
    const maintenanceCtx = document.getElementById('maintenanceChart').getContext('2d');
    new Chart(maintenanceCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'In Progress', 'Completed'],
            datasets: [{
                data: [8, 12, 25],
                backgroundColor: ['#f39c12', '#3498db', '#27ae60'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 6,
                        padding: 20
                    }
                }
            },
            cutout: '60%'
        }
    });

    // Expense Distribution Chart
    const expenseCtx = document.getElementById('expenseChart').getContext('2d');
    new Chart(expenseCtx, {
        type: 'pie',
        data: {
            labels: ['Utilities', 'Maintenance', 'Property Tax', 'Insurance', 'Management Fees', 'Other'],
            datasets: [{
                data: [45000, 35000, 25000, 15000, 20000, 10000],
                backgroundColor: [
                    '#3498db',
                    '#e74c3c',
                    '#f39c12',
                    '#27ae60',
                    '#9b59b6',
                    '#95a5a6'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 6,
                        padding: 20
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed !== null) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                label += '₱' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Chart period filters
    document.getElementById('revenuePeriod').addEventListener('change', function(e) {
        // In a real application, this would fetch new data
        console.log('Revenue period changed to:', e.target.value);
    });

    document.getElementById('occupancyPeriod').addEventListener('change', function(e) {
        // In a real application, this would fetch new data
        console.log('Occupancy filter changed to:', e.target.value);
    });

    document.getElementById('expensePeriod').addEventListener('change', function(e) {
        // In a real application, this would fetch new data
        console.log('Expense period changed to:', e.target.value);
    });
});
</script>
@endsection