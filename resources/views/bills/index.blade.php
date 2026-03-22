<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>💰 Bills Management</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    
    <style>
        /* RESET & BASE - Same as your buildings page */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            background: #f8f9fa;
        }

        body {
            display: block;
            min-height: 100vh;
            overflow-y: auto;
        }

        main {
            background: white;
            padding: 30px 0;
            min-height: calc(100vh - 200px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            width: 100%;
        }

        /* PAGE HEADER */
        .page-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-subtitle {
            color: #6c757d;
            margin-top: 5px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,.1);
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,.15);
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            display: block;
            margin-bottom: 5px;
        }

        .stat-trend {
            font-size: 12px;
            margin-top: 5px;
        }

        .trend-up {
            color: #28a745;
        }

        .trend-down {
            color: #dc3545;
        }
        
        .stat-label {
            font-size: 13px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        /* Bills Grid */
        .bills-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        /* Bill Card Styles */
        .bill-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,.1);
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
            position: relative;
            height: 100%;
            min-height: 280px;
            animation: fadeIn 0.3s ease forwards;
            display: flex;
            flex-direction: column;
        }

        .bill-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,.15);
        }

        /* Bill Status Header */
        .bill-status-header {
            padding: 12px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e9ecef;
        }

        .bill-number {
            font-weight: 600;
            color: #2c3e50;
            font-size: 13px;
        }

        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-partial {
            background: #cce5ff;
            color: #004085;
            border: 1px solid #b8daff;
        }

        .status-paid {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-overdue {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-void {
            background: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }

        /* Bill Card Content */
        .bill-card-content {
            padding: 15px;
            flex: 1;
        }

        .tenant-info {
            margin-bottom: 12px;
        }

        .tenant-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 15px;
            margin-bottom: 3px;
        }

        .unit-info {
            color: #6c757d;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .bill-period {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #e9ecef;
        }

        /* Amount Styles */
        .amount-section {
            margin-bottom: 12px;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
            font-size: 13px;
        }

        .amount-label {
            color: #6c757d;
        }

        .amount-value {
            font-weight: 600;
            color: #2c3e50;
        }

        .total-due {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 2px solid #e9ecef;
            font-size: 16px;
        }

        .total-due .amount-value {
            color: #2c3e50;
            font-size: 18px;
        }

        /* Due Date */
        .due-date {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            padding: 8px 0;
            margin-bottom: 10px;
            color: #6c757d;
        }

        .due-date.overdue {
            color: #dc3545;
            font-weight: 600;
        }

        /* Card Actions */
        .bill-card-actions {
            display: flex;
            gap: 6px;
            padding: 15px;
            border-top: 1px solid #e9ecef;
            background: #f8f9fa;
        }

        .bill-card-actions .btn-sm {
            flex: 1;
            height: 32px;
            font-size: 11px;
            padding: 0 8px;
            min-width: 0;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,.1);
            border: 1px solid #dee2e6;
            margin-bottom: 30px;
            padding: 20px;
        }

        .filter-header {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .filter-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-title span {
            font-size: 18px;
        }

        .filter-title h3 {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .filter-form {
            flex: 1;
            min-width: 250px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-select, .filter-input {
            padding: 10px 15px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 14px;
            color: #2c3e50;
            background: white;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
            min-width: 150px;
        }

        .filter-input {
            cursor: text;
        }

        .filter-select:hover, .filter-input:hover {
            border-color: #4a5568;
        }

        .filter-select:focus, .filter-input:focus {
            outline: none;
            border-color: #4a5568;
            box-shadow: 0 0 0 3px rgba(74,85,104,0.1);
        }

        .clear-filter-btn {
            padding: 10px 20px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            color: #6c757d;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .clear-filter-btn:hover {
            background: #e9ecef;
            color: #2c3e50;
        }

        .generate-bill-btn {
            padding: 10px 20px;
            background: #28a745;
            border: 1px solid #28a745;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .generate-bill-btn:hover {
            background: #218838;
            border-color: #218838;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,.1);
        }

        /* BUTTONS */
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            height: 32px;
            text-decoration: none;
            white-space: nowrap;
            font-weight: 500;
            line-height: 1;
        }

        .btn-primary {
            background: #4a5568;
            color: white;
        }

        .btn-primary:hover {
            background: #2d3748;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 25px;
            font-size: 14px;
            border: 1px solid;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-color: #ffeaa7;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }

        /* Empty State */
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin: 20px;
            background: white;
        }

        .no-data h3 {
            margin-bottom: 10px;
            font-weight: 600;
            color: #2c3e50;
        }

        .no-data p {
            margin-bottom: 20px;
            color: #6c757d;
        }

        /* Pagination */
        .pagination-container {
            margin-top: 30px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,.1);
            border: 1px solid #dee2e6;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            list-style: none;
        }

        .pagination li a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 6px;
            background: #f8f9fa;
            color: #495057;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .pagination li a:hover {
            background: #4a5568;
            color: white;
            border-color: #4a5568;
        }

        .pagination li.active a {
            background: #4a5568;
            color: white;
            border-color: #4a5568;
        }

        /* FOOTER */
        footer {
            background: #333;
            color: white;
            padding: 20px 0;
            width: 100%;
            position: relative;
            bottom: 0;
            left: 0;
            right: 0;
            margin-top: 40px;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 15px;
        }

        .footer-links a {
            color: #aaa;
            margin-right: 15px;
            text-decoration: none;
        }

        .footer-links a:hover {
            color: white;
        }

        /* Delete form styling */
        .delete-form {
            margin: 0;
            padding: 0;
            display: flex;
            flex: 1;
        }

        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            justify-content: flex-end;
        }

        .quick-action-btn {
            padding: 8px 16px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            color: #495057;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s ease;
        }

        .quick-action-btn:hover {
            background: #e9ecef;
            color: #2c3e50;
        }

        /* Responsive */
        @media (max-width: 1400px) {
            .bills-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 1100px) {
            .bills-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            main {
                padding: 20px 0;
                min-height: calc(100vh - 250px);
            }

            .container {
                padding: 0 15px;
            }

            .bills-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .filter-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-form {
                width: 100%;
                flex-direction: column;
            }
            
            .filter-select, .filter-input {
                width: 100%;
            }
            
            .footer-container {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .footer-links a {
                margin: 0 8px;
                display: inline-block;
            }

            .bill-card-actions {
                flex-direction: row;
                gap: 6px;
            }

            .bill-card-actions .btn-sm {
                width: 100%;
                height: 32px;
                font-size: 12px;
                padding: 6px 12px;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .bill-card {
                margin: 0 5px;
            }

            .bill-card-actions {
                flex-direction: row;
                gap: 6px;
            }

            .bill-card-actions .btn-sm {
                height: 32px;
            }
        }
    </style>
</head>
<body>
    {{-- Include the navigation bar --}}
    @auth
        @include('layouts.navigation')
    @endauth

    <main>
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div>
                        <h1 class="page-title">💰 Bills & Invoices</h1>
                        <p class="page-subtitle">Manage tenant billing and track payments</p>
                    </div>
                </div>
            </div>

            <!-- Statistics Overview (with Peso Sign) -->
            <div class="stats-grid">
                @php
                    $totalOutstanding = $bills->whereIn('status', ['pending', 'overdue'])->sum('total_due');
                    $totalCollected = $bills->where('status', 'paid')->sum('total_due');
                    $overdueCount = $bills->where('status', 'overdue')->count();
                    $averageBill = $bills->avg('total_amount');
                @endphp
                
                <div class="stat-card">
                    <div class="stat-value">₱{{ number_format($totalOutstanding, 2) }}</div>
                    <div class="stat-label">Total Outstanding</div>
                    <div class="stat-trend">
                        <span class="{{ $overdueCount > 0 ? 'trend-down' : 'trend-up' }}">
                            {{ $overdueCount }} overdue bills
                        </span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">₱{{ number_format($totalCollected, 2) }}</div>
                    <div class="stat-label">Total Collected</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $bills->total() }}</div>
                    <div class="stat-label">Total Bills</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">₱{{ number_format($averageBill, 2) }}</div>
                    <div class="stat-label">Average Bill Amount</div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning">
                    {{ session('warning') }}
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="{{ route('bills.overdue') }}" class="quick-action-btn">
                    ⚠️ View Overdue ({{ $overdueCount }})
                </a>
                <a href="{{ route('bills.generate') }}" class="quick-action-btn">
                    ➕ Generate Monthly Bills
                </a>
            </div>

            <!-- Filter Section -->
            <div class="filter-section {{ request()->anyFilled(['status', 'search', 'date_from', 'date_to']) ? 'active-filter' : '' }}">
                <div class="filter-header">
                    <div class="filter-title">
                        <span>🔍</span>
                        <h3>Filter Bills</h3>
                    </div>
                    
                    <div class="filter-form">
                        <form action="{{ route('bills.index') }}" method="GET" style="display: flex; gap: 10px; width: 100%; flex-wrap: wrap;">
                            <input type="text" name="search" placeholder="Search by tenant or unit..." 
                                   value="{{ request('search') }}" class="filter-input" style="flex: 2;">
                            
                            <select name="status" class="filter-select" style="flex: 1;">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                <option value="void" {{ request('status') == 'void' ? 'selected' : '' }}>Void</option>
                            </select>
                            
                            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                                   class="filter-input" placeholder="From date" style="flex: 1;">
                            
                            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                                   class="filter-input" placeholder="To date" style="flex: 1;">
                            
                            <button type="submit" class="btn-sm btn-primary">Apply Filters</button>
                            
                            @if(request()->anyFilled(['search', 'status', 'date_from', 'date_to']))
                                <a href="{{ route('bills.index') }}" class="clear-filter-btn">
                                    ✕ Clear
                                </a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            <!-- Bills Content -->
            @if($bills->count() > 0)
                <!-- Bills Grid (Table view removed) -->
                <div class="bills-grid">
                    @foreach($bills as $index => $bill)
                    <div class="bill-card" style="animation-delay: {{ $index * 0.05 }}s">
                        <!-- Status Header -->
                        <div class="bill-status-header">
                            <span class="bill-number">{{ $bill->bill_number }}</span>
                            @php
                                $statusLabels = [
                                    'pending' => 'Pending',
                                    'partial' => 'Partial',
                                    'paid' => 'Paid',
                                    'overdue' => 'Overdue',
                                    'void' => 'Void'
                                ];
                            @endphp
                            <span class="status-badge status-{{ $bill->status }}">
                                {{ $statusLabels[$bill->status] ?? $bill->status }}
                            </span>
                        </div>
                        
                        <!-- Card Content -->
                        <div class="bill-card-content">
                            <!-- Tenant Info -->
                            <div class="tenant-info">
                                <div class="tenant-name">
                                    {{ $bill->lease->tenant->first_name ?? '' }} {{ $bill->lease->tenant->last_name ?? 'Unknown' }}
                                </div>
                                <div class="unit-info">
                                    <span>🏢</span>
                                    <span>Unit {{ $bill->lease->unit->unit_number ?? 'N/A' }} - Building {{ $bill->lease->unit->building->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                            
                            <!-- Bill Period -->
                            <div class="bill-period">
                                📅 {{ $bill->period_start->format('M d, Y') }} - {{ $bill->period_end->format('M d, Y') }}
                            </div>
                            
                            <!-- Amount Section (with Peso Sign) -->
                            <div class="amount-section">
                                <div class="amount-row">
                                    <span class="amount-label">Subtotal:</span>
                                    <span class="amount-value">₱{{ number_format($bill->total_amount, 2) }}</span>
                                </div>
                                <div class="amount-row">
                                    <span class="amount-label">Tax:</span>
                                    <span class="amount-value">₱{{ number_format($bill->total_tax, 2) }}</span>
                                </div>
                                <div class="amount-row total-due">
                                    <span class="amount-label">Total Due:</span>
                                    <span class="amount-value">₱{{ number_format($bill->total_due, 2) }}</span>
                                </div>
                            </div>
                            
                            <!-- Due Date -->
                            @php
                                $isOverdue = $bill->status == 'overdue' || ($bill->status == 'pending' && $bill->due_date < now());
                            @endphp
                            <div class="due-date {{ $isOverdue ? 'overdue' : '' }}">
                                <span>⏰</span>
                                <span>Due: {{ $bill->due_date->format('M d, Y') }}</span>
                                @if($bill->paid_date)
                                    <span style="margin-left: auto;">✅ Paid: {{ $bill->paid_date->format('M d, Y') }}</span>
                                @endif
                            </div>
                            
                            <!-- Late Fee if applicable -->
                            @if($bill->late_fee > 0)
                                <div style="color: #dc3545; font-size: 11px; margin-top: 5px;">
                                    ⚠️ Late fee: ₱{{ number_format($bill->late_fee, 2) }}
                                </div>
                            @endif
                        </div>
                        
                        <!-- Card Actions -->
                        <div class="bill-card-actions">
                            <a href="{{ route('bills.show', $bill) }}" 
                               class="btn-sm btn-primary">
                                👁️ View
                            </a>
                            @if($bill->status != 'paid' && $bill->status != 'void')
                                <a href="{{ route('bills.edit', $bill) }}" 
                                   class="btn-sm" style="background: #718096; color: white;">
                                    ✏️ Edit
                                </a>
                                <a href="{{ route('bills.payments.create', $bill) }}" 
                                   class="btn-sm btn-success">
                                    💰 Pay
                                </a>
                            @endif
                            @if($bill->status != 'paid')
                                <form action="{{ route('bills.destroy', $bill) }}" method="POST" class="delete-form" 
                                      onsubmit="return confirmDelete(this, '{{ $bill->bill_number }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn-sm btn-danger">
                                        🗑️
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                @if($bills->hasPages())
                    <div class="pagination-container">
                        {{ $bills->withQueryString()->links() }}
                    </div>
                @endif
            @else
                <div class="no-data">
                    <div style="font-size: 48px; margin-bottom: 15px;">💰</div>
                    <h3>No bills found</h3>
                    <p>Start by generating bills for your tenants.</p>
                    <div style="display: flex; gap: 10px; justify-content: center; margin-top: 20px;">
                        <a href="{{ route('bills.generate') }}" class="btn-sm btn-primary" style="padding: 12px 24px;">
                            ➕ Generate Monthly Bills
                        </a>
                        <a href="{{ route('leases.index') }}" class="btn-sm" style="background: #718096; color: white; padding: 12px 24px;">
                            📋 View Active Leases
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div>
                &copy; {{ date('Y') }} Utility Wise. All rights reserved.
            </div>
            <div class="footer-links">
                <a href="#">Help</a>
                <a href="#">Docs</a>
                <a href="#">Support</a>
            </div>
        </div>
    </footer>

    <script>
        // Auto-hide alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });

        // Toast notification
        window.showToast = function(message, type = 'success') {
            let container = document.querySelector('.toast-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'toast-container';
                container.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                `;
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.style.cssText = `
                background: white;
                border-radius: 4px;
                padding: 15px 20px;
                margin-bottom: 10px;
                box-shadow: 0 4px 12px rgba(0,0,0,.15);
                display: flex;
                align-items: center;
                min-width: 300px;
                max-width: 400px;
                animation: slideIn 0.3s ease;
                border-left: 4px solid ${type === 'success' ? '#28a745' : type === 'warning' ? '#ffc107' : '#dc3545'};
            `;
            
            toast.innerHTML = `
                <div style="flex-grow: 1;">${message}</div>
                <button onclick="this.parentElement.remove()" style="background: none; border: none; cursor: pointer; font-size: 18px; color: #666;">&times;</button>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                if (toast.parentElement) toast.remove();
            }, 5000);
        };

        // Confirm dialog
        function showConfirmDialog(message, title = 'Confirm Action') {
            return new Promise((resolve) => {
                const dialog = document.createElement('div');
                dialog.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 10000;
                `;
                
                const dialogContent = document.createElement('div');
                dialogContent.style.cssText = `
                    background: white;
                    border-radius: 8px;
                    padding: 25px;
                    width: 90%;
                    max-width: 350px;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                    text-align: center;
                `;
                
                dialogContent.innerHTML = `
                    <div style="margin-bottom: 20px;">
                        <div style="font-size: 42px; color: #ffc107; margin-bottom: 12px;">⚠️</div>
                        <h3 style="color: #2c3e50; margin-bottom: 8px;">${title}</h3>
                        <p style="color: #6c757d;">${message}</p>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button id="confirmCancel" style="background: #718096; color: white; border: none; padding: 9px 18px; border-radius: 4px; cursor: pointer; flex: 1;">Cancel</button>
                        <button id="confirmDelete" style="background: #2d3748; color: white; border: none; padding: 9px 18px; border-radius: 4px; cursor: pointer; flex: 1;">Delete</button>
                    </div>
                `;
                
                dialog.appendChild(dialogContent);
                document.body.appendChild(dialog);
                document.body.style.overflow = 'hidden';
                
                dialog.querySelector('#confirmCancel').onclick = () => {
                    document.body.removeChild(dialog);
                    document.body.style.overflow = 'auto';
                    resolve(false);
                };
                
                dialog.querySelector('#confirmDelete').onclick = () => {
                    document.body.removeChild(dialog);
                    document.body.style.overflow = 'auto';
                    resolve(true);
                };
            });
        }

        // Delete confirmation
        window.confirmDelete = async function(form, billNumber) {
            event.preventDefault();
            
            const confirmed = await showConfirmDialog(
                `Delete Bill "${billNumber}"? This cannot be undone.`,
                'Delete Bill'
            );
            
            if (confirmed) {
                const button = form.querySelector('button[type="submit"]');
                button.innerHTML = '⏳';
                button.disabled = true;
                form.submit();
            }
            
            return false;
        }

        // Add slide animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);

        // Show session messages as toasts
        @if(session('success'))
            document.addEventListener('DOMContentLoaded', function() {
                showToast('{{ session("success") }}', 'success');
            });
        @endif
        
        @if(session('error'))
            document.addEventListener('DOMContentLoaded', function() {
                showToast('{{ session("error") }}', 'error');
            });
        @endif

        @if(session('warning'))
            document.addEventListener('DOMContentLoaded', function() {
                showToast('{{ session("warning") }}', 'warning');
            });
        @endif
    </script>
</body>
</html>