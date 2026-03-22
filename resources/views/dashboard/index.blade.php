<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>🏢 Dashboard - Utility Wise</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    
    <style>
        /* RESET & BASE - EXACT COPY from buildings file */
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
            font-size: 14px; /* EXACT COPY */
            line-height: 1.6; /* EXACT COPY */
            color: #333;
            background: #f8f9fa;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* MAIN CONTENT - Keep original */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 0; /* Same as buildings file main padding */
            background: white;
        }

        .container {
            max-width: 1200px; /* EXACT COPY from buildings file */
            margin: 0 auto;
            padding: 0 15px; /* EXACT COPY */
            width: 100%;
        }

        /* Welcome Banner - Centered */
        .welcome-banner {
            display: block; /* Changed from inline-block to block */
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 6px; /* Slightly smaller radius */
            padding: 12px 24px; /* Compact padding around text */
            margin: 0 auto 25px; /* Center with auto margins */
            box-shadow: 0 2px 8px rgba(0,0,0,.1); /* Lighter shadow */
            color: white;
            text-align: center;
            position: relative;
            width: fit-content; /* Make width fit content */
        }

        .welcome-banner h1 {
            font-size: 24px; /* EXACT COPY from page-title */
            font-weight: 600; /* EXACT COPY from page-title */
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center; /* Center content */
            gap: 10px; /* EXACT COPY from page-title gap */
            color: white;
            white-space: nowrap; /* Prevent text wrapping */
        }

        .welcome-banner p {
            font-size: 14px; /* EXACT COPY base size */
            font-weight: 500;
            opacity: 0.95;
            line-height: 1.6; /* EXACT COPY */
            margin: 8px 0 0 0; /* Tight margin */
            white-space: nowrap; /* Prevent text wrapping */
        }

        /* Features Description - Narrower, taller, centered */
        .features-description {
            background: white;
            border-radius: 8px; /* Same as table-container */
            padding: 25px 30px; /* Increased padding for more height */
            margin: 0 auto 25px; /* Center with auto margins */
            box-shadow: 0 2px 10px rgba(0,0,0,.1); /* Same as table-container */
            border: 1px solid #dee2e6; /* Same as table-container */
            text-align: center;
            width: 90%; /* Narrower width */
            max-width: 800px; /* Maximum width */
            min-height: 350px; /* Taller minimum height */
            display: flex;
            flex-direction: column;
            justify-content: center; /* Vertically center content */
        }

        .features-description h2 {
            font-size: 20px; /* Slightly smaller than page-title */
            font-weight: 600; /* Same as page-title */
            color: #2c3e50;
            margin-bottom: 20px; /* Increased margin for spacing */
            display: flex;
            align-items: center;
            justify-content: center; /* Center the icon and text */
            gap: 10px; /* Same as page-title gap */
            line-height: 1.4; /* Tighter line height */
        }

        .features-description p {
            font-size: 14px; /* EXACT COPY base size */
            line-height: 1.6; /* EXACT COPY */
            color: #495057;
            margin-bottom: 18px; /* Slightly increased margin */
            max-width: 700px; /* Constrain paragraph width */
            margin-left: auto;
            margin-right: auto;
        }

        .highlight-feature {
            display: inline-block;
            background: #f8f9fa; /* Same as table th background */
            padding: 6px 14px; /* EXACT COPY from status-badge */
            border-radius: 20px; /* EXACT COPY from status-badge */
            font-weight: 500; /* EXACT COPY from status-badge */
            font-size: 12px; /* EXACT COPY from status-badge */
            color: #2c3e50;
            margin: 0 6px 8px; /* Reduced horizontal margin */
            border: 1px solid #dee2e6; /* Same as table th border */
            transition: all 0.3s ease;
        }

        .highlight-feature:hover {
            background: #3498db; /* Same as btn-primary */
            color: white;
            border-color: #3498db;
        }

        /* Quick Actions - Centered */
        .quick-actions {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px; /* Increased gap */
            margin: 0 auto; /* Center the entire section */
            width: 90%; /* Match features-description width */
            max-width: 800px; /* Match features-description max-width */
        }

        .actions-title {
            font-size: 20px; /* Same as features-description h2 */
            font-weight: 600; /* Same as page-title */
            color: #2c3e50;
            margin-bottom: 15px; /* Increased margin */
            text-align: center;
        }

        .actions-buttons {
            display: flex;
            gap: 8px; /* Same as action-buttons gap */
            justify-content: center;
            flex-wrap: wrap;
        }

        .action-button {
            display: inline-flex;
            align-items: center;
            gap: 8px; /* Same as spec-item gap */
            padding: 10px 20px; /* EXACT COPY from .btn */
            background: white;
            border-radius: 4px; /* EXACT COPY from .btn */
            text-decoration: none;
            color: #2c3e50;
            box-shadow: 0 2px 10px rgba(0,0,0,.1); /* Same as table-container */
            transition: all 0.3s ease; /* EXACT COPY from .btn */
            border: 1px solid #dee2e6; /* Same as table-container */
            font-size: 14px; /* EXACT COPY from .btn */
            font-weight: 500; /* EXACT COPY from .btn */
            min-width: 150px; /* Similar to type-badge min-width */
            justify-content: center;
        }

        .action-button:hover {
            transform: translateY(-2px); /* Similar to table tbody tr:hover */
            box-shadow: 0 4px 15px rgba(0,0,0,.12); /* Similar to hover states */
            border-color: #3498db; /* Same as building-name:hover color */
            background: #f8f9fa; /* Same as table tbody tr:hover td background */
        }

        .action-button-primary {
            background: #3498db; /* EXACT COPY from .btn-primary */
            color: white;
            border: none;
        }

        .action-button-primary:hover {
            background: #2980b9; /* EXACT COPY from .btn-primary:hover */
        }

        .action-icon {
            font-size: 16px; /* Similar to building icons */
        }

        /* FOOTER - EXACT COPY from buildings file */
        footer {
            background: #333;
            color: white;
            padding: 20px 0;
            width: 100%;
            position: relative;
            bottom: 0;
            left: 0;
            right: 0;
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
            font-size: 14px; /* EXACT COPY base size */
        }

        .footer-links a:hover {
            color: white;
        }

        /* Responsive - EXACT COPY proportions */
        @media (max-width: 768px) {
            main {
                padding: 20px 0; /* Same as buildings file mobile */
                min-height: calc(100vh - 250px); /* Same as buildings file */
            }

            .container {
                padding: 0 15px; /* Maintain */
            }

            .welcome-banner {
                padding: 10px 20px; /* Slightly smaller on mobile */
                margin-bottom: 20px; /* Same as table-container mobile */
                width: 100%; /* Full width on mobile */
                max-width: none;
            }

            .welcome-banner h1 {
                font-size: 22px; /* Slightly smaller for mobile */
                flex-direction: column; /* Stack icon and text on very small screens */
                gap: 5px;
                white-space: normal; /* Allow text wrapping on mobile */
            }

            .welcome-banner p {
                font-size: 13px; /* Slightly smaller on mobile */
                white-space: normal; /* Allow text wrapping on mobile */
                margin-top: 5px;
            }

            .features-description {
                padding: 20px 25px; /* Maintain increased padding on mobile */
                margin-bottom: 20px; /* Same as table-container mobile */
                width: 100%; /* Full width on mobile */
                min-height: 300px; /* Slightly less minimum height on mobile */
            }

            .features-description h2 {
                font-size: 18px; /* Slightly smaller for mobile */
                margin-bottom: 15px; /* Adjusted for mobile */
            }

            .features-description p {
                font-size: 13px; /* Slightly smaller on mobile */
                margin-bottom: 15px; /* Adjusted for mobile */
            }

            .quick-actions {
                width: 100%; /* Full width on mobile */
            }

            .actions-buttons {
                flex-direction: column;
                align-items: center;
                gap: 10px; /* Tighter gap on mobile */
            }

            .action-button {
                width: 100%;
                max-width: 300px;
                padding: 10px 20px; /* Maintain exact .btn padding */
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
        }

        @media (max-width: 480px) {
            .welcome-banner {
                padding: 8px 16px; /* Even smaller on very small screens */
            }

            .welcome-banner h1 {
                font-size: 20px; /* Further reduction for very small screens */
            }

            .features-description {
                padding: 15px 20px; /* Adjusted for very small screens */
                min-height: 280px; /* Further reduced minimum height */
            }

            .features-description h2 {
                font-size: 18px; /* Maintain */
            }

            .highlight-feature {
                display: block;
                margin: 8px auto;
                max-width: 200px;
                padding: 6px 12px; /* Slightly smaller padding on mobile */
                font-size: 12px; /* Maintain status-badge size */
            }

            .actions-title {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <!-- Include original navigation - This will use its own font sizes -->
    @auth
        @include('layouts.navigation')
    @endauth

    <!-- Main Content -->
    <main>
        <div class="container">
            <!-- Welcome Banner - Centered -->
            <div class="welcome-banner">
                <h1>Welcome back, {{ auth()->user()->name }}! 👋</h1>
                <p>Streamline your property management with Utility Wise</p>
            </div>

            <!-- Features Description - Narrower, taller, centered -->
            <div class="features-description">
                <h2>🚀 Powerful Property Management Platform</h2>
                
                <p>
                    Utility Wise provides a complete solution for managing your property portfolio with 
                    <span class="highlight-feature">real-time monitoring</span>, 
                    <span class="highlight-feature">automated billing</span>, and 
                    <span class="highlight-feature">comprehensive reporting</span>. 
                    Our platform helps you track occupancy rates, manage maintenance requests, and streamline tenant communications—all from one centralized dashboard.
                </p>
                
                <p>
                    With features like 
                    <span class="highlight-feature">automated rent collection</span>, 
                    <span class="highlight-feature">maintenance tracking</span>, 
                    <span class="highlight-feature">document management</span>, and 
                    <span class="highlight-feature">financial reporting</span>, 
                    you can reduce administrative work while improving tenant satisfaction and maximizing your property's profitability.
                </p>
                
                <p>
                    Get started by adding your first property or explore your existing portfolio to see how Utility Wise can transform your property management workflow.
                </p>
            </div>

            <!-- Quick Actions - Centered without subtitle -->
            <div class="quick-actions">
                <div class="actions-title">Get Started</div>
                
                <div class="actions-buttons">
                    <a href="{{ route('buildings.create') }}" class="action-button action-button-primary">
                        <span class="action-icon">🏢</span>
                        <span>Add New Building</span>
                    </a>
                    
                    <a href="{{ route('buildings.index') }}" class="action-button">
                        <span class="action-icon">📋</span>
                        <span>View All Buildings</span>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer - EXACT COPY from buildings file -->
    <footer>
        <div class="footer-container">
            <div>
                &copy; {{ date('Y') }} Utility Wise. All rights reserved.
            </div>
            <div class="footer-links">
                <a href="{{ route('help') }}">Help</a>
                <a href="{{ route('documentation') }}">Docs</a>
                <a href="{{ route('support') }}">Support</a>
            </div>
        </div>
    </footer>

    <script>
        // Interactive animations
        document.addEventListener('DOMContentLoaded', function() {
            // Add click animation to action buttons
            document.querySelectorAll('.action-button').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        window.location.href = this.href;
                    }, 200);
                });
            });

            // Animate highlight features on hover
            const highlightFeatures = document.querySelectorAll('.highlight-feature');
            highlightFeatures.forEach(feature => {
                feature.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                
                feature.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Toast notification system - EXACT COPY from buildings file
            window.showToast = function(message, type = 'success') {
                // Create toast container if it doesn't exist
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
                toast.className = `toast toast-${type}`;
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
                    border-left: 4px solid ${type === 'success' ? '#28a745' : 
                                      type === 'error' ? '#dc3545' : 
                                      type === 'warning' ? '#ffc107' : '#17a2b8'};
                `;
                
                toast.innerHTML = `
                    <div style="flex-grow: 1;">${message}</div>
                    <button onclick="this.parentElement.remove()" style="background: none; border: none; cursor: pointer; font-size: 18px; color: #666;">&times;</button>
                `;

                container.appendChild(toast);

                // Auto remove after 5 seconds
                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.remove();
                    }
                }, 5000);
            };

            // Show session messages as toasts - EXACT COPY from buildings file
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
            
            @if(session('info'))
                document.addEventListener('DOMContentLoaded', function() {
                    showToast('{{ session("info") }}', 'info');
                });
            @endif
        });
    </script>
</body>
</html>