<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Property Wise</title>
    <!-- Fonts - Inter with all weights (exact match from login page) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Main background: copy of main-content background (#f8f9fa) - exactly like login */
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;  /* exact .main-content background */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-size: 14px;
            line-height: 1.6;
            color: #4b5563; /* default text color from original */
        }

        .register-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            overflow: hidden;
            width: 100%;
            max-width: 480px;  /* exactly like login width */
        }

        /* Header: exact navbar color design (#2c3e50 background + #3498db logo accent) - like login */
        .register-header {
            background: #2c3e50;  /* solid navbar background */
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }

        /* "P" logo box as in login page (using #3498db) */
        .register-header::before {
            content: "P";
            display: inline-block;
            width: 40px;
            height: 40px;
            background: #3498db;  /* logo-icon blue */
            border-radius: 8px;
            font-weight: 700;
            font-size: 20px;
            line-height: 40px;
            text-align: center;
            margin-bottom: 10px;
            color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .register-header h1 {
            font-size: 28px;
            margin: 5px 0 6px 0;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: white;
            font-family: 'Inter', sans-serif;
        }

        .register-header p {
            opacity: 0.9;
            font-size: 15px;
            font-weight: 400;
            color: #ecf0f1; /* light text from nav quick-links */
        }

        .register-body {
            padding: 35px 30px;
            background: white;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1f2937;  /* dark heading color from original */
            font-size: 14px;
            letter-spacing: -0.01em;
            font-family: 'Inter', sans-serif;
        }

        input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
            color: #4b5563;
        }

        input:focus {
            outline: none;
            border-color: #3498db;  /* focus color matches logo/nav accent */
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        input::placeholder {
            color: #9ca3af;
            font-weight: 400;
            opacity: 0.7;
        }

        /* Name fields grid - same spacing as login but with two columns */
        .name-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 0;
        }

        .register-button {
            width: 100%;
            padding: 15px;
            background: #3498db;  /* solid blue from logo/nav accent */
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            letter-spacing: -0.01em;
            margin-top: 10px;
        }

        .register-button:hover {
            background: #2c3e50;  /* navbar dark on hover */
        }

        .register-button:active {
            transform: translateY(1px);
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            font-size: 15px;
            color: #4b5563;
            font-family: 'Inter', sans-serif;
        }

        .login-link a {
            color: #3498db;
            font-weight: 600;
            text-decoration: none;
        }

        .login-link a:hover {
            color: #2c3e50;
            text-decoration: underline;
        }

        .back-home {
            text-align: center;
            margin-top: 20px;
        }

        .back-home a {
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
            transition: color 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .back-home a:hover {
            color: #3498db;
        }

        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid #dc2626;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
        }

        /* Exactly match login page styles */
        .divider {
            text-align: center;
            margin: 30px 0 20px;
            position: relative;
            color: #95a5a6;
            font-size: 14px;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
        }

        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 45%;
            height: 1px;
            background: #e0e6ed;
        }

        .divider::before {
            left: 0;
        }

        .divider::after {
            right: 0;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <!-- Header exactly like login page -->
        <div class="register-header">
            <h1>Property Wise</h1>
            <p>Create your account</p>
        </div>

        <div class="register-body">
            @if($errors->any())
                <div class="error-message">
                    <span>⚠️</span> {{ $errors->first() }}
                </div>
            @endif

            <!-- Form with original Laravel directives, untouched functionality -->
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Two-column name fields (first/last) - design matches login spacing -->
                <div class="name-fields">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required placeholder="John">
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required placeholder="Doe">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="john@example.com">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="At least 8 characters">
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Repeat your password">
                </div>

                <button type="submit" class="register-button">Create Account</button>
            </form>

            <div class="login-link">
                Already have an account? <a href="{{ route('login') }}">Sign in here</a>
            </div>

            <div class="back-home">
                <a href="{{ url('/') }}">
                    ← Back to Home
                </a>
            </div>
        </div>
    </div>

    <!-- Original functionality scripts (unchanged) -->
    <script>
        // Password validation - exactly as original but with updated selectors
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long!');
                return false;
            }
            
            // Add loading state
            const button = document.querySelector('.register-button');
            button.innerHTML = 'Creating Account...';
            button.disabled = true;
        });

        // Auto-focus first name field
        document.getElementById('first_name').focus();
    </script>
</body>
</html>