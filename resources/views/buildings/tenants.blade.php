@extends('layouts.app')

@section('title', $building->name . ' - Tenants')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('buildings.show', $building) }}" class="btn btn-outline-secondary me-3">
                ← Back to Building
            </a>
            <h1 class="h3 mb-0 text-gray-800">
                <span class="me-2">🏢</span> {{ $building->name }} - Tenants
            </h1>
        </div>
        <div class="btn-group">
            <a href="{{ route('buildings.tenants.export', $building) }}" class="btn btn-outline-success me-2">
                📥 Export Tenants
            </a>
            <a href="{{ route('tenants.create', ['building_id' => $building->id]) }}" class="btn btn-primary">
                ➕ Add New Tenant
            </a>
        </div>
    </div>

    <!-- Building Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Units
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $building->units_count ?? $building->units->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-door-open fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Tenants
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $building->activeTenants->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Occupancy Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($building->tenant_occupancy_rate, 1) }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-pie fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Monthly Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₱{{ number_format($building->total_monthly_tenant_revenue, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lease Expiration Alerts -->
    @if($building->expiringLeases->count() > 0 || $building->overdueLeases->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Lease Alerts
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($building->expiringLeases->count() > 0)
                        <div class="col-md-6">
                            <div class="alert alert-warning mb-0">
                                <strong>{{ $building->expiringLeases->count() }}</strong> lease(s) expiring in the next 30 days
                                <button class="btn btn-sm btn-outline-warning ms-3" type="button" data-bs-toggle="collapse" data-bs-target="#expiringLeasesList">
                                    View Details
                                </button>
                                <div class="collapse mt-3" id="expiringLeasesList">
                                    <div class="list-group list-group-flush">
                                        @foreach($building->expiringLeases as $tenant)
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <a href="{{ route('tenants.show', $tenant) }}" class="fw-bold text-decoration-none">
                                                        {{ $tenant->full_name }}
                                                    </a>
                                                    <div class="small text-muted">
                                                        Unit {{ $tenant->unit->unit_number ?? 'N/A' }} • 
                                                        Ends {{ $tenant->lease_end_date?->format('M d, Y') }}
                                                        ({{ now()->diffInDays($tenant->lease_end_date) }} days)
                                                    </div>
                                                </div>
                                                <span class="badge bg-warning text-dark">{{ $tenant->lease_end_date?->diffForHumans() }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($building->overdueLeases->count() > 0)
                        <div class="col-md-6">
                            <div class="alert alert-danger mb-0">
                                <strong>{{ $building->overdueLeases->count() }}</strong> overdue lease(s)
                                <button class="btn btn-sm btn-outline-danger ms-3" type="button" data-bs-toggle="collapse" data-bs-target="#overdueLeasesList">
                                    View Details
                                </button>
                                <div class="collapse mt-3" id="overdueLeasesList">
                                    <div class="list-group list-group-flush">
                                        @foreach($building->overdueLeases as $tenant)
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <a href="{{ route('tenants.show', $tenant) }}" class="fw-bold text-decoration-none">
                                                        {{ $tenant->full_name }}
                                                    </a>
                                                    <div class="small text-muted">
                                                        Unit {{ $tenant->unit->unit_number ?? 'N/A' }} • 
                                                        Expired {{ $tenant->lease_end_date?->format('M d, Y') }}
                                                        ({{ now()->diffInDays($tenant->lease_end_date) }} days overdue)
                                                    </div>
                                                </div>
                                                <span class="badge bg-danger">Overdue</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Filter Tenants</h6>
            <span class="badge bg-primary">Total: {{ $tenants->total() }}</span>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('buildings.tenants', $building) }}" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Lease Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="expiring" {{ request('status') == 'expiring' ? 'selected' : '' }}>Expiring Soon</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label for="unit_filter" class="form-label">Unit</label>
                    <select name="unit_id" id="unit_filter" class="form-select">
                        <option value="">All Units</option>
                        @foreach($building->units as $unit)
                            <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                Unit {{ $unit->unit_number }} - {{ $unit->unit_type_label ?? $unit->unit_type }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-5">
                    <label for="search" class="form-label">Search</label>
                    <div class="input-group">
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="Name, email, phone..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">
                            🔍 Apply Filters
                        </button>
                        @if(request()->anyFilled(['status', 'unit_id', 'search']))
                            <a href="{{ route('buildings.tenants', $building) }}" class="btn btn-outline-secondary">
                                Clear
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tenants Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-users me-2"></i>
                Building Tenants
            </h6>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    Sort By
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => 'asc']) }}">Name (A-Z)</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => 'desc']) }}">Name (Z-A)</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'unit', 'direction' => 'asc']) }}">Unit Number</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'lease_end', 'direction' => 'asc']) }}">Lease End (Soonest)</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'rent', 'direction' => 'desc']) }}">Rent (Highest)</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            @if($tenants->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th>Tenant</th>
                                <th>Unit</th>
                                <th>Contact</th>
                                <th>Lease Period</th>
                                <th>Monthly Rent</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tenants as $tenant)
                                @php
                                    $daysLeft = $tenant->lease_end_date ? now()->diffInDays($tenant->lease_end_date, false) : 0;
                                    $statusColor = match($tenant->lease_status) {
                                        'active' => 'success',
                                        'pending' => 'warning',
                                        'expired' => 'danger',
                                        'terminated' => 'secondary',
                                        default => 'secondary'
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-2">
                                                <span class="initials">{{ substr($tenant->first_name, 0, 1) }}{{ substr($tenant->last_name, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <a href="{{ route('tenants.show', $tenant) }}" class="fw-bold text-decoration-none">
                                                    {{ $tenant->full_name }}
                                                </a>
                                                @if(!$tenant->is_active)
                                                    <span class="badge bg-secondary ms-1">Inactive</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>Unit {{ $tenant->unit->unit_number ?? 'N/A' }}</strong>
                                        <div class="small text-muted">
                                            Floor {{ $tenant->unit->floor ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <i class="fas fa-envelope me-1"></i>
                                            <a href="mailto:{{ $tenant->email }}">{{ $tenant->email }}</a>
                                        </div>
                                        <div class="small">
                                            <i class="fas fa-phone me-1"></i>
                                            <a href="tel:{{ $tenant->phone }}">{{ $tenant->phone }}</a>
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ $tenant->lease_start_date?->format('M d, Y') }}</div>
                                        <div>→ {{ $tenant->lease_end_date?->format('M d, Y') }}</div>
                                        @if($tenant->lease_status === 'active' && $daysLeft <= 30)
                                            <span class="badge bg-warning text-dark mt-1">
                                                {{ $daysLeft }} days left
                                            </span>
                                        @elseif($tenant->lease_status === 'active' && $daysLeft < 0)
                                            <span class="badge bg-danger mt-1">
                                                {{ abs($daysLeft) }} days overdue
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="text-primary">
                                            ₱{{ number_format($tenant->monthly_rent ?? 0, 2) }}
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $statusColor }} p-2">
                                            {{ $tenant->lease_status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('tenants.show', $tenant) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('tenants.edit', $tenant) }}" 
                                               class="btn btn-sm btn-warning" 
                                               title="Edit Tenant">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" 
                                                    data-bs-toggle="dropdown">
                                                <span class="visually-hidden">Toggle Dropdown</span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('units.show', $tenant->unit) }}">
                                                        <i class="fas fa-door-open me-2"></i>View Unit
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('tenants.maintenance-requests', $tenant) }}">
                                                        <i class="fas fa-tools me-2"></i>Maintenance
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('tenants.destroy', $tenant) }}" method="POST" 
                                                          onsubmit="return confirm('Are you sure you want to delete {{ $tenant->full_name }}?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="fas fa-trash me-2"></i>Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted small">
                        Showing {{ $tenants->firstItem() ?? 0 }} to {{ $tenants->lastItem() ?? 0 }} of {{ $tenants->total() }} tenants
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $tenants->withQueryString()->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="display-1 text-muted mb-4">👥</div>
                    <h3 class="text-gray-800 mb-3">No Tenants Found</h3>
                    <p class="text-muted mb-4">
                        @if(request()->anyFilled(['status', 'unit_id', 'search']))
                            No tenants match your filter criteria.
                            <br>
                            <a href="{{ route('buildings.tenants', $building) }}" class="btn btn-link">Clear all filters</a>
                        @else
                            This building doesn't have any tenants yet.
                        @endif
                    </p>
                    @if(!request()->anyFilled(['status', 'unit_id', 'search']))
                        <a href="{{ route('tenants.create', ['building_id' => $building->id]) }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus-circle me-2"></i>
                            Add Your First Tenant
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Tenant Statistics -->
    @if($tenants->count() > 0)
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-2"></i>
                        Tenant Distribution by Unit Type
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $unitTypeStats = $building->tenants_by_unit_type_stats;
                    @endphp
                    @if(!empty($unitTypeStats))
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless">
                                @foreach($unitTypeStats as $type => $stats)
                                    <tr>
                                        <td>{{ $stats['unit_type_label'] }}</td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                @php
                                                    $percentage = $building->activeTenants->count() > 0 
                                                        ? ($stats['count'] / $building->activeTenants->count()) * 100 
                                                        : 0;
                                                @endphp
                                                <div class="progress-bar bg-primary" 
                                                     role="progressbar" 
                                                     style="width: {{ $percentage }}%"
                                                     aria-valuenow="{{ $percentage }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    {{ $stats['count'] }} tenants
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-primary">{{ round($percentage, 1) }}%</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No tenant data available for statistics.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Lease Expiration Timeline
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $timeline = $building->lease_expiration_timeline;
                    @endphp
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Overdue</span>
                            <span class="badge bg-danger rounded-pill p-2">{{ $timeline['overdue'] }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Expiring this month</span>
                            <span class="badge bg-warning text-dark rounded-pill p-2">{{ $timeline['this_month'] }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Next 30 days</span>
                            <span class="badge bg-warning text-dark rounded-pill p-2">{{ $timeline['next_30_days'] }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>30-60 days</span>
                            <span class="badge bg-info rounded-pill p-2">{{ $timeline['next_60_days'] }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>60-90 days</span>
                            <span class="badge bg-info rounded-pill p-2">{{ $timeline['next_90_days'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    background-color: #4a5568;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
}
.initials {
    color: white;
    font-size: 16px;
    font-weight: 600;
    text-transform: uppercase;
}
.border-left-primary {
    border-left: 4px solid #4a5568;
}
.border-left-success {
    border-left: 4px solid #28a745;
}
.border-left-warning {
    border-left: 4px solid #ffc107;
}
.border-left-info {
    border-left: 4px solid #17a2b8;
}
.border-left-danger {
    border-left: 4px solid #dc3545;
}
.card {
    border-radius: 0.5rem;
}
.table td, .table th {
    vertical-align: middle;
}
.progress {
    background-color: #e9ecef;
    border-radius: 0.25rem;
}
.progress-bar {
    background-color: #4a5568;
}
.list-group-item {
    border: none;
    border-bottom: 1px solid rgba(0,0,0,.125);
}
.list-group-item:last-child {
    border-bottom: none;
}
.badge {
    font-weight: 500;
    padding: 0.5em 1em;
}
</style>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // Confirm delete
    window.confirmDelete = function(tenantName) {
        return confirm(`Are you sure you want to delete ${tenantName}? This action cannot be undone.`);
    };
});
</script>
@endsection