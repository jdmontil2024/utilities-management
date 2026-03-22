<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Property Management System') }}</title>
    <!-- Fonts - Inter with all weights (exact match from main layout) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #4b5563;
            background: #ffffff;
            font-size: 14px;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: #1f2937;
            line-height: 1.2;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        /* Navigation - full width spread */
        .navbar {
            background: #2c3e50;
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
            position: sticky;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .navbar .container {
            max-width: 1400px;  /* increased from 1200px */
            margin: 0 auto;
            padding: 0 40px;    /* slightly larger padding */
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Inter', sans-serif;
            letter-spacing: -0.02em;
        }

        .logo-icon {
            background: #3498db;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 700;
        }

        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-links a {
            color: #ecf0f1;
            text-decoration: none;
            padding: 6px 15px;
            border-radius: 30px;
            transition: background 0.2s;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .nav-links a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .btn {
            background: #3498db;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            font-family: 'Inter', sans-serif;
            transition: background 0.2s;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
        }

        .btn:hover {
            background: #2c3e50;
        }

        /* Hero Section - full width spread */
        .hero {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 100px 0;
            text-align: center;
            width: 100%;
        }

        .hero .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            width: 100%;
        }

        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
            color: white;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .hero p {
            font-size: 20px;
            max-width: 800px;  /* increased for better spread */
            margin: 0 auto 30px;
            opacity: 0.9;
            font-weight: 400;
        }

        /* Stats Section - full width */
        .stats {
            background: #f8f9fa;
            padding: 80px 0;
            width: 100%;
        }

        .container {
            max-width: 1400px;  /* increased from 1200px */
            margin: 0 auto;
            padding: 0 40px;    /* increased padding */
            width: 100%;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title h2 {
            font-size: 36px;
            color: #1f2937;
            margin-bottom: 15px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .section-title p {
            color: #4b5563;
            max-width: 800px;  /* increased */
            margin: 0 auto;
            font-size: 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);  /* fixed 4 columns for even spread */
            gap: 30px;
            margin-top: 40px;
            width: 100%;
        }

        .stat-card {
            background: white;
            padding: 30px 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
            font-family: 'Inter', sans-serif;
            width: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
            letter-spacing: -0.02em;
        }

        .stat-label {
            color: #6b7280;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 600;
        }

        /* Features Section - full width */
        .features {
            padding: 80px 0;
            background: white;
            width: 100%;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);  /* 3 equal columns */
            gap: 30px;
            width: 100%;
        }

        .feature-card {
            padding: 30px;
            border-radius: 12px;
            background: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.2s;
            border: 1px solid #edf2f7;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .feature-card:hover {
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        }

        .feature-icon {
            font-size: 40px;
            margin-bottom: 20px;
            color: #3498db;
        }

        .feature-card h3 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #1f2937;
            font-weight: 700;
        }

        .feature-card p {
            color: #4b5563;
            font-size: 0.95rem;
            flex-grow: 1;
        }

        /* CTA Section - full width */
        .cta-section {
            background: #2c3e50;
            color: white;
            padding: 60px 0;
            width: 100%;
            text-align: center;
        }

        .cta-section .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
        }

        .cta-section h2 {
            font-size: 32px;
            color: white !important;
            margin-bottom: 15px;
        }

        .cta-section p {
            margin-bottom: 30px;
            color: white;
            font-size: 1.1rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Footer - full width */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 40px 0;
            text-align: center;
            width: 100%;
        }

        .footer .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: #ecf0f1;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: white;
            text-decoration: underline;
        }

        .footer .logo {
            justify-content: center;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .footer p {
            color: #95a5a6;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        /* Additional button variant */
        .btn-outline {
            background: transparent;
            border: 2px solid white;
            color: white;
        }

        .btn-outline:hover {
            background: rgba(255,255,255,0.1);
            border-color: white;
            color: white;
        }

        /* Responsive - adjust grids for smaller screens */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .navbar .container {
                flex-direction: column;
                gap: 15px;
                padding: 0 20px;
            }

            .hero h1 {
                font-size: 36px;
            }

            .hero p {
                font-size: 18px;
            }

            .container {
                padding: 0 20px;
            }

            .section-title h2 {
                font-size: 28px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .footer-links {
                gap: 15px;
                flex-direction: column;
            }
        }
        
        @media (min-width: 1400px) {
            .container {
                padding: 0 60px;  /* even more spread on very large screens */
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="{{ url('/') }}" class="logo">
                <div class="logo-icon">P</div>
                <span>Property Wise</span>
            </a>
            
            <div class="nav-links">
                @auth
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <a href="{{ route('logout') }}" 
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                @else
                    <a href="{{ route('login') }}">Login</a>
                    <a href="{{ route('register') }}" class="btn">Get Started</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Manage Your Properties with Ease</h1>
            <p>A comprehensive Property Management system for property managers, landlords, and building owners.</p>
            
            @auth
                <a href="{{ route('dashboard') }}" class="btn" style="font-size: 18px; padding: 15px 30px;">
                    Go to Dashboard →
                </a>
            @else
                <a href="{{ route('register') }}" class="btn" style="font-size: 18px; padding: 15px 30px;">
                    Start Free Trial
                </a>
            @endauth
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="section-title">
                <h2>Trusted by Property Managers</h2>
                <p>Join hundreds of satisfied customers managing their properties efficiently</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🏢</div>
                    <div class="stat-number">{{ $stats['total_buildings'] ?? '100+' }}</div>
                    <div class="stat-label">Buildings Managed</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🏠</div>
                    <div class="stat-number">{{ $stats['total_units'] ?? '500+' }}</div>
                    <div class="stat-label">Units Managed</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-number">{{ $stats['happy_tenants'] ?? '1000+' }}</div>
                    <div class="stat-label">Happy Tenants</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-number">{{ $stats['years_experience'] ?? '5+' }}</div>
                    <div class="stat-label">Years Experience</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="section-title">
                <h2>Everything You Need in One Platform</h2>
                <p>Streamline your property management operations with our comprehensive suite of tools</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">💰</div>
                    <h3>Automated Billing</h3>
                    <p>Generate and send bills automatically, track payments, and manage late fees with ease.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>Utilities Tracking</h3>
                    <p>Monitor electricity, water, and gas consumption with smart meter integration and detailed reports.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🔧</div>
                    <h3>Maintenance Management</h3>
                    <p>Handle maintenance requests, schedule preventive maintenance, and manage vendor relationships.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📄</div>
                    <h3>Lease Management</h3>
                    <p>Create, renew, and terminate leases with automated notifications and document storage.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Advanced Reporting</h3>
                    <p>Generate custom reports on occupancy, revenue, expenses, and utility consumption.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🔔</div>
                    <h3>Smart Alerts</h3>
                    <p>Get notified about overdue payments, maintenance issues, and lease expirations.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Simplify Your Property Management?</h2>
            <p>Join thousands of satisfied property managers today.</p>
            
            @auth
                <a href="{{ route('dashboard') }}" class="btn" style="background: #2ecc71;">
                    Go to Dashboard →
                </a>
            @else
                <div class="cta-buttons">
                    <a href="{{ route('register') }}" class="btn" style="background: #2ecc71;">
                        Start Free Trial
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-outline">
                        Schedule a Demo
                    </a>
                </div>
            @endauth
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="logo" style="justify-content: center; margin-bottom: 20px;">
                <div class="logo-icon">P</div>
                <span>Property Wise</span>
            </div>
            
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Contact Us</a>
                <a href="#">Support</a>
                <a href="#">Documentation</a>
            </div>
            
            <p>
                &copy; {{ date('Y') }} Property Wise. All rights reserved.
            </p>
        </div>
    </footer>
</body>
</html>