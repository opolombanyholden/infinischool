<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Authentification') - InfiniSchool</title>
    <meta name="description" content="@yield('description', 'Connexion à votre espace InfiniSchool')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS via CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet" integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Font Awesome 6.5.1 via CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Custom Auth Styles -->
    <style>
        :root {
            --primary-color: #800020;
            --primary-dark: #5a0016;
            --primary-light: #a6002a;
            --secondary-color: #f8f9fa;
            --text-dark: #212529;
            --text-light: #6c757d;
            --border-radius: 15px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(128, 0, 32, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(102, 126, 234, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(118, 75, 162, 0.3) 0%, transparent 50%);
            animation: backgroundShift 15s ease infinite;
        }

        @keyframes backgroundShift {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        /* Auth Container */
        .auth-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1100px;
            background: rgba(255, 255, 255, 0.98);
            border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideInUp 0.6s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Split Screen Layout */
        .auth-content {
            display: flex;
            min-height: 600px;
        }

        /* Left Side - Brand/Info */
        .auth-brand {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .auth-brand::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .auth-brand-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .auth-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .auth-logo i {
            font-size: 4rem;
            margin-right: 15px;
        }

        .auth-logo h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
        }

        .auth-brand h2 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 20px;
            line-height: 1.3;
        }

        .auth-brand p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .auth-features {
            margin-top: 30px;
            text-align: left;
        }

        .auth-feature {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }

        .auth-feature:hover {
            transform: translateX(10px);
        }

        .auth-feature i {
            font-size: 1.5rem;
            margin-right: 15px;
            width: 40px;
            text-align: center;
        }

        .auth-feature-text h5 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0 0 5px 0;
        }

        .auth-feature-text p {
            font-size: 0.85rem;
            margin: 0;
            opacity: 0.8;
        }

        /* Right Side - Form */
        .auth-form-container {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-header {
            margin-bottom: 40px;
            text-align: center;
        }

        .auth-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .auth-header p {
            color: var(--text-light);
            font-size: 1rem;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-control {
            padding: 12px 15px;
            border-radius: 12px;
            border: 2px solid #e9ecef;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(128, 0, 32, 0.1);
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            pointer-events: none;
            z-index: 5;
        }

        .form-control.with-icon {
            padding-left: 45px;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-light);
            z-index: 5;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        /* Checkbox & Radio */
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-check-label {
            color: var(--text-dark);
            font-size: 0.95rem;
        }

        /* Buttons */
        .btn {
            border-radius: 12px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(128, 0, 32, 0.3);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            border-width: 2px;
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .btn-block {
            width: 100%;
        }

        /* Links */
        .auth-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .auth-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Divider */
        .auth-divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 25px 0;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #dee2e6;
        }

        .auth-divider span {
            padding: 0 15px;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* Social Login Buttons */
        .social-login {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .btn-social {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            border: 2px solid #dee2e6;
            background: white;
            color: var(--text-dark);
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-social:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-google:hover { border-color: #DB4437; color: #DB4437; }
        .btn-linkedin:hover { border-color: #0077B5; color: #0077B5; }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 25px;
            animation: slideInDown 0.5s ease-out;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        /* Back to Home Link */
        .back-home {
            position: absolute;
            top: 30px;
            left: 30px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            z-index: 10;
        }

        .back-home:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateX(-5px);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .auth-content {
                flex-direction: column;
            }

            .auth-brand {
                padding: 40px 30px;
                min-height: 400px;
            }

            .auth-form-container {
                padding: 40px 30px;
            }

            .auth-logo h1 {
                font-size: 2rem;
            }

            .auth-logo i {
                font-size: 3rem;
            }

            .auth-brand h2 {
                font-size: 1.5rem;
            }

            .auth-header h2 {
                font-size: 1.7rem;
            }

            .back-home {
                top: 20px;
                left: 20px;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 10px;
            }

            .auth-container {
                border-radius: 15px;
            }

            .auth-brand {
                padding: 30px 20px;
            }

            .auth-form-container {
                padding: 30px 20px;
            }

            .auth-logo {
                flex-direction: column;
            }

            .auth-logo i {
                margin-right: 0;
                margin-bottom: 10px;
            }

            .social-login {
                flex-direction: column;
            }

            .back-home {
                position: static;
                display: inline-block;
                margin-bottom: 20px;
            }
        }

        /* Loading State */
        .btn-loading {
            position: relative;
            pointer-events: none;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            border: 2px solid white;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: translateY(-50%) rotate(360deg); }
        }

        /* Additional custom styles from pages */
        @yield('styles')
    </style>
</head>
<body>
    <!-- Back to Home Link -->
    <a href="{{ route('home') }}" class="back-home">
        <i class="fas fa-arrow-left me-2"></i>Retour à l'accueil
    </a>

    <!-- Auth Container -->
    <div class="auth-container">
        <div class="auth-content">
            <!-- Left Side - Brand Section -->
            <div class="auth-brand">
                <div class="auth-brand-content">
                    <div class="auth-logo">
                        <i class="fas fa-graduation-cap"></i>
                        <h1>InfiniSchool</h1>
                    </div>
                    
                    <h2>@yield('brand-title', 'Votre Avenir Commence Ici')</h2>
                    <p>@yield('brand-description', 'Rejoignez des milliers d\'étudiants et transformez votre apprentissage avec nos cours en direct et classes virtuelles.')</p>
                    
                    <!-- Features -->
                    <div class="auth-features">
                        <div class="auth-feature">
                            <i class="fas fa-video"></i>
                            <div class="auth-feature-text">
                                <h5>Cours en Direct</h5>
                                <p>Interagissez en temps réel avec vos formateurs</p>
                            </div>
                        </div>
                        
                        <div class="auth-feature">
                            <i class="fas fa-certificate"></i>
                            <div class="auth-feature-text">
                                <h5>Certifications</h5>
                                <p>Obtenez des certificats reconnus</p>
                            </div>
                        </div>
                        
                        <div class="auth-feature">
                            <i class="fas fa-users"></i>
                            <div class="auth-feature-text">
                                <h5>Communauté Active</h5>
                                <p>Apprenez avec des milliers d'étudiants</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Form Section -->
            <div class="auth-form-container">
                <!-- Header -->
                <div class="auth-header">
                    <h2>@yield('form-title', 'Connexion')</h2>
                    <p>@yield('form-description', 'Accédez à votre espace personnel')</p>
                </div>

                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Erreur !</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Main Form Content -->
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3.3 JS Bundle (includes Popper) via CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js" integrity="sha512-7Pi/otdlbbCR+LnW+F7PwFcSDJOuUJB3OxtEHbg4vSMvzvJjde4Po1v4BR9Gdc9aXNUNFVUY+SK51wWT8WF0Gg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    if (alert) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                });
            }, 5000);

            // Password toggle functionality
            document.querySelectorAll('.password-toggle').forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const input = this.previousElementSibling;
                    const icon = this.querySelector('i');
                    
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });

            // Form submission loading state
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.classList.contains('btn-loading')) {
                        submitBtn.classList.add('btn-loading');
                        submitBtn.disabled = true;
                    }
                });
            });

            // Form validation
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                        
                        // Remove loading state if validation fails
                        const submitBtn = form.querySelector('button[type="submit"]');
                        if (submitBtn) {
                            submitBtn.classList.remove('btn-loading');
                            submitBtn.disabled = false;
                        }
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        });
    </script>

    <!-- Additional scripts from pages -->
    @yield('scripts')
</body>
</html>