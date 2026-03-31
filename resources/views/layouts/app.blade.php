<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PROPMANAGE | System')</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --bg-main: #121212;
            --bg-sidebar: #181818;
            --bg-card: #1f1f1f;
            --accent-green: #10b981;
            --accent-blue: #3b82f6;
            --text-primary: #f1f5f9;
            --text-muted: #94a3b8;
            --border: #2d2d2d;
            --sidebar-width: 260px;
            --header-height: 70px;
        }

        /* RESET & BASE */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            height: 100%;
            background-color: var(--bg-main);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            overflow: hidden;
        }

        .app-container {
            display: flex;
            height: 100vh;
            width: 100vw;
        }

        /* SIDEBAR */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--bg-sidebar);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            flex-shrink: 0;
        }

        .brand-section {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        /* --- SHARED INTRICATE BOX DESIGN --- */
        .intricate-box {
            position: relative;
            width: 38px; 
            height: 38px;
            background: #111111;
            border: 1.5px solid var(--accent-green);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.15);
            flex-shrink: 0;
            overflow: hidden;
            transition: transform 0.2s ease;
        }

        .intricate-box .svg-icon {
            width: 75%; /* Restored for original logo complexity */
            height: 75%;
            fill: none;
            stroke: var(--accent-green);
            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-linejoin: round;
            filter: drop-shadow(0 0 2px rgba(16, 185, 129, 0.5));
        }

        /* Adjustments for Lucide icons inside intricate boxes */
        .user-dropdown-btn .intricate-box .svg-icon-lucide {
            width: 55%;
            height: 55%;
            stroke-width: 2;
            stroke: var(--accent-green);
        }

        .reflection {
            position: absolute;
            top: -100%;
            left: -100%;
            width: 300%;
            height: 300%;
            background: linear-gradient(
                135deg,
                rgba(16, 185, 129, 0) 0%,
                rgba(16, 185, 129, 0.05) 40%,
                rgba(255, 255, 255, 0.1) 50%,
                rgba(16, 185, 129, 0.05) 60%,
                rgba(16, 185, 129, 0) 100%
            );
            transform: rotate(25deg);
            z-index: 10;
        }

        .brand-link:hover .intricate-box .reflection,
        .user-dropdown-btn:hover .intricate-box .reflection {
            animation: glossSwipe 1.2s ease forwards;
        }

        @keyframes glossSwipe {
            0% { top: -100%; left: -100%; }
            100% { top: 100%; left: 100%; }
        }

        .brand-link {
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-text {
            font-size: 1.1rem;
            letter-spacing: 0.5px;
            display: flex;
            align-items: baseline;
        }

        .brand-text .prop { color: #ffffff; font-weight: 700; }
        .brand-text .manage { color: var(--accent-green); font-weight: 500; }

        /* SIDEBAR MENU */
        .sidebar-menu {
            flex-grow: 1;
            overflow-y: auto;
            padding: 1.5rem 0.75rem;
        }

        .sidebar-section-title {
            padding: 0 1rem;
            margin: 1.5rem 0 0.75rem 0;
            color: var(--text-muted);
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 1rem;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 4px;
            position: relative;
            overflow: hidden;
        }

        .nav-icon {
            width: 18px;
            height: 18px;
            stroke-width: 1.5px;
            transition: all 0.3s ease;
        }

        .sidebar-link:hover {
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.12) 0%, rgba(16, 185, 129, 0.02) 100%);
            color: var(--text-primary);
        }

        .sidebar-link:hover .nav-icon {
            color: var(--text-primary);
        }

        .sidebar-link.active {
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.2) 0%, rgba(16, 185, 129, 0.05) 100%);
            color: var(--accent-green);
            font-weight: 600;
        }

        .sidebar-link.active .nav-icon {
            color: var(--accent-green);
            filter: drop-shadow(0 0 5px rgba(16, 185, 129, 0.4));
        }

        /* MAIN WRAPPER */
        .main-wrapper {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        /* TOP NAVIGATION & SEARCH */
        .top-nav {
            height: var(--header-height);
            background-color: var(--bg-sidebar);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
        }

        .search-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-icon {
            position: absolute;
            left: 14px;
            width: 16px;
            height: 16px;
            color: var(--text-muted);
            pointer-events: none;
            stroke-width: 2px;
        }

        .search-input {
            background: var(--bg-main);
            border: 1px solid var(--border);
            color: white;
            padding: 10px 16px 10px 40px;
            border-radius: 20px;
            width: 320px;
            font-size: 0.85rem;
            transition: 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--accent-green);
            width: 400px;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .top-nav-actions {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        /* USER DROPDOWN */
        .user-dropdown { position: relative; }

        .user-dropdown-btn {
            background: none;
            border: none;
            color: var(--text-primary);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 4px;
            border-radius: 10px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .user-name {
            font-size: 0.9rem;
            font-weight: 600;
        }

        .user-role {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 55px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            width: 220px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.6);
            z-index: 1001;
        }

        .dropdown-menu.show { display: block; animation: menuFade 0.2s ease; }

        @keyframes menuFade {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dropdown-item {
            padding: 12px 20px;
            color: var(--text-primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
        }

        .dropdown-item:hover { background: rgba(16, 185, 129, 0.1); color: var(--accent-green); }

        /* CONTENT AREA */
        .content-area {
            flex-grow: 1;
            overflow-y: auto;
            padding: 2rem;
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            background: var(--bg-card);
            border-left: 4px solid var(--accent-green);
            color: white;
            padding: 16px 20px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
            display: flex;
            justify-content: space-between;
            min-width: 300px;
        }

        @media (max-width: 768px) {
            .sidebar { display: none; }
        }
    </style>
    @stack('styles')
</head>
<body>

    <div class="app-container">
        <aside class="sidebar">
            <div class="brand-section">
                <a href="{{ route('dashboard') }}" class="brand-link">
                    <div class="intricate-box">
                        <svg class="svg-icon" viewBox="0 0 100 100">
                            <path d="M25,20 L25,80 M25,20 Q50,5 75,20 Q100,35 75,50 L25,50 M50,20 L50,80" />
                            <path d="M25,60 L40,80 L55,60 L70,80 L85,60" />
                            <path d="M70,80 L90,80 M80,80 L80,75 M85,80 L85,75 M75,80 L75,75" />
                            <circle cx="50" cy="35" r="4" fill="var(--accent-green)" stroke="none"/>
                        </svg>
                        <div class="reflection"></div>
                    </div>
                    
                    <div class="brand-text">
                        <span class="prop">PROP</span>
                        <span class="manage">MANAGE</span>
                    </div>
                </a>
            </div>

            <div class="sidebar-menu">
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i data-lucide="layout-dashboard" class="nav-icon"></i> Dashboard
                </a>

                <div class="sidebar-section-title">Core Management</div>
                <a href="{{ route('buildings.index') }}" class="sidebar-link {{ request()->routeIs('buildings.*') ? 'active' : '' }}">
                    <i data-lucide="building-2" class="nav-icon"></i> Buildings
                </a>
                <a href="{{ route('units.index') }}" class="sidebar-link {{ request()->routeIs('units.*') ? 'active' : '' }}">
                    <i data-lucide="door-open" class="nav-icon"></i> Units
                </a>
                <a href="{{ route('tenants.index') }}" class="sidebar-link {{ request()->routeIs('tenants.*') ? 'active' : '' }}">
                    <i data-lucide="users" class="nav-icon"></i> Tenants
                </a>

                <div class="sidebar-section-title">Operations</div>
                <a href="{{ route('leases.index') }}" class="sidebar-link {{ request()->routeIs('leases.*') ? 'active' : '' }}">
                    <i data-lucide="scroll-text" class="nav-icon"></i> Leases
                </a>
                <a href="{{ route('maintenance-requests.index') }}" class="sidebar-link {{ request()->routeIs('maintenance-requests.*') ? 'active' : '' }}">
                    <i data-lucide="wrench" class="nav-icon"></i> Maintenance
                </a>
                <a href="{{ route('bills.index') }}" class="sidebar-link {{ request()->routeIs('bills.*') ? 'active' : '' }}">
                    <i data-lucide="wallet" class="nav-icon"></i> Accounting
                </a>

                <div class="sidebar-section-title">Utilities</div>
                <a href="{{ route('meter-readings.index') }}" class="sidebar-link {{ request()->routeIs('meter-readings.*') ? 'active' : '' }}">
                    <i data-lucide="gauge" class="nav-icon"></i> Meter Readings
                </a>
                <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i data-lucide="bar-chart-3" class="nav-icon"></i> Reports
                </a>
                <a href="{{ route('settings.index') }}" class="sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i data-lucide="settings-2" class="nav-icon"></i> Settings
                </a>
            </div>
        </aside>

        <div class="main-wrapper">
            <header class="top-nav">
                <div class="search-container">
                    <i data-lucide="search" class="search-icon"></i>
                    <form action="{{ route('search') }}" method="GET">
                        <input type="text" name="q" placeholder="Global search properties, tenants, leases..." class="search-input" value="{{ request('q') }}">
                    </form>
                </div>

                <div class="top-nav-actions">
                    <div class="notifications-icon" style="cursor:pointer; color: var(--text-muted);">
                        <i data-lucide="bell" style="width: 20px; height: 20px; stroke-width: 1.5px;"></i>
                    </div>
                    <div class="mail-icon" style="cursor:pointer; color: var(--text-muted);">
                        <i data-lucide="mail" style="width: 20px; height: 20px; stroke-width: 1.5px;"></i>
                    </div>
                    
                    <div class="user-dropdown">
                        <button onclick="toggleUserMenu()" class="user-dropdown-btn">
                            <div class="user-info">
                                <span class="user-name">{{ auth()->user()->name }}</span>
                                <span class="user-role">Administrator</span>
                            </div>
                            <div class="intricate-box">
                                <i data-lucide="user" class="svg-icon-lucide"></i>
                                <div class="reflection"></div>
                            </div>
                        </button>
                        
                        <div class="dropdown-menu" id="userMenu">
                            <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                <i data-lucide="user-cog" style="width: 16px;"></i> Profile
                            </a>
                            <a href="{{ route('settings.index') }}" class="dropdown-item">
                                <i data-lucide="settings" style="width: 16px;"></i> Settings
                            </a>
                            <hr style="border: 0; border-top: 1px solid var(--border); margin: 5px 0;">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item" style="width:100%; text-align:left; background:none; border:none; color:#f87171; cursor:pointer;">
                                    <i data-lucide="log-out" style="width: 16px; stroke: #f87171;"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="content-area">
                @yield('content')
            </main>
        </div>
    </div>

    <div class="toast-container"></div>

    <script>
        lucide.createIcons();

        function toggleUserMenu() {
            document.getElementById('userMenu').classList.toggle('show');
        }

        window.onclick = function(event) {
            if (!event.target.closest('.user-dropdown')) {
                const menu = document.getElementById('userMenu');
                if (menu) menu.classList.remove('show');
            }
        }

        const showToast = (msg, type = 'success') => {
            const container = document.querySelector('.toast-container');
            const toast = document.createElement('div');
            toast.className = 'toast';
            if(type === 'error') toast.style.borderLeftColor = '#ef4444';
            toast.innerHTML = `<span>${msg}</span>`;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        };

        @if(session('success')) showToast("{{ session('success') }}"); @endif
        @if(session('error')) showToast("{{ session('error') }}", 'error'); @endif
    </script>
    @stack('scripts')
</body>
</html>