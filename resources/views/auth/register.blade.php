<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Create Account | PROPMANAGE</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-main: #121212;
            --bg-card: #1f1f1f;
            --accent-green: #10b981;
            --text-primary: #f1f5f9;
            --text-muted: #94a3b8;
            --border: #2d2d2d;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            background-color: var(--bg-main);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        /* --- REGISTER CARD --- */
        .register-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            width: 100%;
            max-width: 480px; /* Slightly wider for registration forms */
            padding: 3rem 2.5rem;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            text-align: center;
        }

        /* --- CARD BRANDING --- */
        .card-brand {
            text-decoration: none;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            margin-bottom: 2.5rem;
        }

        .intricate-logo-p {
            position: relative;
            width: 50px; 
            height: 50px;
            background: #111111;
            border: 2px solid var(--accent-green);
            border-radius: 10px;
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
            stroke: var(--accent-green);
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

        .brand-text {
            font-size: 1.25rem;
            letter-spacing: 1px;
            display: flex;
            align-items: baseline;
        }
        .brand-text .prop { color: #ffffff; font-weight: 700; }
        .brand-text .manage { color: var(--accent-green); font-weight: 500; }

        /* --- FORM STYLES --- */
        .register-card h2 {
            font-size: 1.4rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
            text-align: left;
        }

        .register-card p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .form-group { 
            margin-bottom: 1.25rem; 
            text-align: left;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            background-color: var(--bg-main);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px 16px;
            color: white;
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-green);
        }

        .btn-submit {
            width: 100%;
            background-color: var(--accent-green);
            color: #121212;
            border: none;
            border-radius: 8px;
            padding: 14px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.2s, background-color 0.2s;
            margin-top: 1rem;
        }

        .btn-submit:hover {
            background-color: #059669;
            transform: translateY(-1px);
        }

        .login-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.2s;
        }

        .login-link:hover { color: var(--accent-green); }

    </style>
</head>
<body>

    <div class="register-card">
        <div class="card-brand">
            <div class="intricate-logo-p">
                <svg class="svg-icon" viewBox="0 0 100 100">
                    <path d="M25,20 L25,80 M25,20 Q50,5 75,20 Q100,35 75,50 L25,50 M50,20 L50,80" />
                    <path d="M25,60 L40,80 L55,60 L70,80 L85,60" />
                    <path d="M70,80 L90,80 M80,80 L80,75 M85,80 L85,75 M75,80 L75,75" />
                    <circle cx="50" cy="35" r="4" fill="var(--accent-green)" stroke="none"/>
                </svg>
                <div class="reflection"></div>
            </div>
            <div class="brand-text">
                <span class="prop">PROP</span><span class="manage">MANAGE</span>
            </div>
        </div>

        <h2>Create Account</h2>
        <p>Join our platform to streamline your property management.</p>

        <form method="POST" action="{{ route('register') }}">
            @csrf
            
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-input" placeholder="John Doe" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-input" placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-input" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-submit">Start Managing Now</button>
        </form>

        <a href="{{ route('login') }}" class="login-link">Already have an account? Sign in</a>
    </div>

</body>
</html>