<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Utility Wise - Utilities Management System')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts - Inter with all weights -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* RESET & BASE */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #4b5563;
            background: #f8f9fa;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Typography */
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: #1f2937;
            line-height: 1.2;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        /* Main Navigation */
        .main-nav {
            background: #2c3e50;
            color: white;
            padding: 15px 0;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.4);  /* ← BOLD SHADOW */
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            padding: 0 30px;
        }

        /* Logo & Brand */
        .logo-section {
            display: flex;
            align-items: center;
        }

        .logo-link {
            color: white;
            text-decoration: none;
            font-size: 24px;
            font-weight: 700;
            display: flex;
            align-items: center;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            letter-spacing: -0.02em;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: #3498db;
            border-radius: 8px;
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 20px;
        }

        /* Search Bar */
        .search-container {
            flex-grow: 1;
            margin: 0 40px;
            max-width: 500px;
            position: relative;
        }

        .search-form {
            position: relative;
            width: 100%;
        }

        .search-input {
            width: 100%;
            padding: 10px 20px;
            padding-right: 45px;
            border: none;
            border-radius: 30px;
            font-size: 14px;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            font-weight: 400;
            background: rgba(255,255,255,0.15);
            color: white;
            backdrop-filter: blur(5px);
        }

        .search-input::placeholder {
            color: rgba(255,255,255,0.7);
        }

        .search-button {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255,255,255,0.8);
            cursor: pointer;
            font-size: 16px;
        }

        /* User Menu */
        .user-menu {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .quick-links {
            display: flex;
            gap: 20px;
        }

        .quick-link {
            color: #ecf0f1;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 1rem;
            font-weight: 500;
            padding: 6px 10px;
            border-radius: 30px;
            transition: background 0.2s;
        }

        .quick-link:hover {
            background: rgba(255,255,255,0.1);
            text-decoration: none;
            color: white;
        }

        /* Notifications */
        .notifications {
            position: relative;
        }

        .notifications-link {
            color: #ecf0f1;
            text-decoration: none;
            position: relative;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -8px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* User Dropdown */
        .user-dropdown {
            position: relative;
        }

        .user-dropdown-btn {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 15px 6px 8px;
            border-radius: 40px;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            font-size: 1rem;
            font-weight: 500;
            backdrop-filter: blur(5px);
            transition: background 0.2s;
        }

        .user-dropdown-btn:hover {
            background: rgba(255,255,255,0.2);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background: #3498db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            border: 2px solid rgba(255,255,255,0.5);
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            color: #333;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,.2);
            min-width: 240px;
            z-index: 1000;
            margin-top: 10px;
            overflow: hidden;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-header {
            padding: 16px 20px;
            border-bottom: 1px solid #edf2f7;
        }

        .dropdown-user-name {
            font-weight: 600;
            margin-bottom: 4px;
            font-size: 0.95rem;
            color: #1f2937;
        }

        .dropdown-user-email {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .dropdown-items {
            padding: 8px 0;
        }

        .dropdown-item {
            display: block;
            padding: 10px 20px;
            color: #374151;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: background 0.2s;
        }

        .dropdown-item:hover {
            background: #f3f4f6;
            text-decoration: none;
            color: #374151;
        }

        .dropdown-item span {
            margin-right: 10px;
        }

        .dropdown-divider {
            border-top: 1px solid #edf2f7;
            margin: 8px 0;
        }

        .logout-form {
            margin: 0;
            padding: 0;
        }

        .logout-btn {
            width: 100%;
            text-align: left;
            background: none;
            border: none;
            padding: 10px 20px;
            color: #dc2626;
            cursor: pointer;
            font-size: 0.9rem;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            font-weight: 500;
            transition: background 0.2s;
        }

        .logout-btn:hover {
            background: #f3f4f6;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 73px;
            bottom: 0;
            width: 260px;
            background: #34495e;  /* ← CHANGED FROM #2c3e50 TO #34495e (lighter) */
            color: white;
            z-index: 999;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,.2);
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        /* Sidebar Date & Time Widget */
        .sidebar-datetime {
            padding: 20px 20px 10px 20px;
            border-bottom: 1px solid #34495e;
            margin-bottom: 15px;
            text-align: center;
        }

        .sidebar-date {
            font-size: 13px;
            font-weight: 500;
            color: #ecf0f1;
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }

        .sidebar-time {
            font-size: 16px;
            font-weight: 700;
            color: #3498db;
            font-family: 'Inter', monospace;
            letter-spacing: 1px;
            background: rgba(52, 152, 219, 0.1);
            padding: 8px 12px;
            border-radius: 8px;
            display: inline-block;
            border: 1px solid #34495e;
        }

        .sidebar-menu {
            padding: 0 0 20px 0;
        }

        .sidebar-section {
            margin-bottom: 20px;
        }

        .sidebar-section-title {
            padding: 0 20px;
            margin-bottom: 8px;
            color: #95a5a6;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 700;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: background 0.2s;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .sidebar-link:hover {
            background: #34495e;
            text-decoration: none;
            color: white;
        }

        .sidebar-link.active {
            background: #3498db;
            border-left: 4px solid #f1c40f;
        }

        .sidebar-link span:first-child {
            font-size: 1.2rem;
        }

        /* Sidebar scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 8px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #34495e;  /* ← CHANGED FROM #2c3e50 TO #34495e */
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #34495e;
            border-radius: 4px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #3d566e;
        }

        /* Main Content */
        .main-content {
            margin-top: 73px;
            margin-left: 260px;
            min-height: calc(100vh - 73px);
            background:linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        .page-content {
            padding: 25px 30px;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            width: 100%;
        }

        .container-fluid {
            width: 100%;
            padding: 0 15px;
            margin: 0 auto;
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
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
            font-family: 'Inter', 'Segoe UI', sans-serif;
            font-size: 0.95rem;
            color: #4b5563;
        }

        .toast-success {
            border-left: 4px solid #28a745;
        }

        .toast-error {
            border-left: 4px solid #dc3545;
        }

        .toast-warning {
            border-left: 4px solid #ffc107;
        }

        .toast-info {
            border-left: 4px solid #17a2b8;
        }

        .toast-close {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: #666;
            margin-left: 10px;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Loading Spinner */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(0,0,0,.1);
            border-radius: 50%;
            border-top-color: #3498db;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Form elements */
        input, select, textarea, button {
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        input::placeholder, textarea::placeholder {
            color: #9ca3af;
            font-weight: 400;
            opacity: 0.7;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-container {
                padding: 0 15px;
                flex-wrap: wrap;
            }

            .logo-section {
                width: 100%;
                justify-content: space-between;
                margin-bottom: 10px;
            }

            .search-container {
                margin: 10px 0;
                max-width: 100%;
            }

            .user-menu {
                width: 100%;
                justify-content: space-between;
            }

            .quick-links {
                display: none;
            }

            .sidebar {
                width: 0;
                display: none;
            }

            .main-content {
                margin-left: 0;
            }

            .page-content {
                padding: 20px 15px;
            }
        }
    </style>

    <!-- Page-specific styles -->
    @stack('styles')
</head>
<body>
    <!-- Main Navigation -->
    <nav class="main-nav">
        <div class="nav-container">
            <!-- Logo & Brand -->
            <div class="logo-section">
                <a href="{{ route('dashboard') }}" class="logo-link">
                    <div class="logo-icon">P</div>
                    <span>Property Wise</span>
                </a>
            </div>

            <!-- Search Bar -->
            <div class="search-container">
                <form action="{{ route('search') }}" method="GET" class="search-form">
                    <input type="text" 
                           name="q" 
                           placeholder="Search buildings, tenants, units..." 
                           class="search-input"
                           value="{{ request('q') }}">
                    <button type="submit" class="search-button">🔍</button>
                </form>
            </div>

            <!-- User Menu -->
            <div class="user-menu">
                <!-- Quick Links -->
                <div class="quick-links">
                    <a href="{{ route('dashboard') }}" class="quick-link">
                        <span>📊</span>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('calendar') }}" class="quick-link">
                        <span>📅</span>
                        <span>Calendar</span>
                    </a>
                    <a href="{{ route('map') }}" class="quick-link">
                        <span>🗺️</span>
                        <span>Map</span>
                    </a>
                </div>

                <!-- Notifications -->
                <div class="notifications">
                    <a href="{{ route('notifications') }}" class="notifications-link">
                        🔔
                        @php
                            $unreadCount = \App\Models\Alert::where('is_read', false)->count();
                        @endphp
                        @if($unreadCount > 0)
                            <span class="notification-badge">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        @endif
                    </a>
                </div>

                <!-- User Dropdown -->
                <div class="user-dropdown">
                    <button onclick="toggleUserMenu()" class="user-dropdown-btn">
                        <div class="user-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span>{{ auth()->user()->name }}</span>
                        <span style="font-size: 0.8rem; opacity: 0.8;">▼</span>
                    </button>
                    
                    <div class="dropdown-menu" id="userMenu">
                        <div class="dropdown-header">
                            <div class="dropdown-user-name">{{ auth()->user()->name }}</div>
                            <div class="dropdown-user-email">{{ auth()->user()->email }}</div>
                        </div>
                        
                        <div class="dropdown-items">
                            <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                <span>👤</span> My Profile
                            </a>
                            <a href="{{ route('profile.notifications') }}" class="dropdown-item">
                                <span>🔔</span> Notifications
                            </a>
                            <a href="{{ route('settings.index') }}" class="dropdown-item">
                                <span>⚙️</span> Settings
                            </a>
                            <a href="{{ route('help') }}" class="dropdown-item">
                                <span>❓</span> Help
                            </a>
                        </div>
                        
                        <div class="dropdown-divider"></div>
                        
                        <div class="dropdown-items">
                            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                                @csrf
                                <button type="submit" class="logout-btn">
                                    <span>🚪</span> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Date & Time Widget -->
        <div class="sidebar-datetime">
            <div class="sidebar-date" id="sidebarDate">{{ now()->format('l, F j, Y') }}</div>
            <div class="sidebar-time" id="sidebarTime"></div>
        </div>

        <div class="sidebar-menu">
            <!-- Core Section -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">Core</div>
                <a href="{{ route('buildings.index') }}" class="sidebar-link {{ request()->routeIs('buildings.*') ? 'active' : '' }}">
                    <span>🏢</span> Buildings
                </a>
                <a href="{{ route('units.index') }}" class="sidebar-link {{ request()->routeIs('units.*') ? 'active' : '' }}">
                    <span>🏠</span> Units
                </a>
                <a href="{{ route('tenants.index') }}" class="sidebar-link {{ request()->routeIs('tenants.*') ? 'active' : '' }}">
                    <span>👥</span> Tenants
                </a>
            </div>

            <!-- Billing Section -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">Billing</div>
                <a href="{{ route('leases.index') }}" class="sidebar-link {{ request()->routeIs('leases.*') ? 'active' : '' }}">
                    <span>📄</span> Leases
                </a>
                <a href="{{ route('bills.index') }}" class="sidebar-link {{ request()->routeIs('bills.*') ? 'active' : '' }}">
                    <span>💰</span> Bills
                </a>
            </div>

            <!-- Maintenance Section -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">Maintenance</div>
                <a href="{{ route('maintenance-requests.index') }}" class="sidebar-link {{ request()->routeIs('maintenance-requests.*') ? 'active' : '' }}">
                    <span>🔧</span> Maintenance
                </a>
                <a href="{{ route('preventive-maintenances.index') }}" class="sidebar-link {{ request()->routeIs('preventive-maintenances.*') ? 'active' : '' }}">
                    <span>🛠️</span> Preventive
                </a>
                <a href="{{ route('vendors.index') }}" class="sidebar-link {{ request()->routeIs('vendors.*') ? 'active' : '' }}">
                    <span>👷</span> Vendors
                </a>
            </div>

            <!-- Utilities Section -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">Utilities</div>
                <a href="{{ route('meter-readings.index') }}" class="sidebar-link {{ request()->routeIs('meter-readings.*') ? 'active' : '' }}">
                    <span>📊</span> Meter Readings
                </a>
                <a href="{{ route('consumptions.index') }}" class="sidebar-link {{ request()->routeIs('consumptions.*') ? 'active' : '' }}">
                    <span>⚡</span> Consumptions
                </a>
            </div>

            <!-- Reports Section -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">Reports</div>
                <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <span>📈</span> Reports
                </a>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container"></div>

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Global Utilities Script -->
    <script>
        // Toggle user dropdown
        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('userMenu');
            const button = event.target.closest('.user-dropdown-btn');
            
            if (menu && menu.classList.contains('show') && !menu.contains(event.target) && !button) {
                menu.classList.remove('show');
            }
        });

        // Update sidebar clock
        function updateSidebarClock() {
            const now = new Date();
            const timeElement = document.getElementById('sidebarTime');
            const dateElement = document.getElementById('sidebarDate');
            
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
            
            // Update date every minute to ensure it's accurate
            if (dateElement) {
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                dateElement.textContent = now.toLocaleDateString('en-US', options);
            }
        }

        // Toast notification system
        window.Utilities = {
            showToast: function(message, type = 'success') {
                const container = document.querySelector('.toast-container');
                
                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;
                toast.innerHTML = `
                    <div style="flex-grow: 1;">${message}</div>
                    <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
                `;

                container.appendChild(toast);

                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.remove();
                    }
                }, 5000);
            },

            formatCurrency: function(amount) {
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD'
                }).format(amount);
            },

            formatDate: function(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            }
        };

        // Auto-show toasts for session messages
        @if(session('success'))
            document.addEventListener('DOMContentLoaded', function() {
                Utilities.showToast('{{ session("success") }}', 'success');
            });
        @endif
        
        @if(session('error'))
            document.addEventListener('DOMContentLoaded', function() {
                Utilities.showToast('{{ session("error") }}', 'error');
            });
        @endif

        @if(session('warning'))
            document.addEventListener('DOMContentLoaded', function() {
                Utilities.showToast('{{ session("warning") }}', 'warning');
            });
        @endif

        @if(session('info'))
            document.addEventListener('DOMContentLoaded', function() {
                Utilities.showToast('{{ session("info") }}', 'info');
            });
        @endif

        // Set active sidebar link based on current URL
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const sidebarLinks = document.querySelectorAll('.sidebar-link');
            
            sidebarLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }
            });

            // Start the clock
            updateSidebarClock();
            setInterval(updateSidebarClock, 1000);
        });
    </script>

    <!-- Page-specific scripts -->
    @stack('scripts')
</body>
</html>