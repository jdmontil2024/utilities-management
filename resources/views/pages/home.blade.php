<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PROPMANAGE | Master Your Portfolio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0a0a0a;
            --bg-card: #141414;
            --accent-emerald: #10b981;
            --text-main: #f1f5f9;
            --text-dim: #94a3b8;
            --border: #262626;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-dark); color: var(--text-main); line-height: 1.6; }

        /* --- THE NEW LOGO COMPONENT --- */
        .intricate-logo-p {
            position: relative;
            width: 38px; 
            height: 38px;
            background: #111111;
            border: 1.5px solid var(--accent-emerald);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.2);
            overflow: hidden;
        }

        .intricate-logo-p .svg-icon {
            width: 75%;
            height: 75%;
            fill: none;
            stroke: var(--accent-emerald);
            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-linejoin: round;
            filter: drop-shadow(0 0 2px rgba(16, 185, 129, 0.5));
        }

        .intricate-logo-p .reflection {
            position: absolute;
            top: -100%;
            left: -100%;
            width: 300%;
            height: 300%;
            background: linear-gradient(
                135deg,
                rgba(16, 185, 129, 0) 0%,
                rgba(255, 255, 255, 0.1) 50%,
                rgba(16, 185, 129, 0) 100%
            );
            transform: rotate(25deg);
            animation: glossAuto 5s infinite ease-in-out;
        }

        @keyframes glossAuto {
            0%, 15% { top: -100%; left: -100%; }
            35%, 100% { top: 100%; left: 100%; }
        }

        /* NAVIGATION */
        nav {
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 8%;
            position: sticky;
            top: 0;
            background: rgba(10, 10, 10, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            z-index: 1000;
        }

        .brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .brand-text { font-size: 1.2rem; letter-spacing: 0.5px; }
        .brand-text .prop { color: #fff; font-weight: 700; }
        .brand-text .manage { color: var(--accent-emerald); font-weight: 500; }

        .nav-actions { display: flex; align-items: center; gap: 20px; }
        .nav-actions a.sign-in-link { color: var(--text-dim); text-decoration: none; font-size: 13px; transition: 0.3s; font-weight: 500; }
        .nav-actions a.sign-in-link:hover { color: var(--accent-emerald); }

        .btn-nav { 
            background: var(--accent-emerald); 
            color: #000; 
            padding: 10px 22px; 
            border-radius: 6px; 
            font-weight: 700; 
            font-size: 13px;
            text-decoration: none; 
            transition: 0.3s; 
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.2);
            display: inline-block;
        }
        .btn-nav:hover { 
            transform: translateY(-1px); 
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3); 
        }

        /* HERO SECTION */
        .hero { 
            position: relative;
            padding: 160px 8% 120px; 
            text-align: center; 
            background-image: url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=2070&auto=format&fit=crop'); 
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            overflow: hidden;
        }

        .hero::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(to bottom, rgba(10, 10, 10, 0.9), rgba(10, 10, 10, 0.75), rgba(10, 10, 10, 1));
            z-index: 1;
        }

        .hero-content { position: relative; z-index: 2; max-width: 1200px; margin: 0 auto; }
        .hero h1 { font-size: clamp(3rem, 6vw, 4.5rem); font-weight: 800; margin-bottom: 20px; color: #fff; letter-spacing: -1px; line-height: 1.1; }
        .hero p { font-size: 1.25rem; color: var(--text-dim); max-width: 750px; margin: 0 auto 45px; }

        .btn-group { display: flex; justify-content: center; gap: 15px; }

        .btn-primary { 
            background: var(--accent-emerald); 
            color: #000; padding: 16px 36px; border-radius: 8px; 
            font-weight: 700; text-decoration: none; transition: 0.3s; 
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.2);
            display: inline-block;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 15px 35px rgba(16, 185, 129, 0.35); }

        .btn-outline { 
            border: 1px solid var(--border); color: #fff; padding: 16px 36px; border-radius: 8px; 
            font-weight: 600; text-decoration: none; transition: 0.3s; 
        }
        .btn-outline:hover { border-color: var(--accent-emerald); color: var(--accent-emerald); }

        /* STATS SECTION */
        .stats-section { 
            padding: 80px 8%; 
            border-top: 1px solid var(--border); 
            border-bottom: 1px solid var(--border); 
            background: #0d0d0d;
        }

        .stats-header { text-align: center; margin-bottom: 50px; }
        .stats-header h1 { font-size: 1.8rem; color: #fff; font-weight: 700; letter-spacing: -0.5px; }

        .stats-grid { 
            display: grid; grid-template-columns: repeat(4, 1fr); 
            gap: 20px; max-width: 1200px; margin: 0 auto;
        }

        .stat-item { text-align: center; }
        .stat-item .val { display: block; font-size: 2.5rem; font-weight: 800; color: #fff; margin-bottom: 5px; }
        .stat-item .lbl { color: var(--text-dim); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; }

        /* FEATURES SECTION */
        .features { padding: 100px 8%; }
        .section-header { text-align: center; margin-bottom: 60px; }
        .section-header h2 { font-size: 2.8rem; color: #fff; margin-bottom: 15px; font-weight: 800; }
        .section-header p { color: var(--text-dim); font-size: 1.1rem; }
        
        .feature-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        .feature-card { background: var(--bg-card); padding: 45px; border-radius: 20px; border: 1px solid var(--border); transition: 0.3s; }
        .feature-card:hover { border-color: var(--accent-emerald); transform: translateY(-5px); }
        .feature-card .icon { font-size: 2.5rem; margin-bottom: 25px; display: block; }
        .feature-card h3 { color: #fff; margin-bottom: 15px; font-size: 1.5rem; font-weight: 700; }
        .feature-card p { color: var(--text-dim); font-size: 1rem; }

        /* UPGRADE CTA */
        .upgrade-cta { padding: 120px 8%; text-align: center; background: var(--bg-dark); border-top: 1px solid var(--border); }
        .upgrade-cta h2 { font-size: 3rem; color: #fff; margin-bottom: 25px; font-weight: 800; }
        .upgrade-cta p { margin-bottom: 45px; color: var(--text-dim); font-size: 1.2rem; }

        /* FOOTER */
        footer { padding: 80px 8% 40px; background: #050505; border-top: 1px solid var(--border); }
        .footer-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 60px; }
        .footer-links { display: flex; gap: 40px; }
        .footer-links a { color: #666; text-decoration: none; font-size: 14px; transition: 0.2s; font-weight: 500; }
        .footer-links a:hover { color: var(--accent-emerald); }
        .footer-bottom { border-top: 1px solid #1a1a1a; padding-top: 30px; color: #444; font-size: 13px; text-align: center; font-weight: 500; letter-spacing: 0.5px; }

        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 40px; }
            .hero { background-attachment: scroll; padding-top: 100px; }
            .hero h1 { font-size: 2.8rem; }
            .nav-actions { display: none; }
        }
    </style>
</head>
<body>

    <nav>
        <a href="/" class="brand">
            <div class="intricate-logo-p">
                <svg class="svg-icon" viewBox="0 0 100 100">
                    <path d="M25,20 L25,80 M25,20 Q50,5 75,20 Q100,35 75,50 L25,50 M50,20 L50,80" />
                    <path d="M25,60 L40,80 L55,60 L70,80 L85,60" />
                    <path d="M70,80 L90,80 M80,80 L80,75 M85,80 L85,75 M75,80 L75,75" />
                    <circle cx="50" cy="35" r="4" fill="var(--accent-emerald)" stroke="none"/>
                </svg>
                <div class="reflection"></div>
            </div>
            <div class="brand-text">
                <span class="prop">PROP</span><span class="manage">MANAGE</span>
            </div>
        </a>
        <div class="nav-actions">
            <a href="{{ route('login') }}" class="sign-in-link">Sign In</a>
            <a href="{{ route('register') }}" class="btn-nav">Start Free Trial</a>
        </div>
    </nav>

    <header class="hero">
        <div class="hero-content">
            <h1>Master Your Properties</h1>
            <p>High-Performance Property Management software designed for Modern Landlords and Operators.</p>
            <div class="btn-group">
                <a href="{{ route('register') }}" class="btn-primary">Start Free Trial</a>
                <a href="#features" class="btn-outline">Explore Features</a>
            </div>
        </div>
    </header>

    <section class="stats-section">
        <div class="stats-header">
            <h1>Performance by the Numbers</h1>
        </div>
        <div class="stats-grid">
            <div class="stat-item">
                <span class="val">1</span>
                <span class="lbl">Portfolios</span>
            </div>
            <div class="stat-item">
                <span class="val">3</span>
                <span class="lbl">Active Units</span>
            </div>
            <div class="stat-item">
                <span class="val">99.9%</span>
                <span class="lbl">Uptime</span>
            </div>
            <div class="stat-item">
                <span class="val">24/7</span>
                <span class="lbl">Support</span>
            </div>
        </div>
    </section>

    <section class="features" id="features">
        <div class="section-header">
            <h2>Built for Scale</h2>
            <p>Everything you need to automate your operations in one matte interface.</p>
        </div>
        <div class="feature-grid">
            <div class="feature-card">
                <span class="icon">💎</span>
                <h3>Automated Billing</h3>
                <p>Smart invoicing that handles complex utility splits and late fees automatically.</p>
            </div>
            <div class="feature-card">
                <span class="icon">⚡</span>
                <h3>Utility Metrics</h3>
                <p>Real-time tracking of water, gas, and electric consumption across your portfolio.</p>
            </div>
            <div class="feature-card">
                <span class="icon">🛡️</span>
                <h3>Lease Security</h3>
                <p>Encrypted document storage with automated renewal alerts and digital signatures.</p>
            </div>
        </div>
    </section>

    <section class="upgrade-cta">
        <h2>Ready to Upgrade Your Workflow?</h2>
        <p>Join the next generation of Property Managers who value efficiency and design.</p>
        <a href="{{ route('register') }}" class="btn-primary">Get Started Now</a>
    </section>

    <footer>
        <div class="footer-top">
            <a href="/" class="brand">
                <div class="intricate-logo-p" style="width: 32px; height: 32px;">
                    <svg class="svg-icon" viewBox="0 0 100 100">
                        <path d="M25,20 L25,80 M25,20 Q50,5 75,20 Q100,35 75,50 L25,50 M50,20 L50,80" />
                        <path d="M25,60 L40,80 L55,60 L70,80 L85,60" />
                        <path d="M70,80 L90,80 M80,80 L80,75 M85,80 L85,75 M75,80 L75,75" />
                        <circle cx="50" cy="35" r="4" fill="var(--accent-emerald)" stroke="none"/>
                    </svg>
                </div>
                <div class="brand-text" style="font-size: 1.1rem;">
                    <span class="prop">PROP</span><span class="manage">MANAGE</span>
                </div>
            </a>
            <div class="footer-links">
                <a href="#">Security</a>
                <a href="#">Terms</a>
                <a href="#">Privacy</a>
                <a href="#">API docs</a>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2026 PROPMANAGE. Crafted for Excellence.
        </div>
    </footer>

</body>
</html>