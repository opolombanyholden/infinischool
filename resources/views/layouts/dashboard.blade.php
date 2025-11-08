<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - InfiniSchool</title>
    <meta name="description" content="@yield('description', 'Tableau de bord InfiniSchool')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS via CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet" integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Font Awesome 6.5.1 via CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Custom Dashboard Styles -->
    <style>
        :root {
            --primary-color: #800020;
            --primary-dark: #5a0016;
            --primary-light: #a6002a;
            --secondary-color: #f8f9fa;
            --text-dark: #212529;
            --text-light: #6c757d;
            --border-radius: 15px;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --topbar-height: 70px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--text-dark);
            overflow-x: hidden;
        }

        /* ========================================
           SIDEBAR
        ======================================== */
        .dashboard-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            transition: all 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
        }

        .dashboard-sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        /* Sidebar Header */
        .sidebar-header {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            min-height: var(--topbar-height);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
        }

        .sidebar-logo i {
            font-size: 1.8rem;
            margin-right: 12px;
        }

        .sidebar-logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .collapsed .sidebar-logo-text {
            display: none;
        }

        .sidebar-toggle {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .collapsed .sidebar-toggle i {
            transform: rotate(180deg);
        }

        /* Sidebar Navigation */
        .sidebar-nav {
            padding: 20px 0;
        }

        .sidebar-nav-title {
            padding: 15px 20px 10px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.6);
            transition: all 0.3s ease;
        }

        .collapsed .sidebar-nav-title {
            text-align: center;
            padding: 15px 5px 10px;
        }

        .sidebar-nav-item {
            margin: 5px 15px;
        }

        .sidebar-nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .sidebar-nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: white;
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .sidebar-nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(5px);
        }

        .sidebar-nav-link:hover::before {
            transform: scaleY(1);
        }

        .sidebar-nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-weight: 600;
        }

        .sidebar-nav-link.active::before {
            transform: scaleY(1);
        }

        .sidebar-nav-icon {
            width: 24px;
            margin-right: 12px;
            text-align: center;
            font-size: 1.1rem;
        }

        .collapsed .sidebar-nav-icon {
            margin-right: 0;
        }

        .sidebar-nav-text {
            flex: 1;
            white-space: nowrap;
            transition: opacity 0.3s ease;
        }

        .collapsed .sidebar-nav-text {
            opacity: 0;
            display: none;
        }

        .sidebar-nav-badge {
            background: rgba(255, 255, 255, 0.3);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .collapsed .sidebar-nav-badge {
            display: none;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.1);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .sidebar-user:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .sidebar-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.1rem;
            margin-right: 12px;
        }

        .collapsed .sidebar-user-avatar {
            margin-right: 0;
        }

        .sidebar-user-info {
            flex: 1;
            min-width: 0;
        }

        .collapsed .sidebar-user-info {
            display: none;
        }

        .sidebar-user-name {
            font-weight: 600;
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-role {
            font-size: 0.75rem;
            opacity: 0.8;
        }

        /* Custom Scrollbar for Sidebar */
        .dashboard-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .dashboard-sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .dashboard-sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .dashboard-sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* ========================================
           MAIN CONTENT
        ======================================== */
        .dashboard-main {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .sidebar-collapsed .dashboard-main {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* ========================================
           TOPBAR
        ======================================== */
        .dashboard-topbar {
            background: white;
            height: var(--topbar-height);
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .topbar-search {
            position: relative;
        }

        .topbar-search-input {
            width: 300px;
            padding: 10px 15px 10px 45px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .topbar-search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(128, 0, 32, 0.1);
            outline: none;
        }

        .topbar-search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .topbar-icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: none;
            background: #f8f9fa;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .topbar-icon-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .topbar-icon-btn .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 0.65rem;
            font-weight: 600;
        }

        .topbar-user-dropdown {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 15px;
            border-radius: 10px;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .topbar-user-dropdown:hover {
            background: #e9ecef;
        }

        .topbar-user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .topbar-user-info {
            display: flex;
            flex-direction: column;
        }

        .topbar-user-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-dark);
        }

        .topbar-user-role {
            font-size: 0.75rem;
            color: var(--text-light);
        }

        /* ========================================
           CONTENT AREA
        ======================================== */
        .dashboard-content {
            padding: 30px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Page Header */
        .page-header {
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .page-subtitle {
            color: var(--text-light);
            font-size: 1rem;
        }

        /* Breadcrumb */
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
            font-size: 0.9rem;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: '›';
            color: var(--text-light);
        }

        .breadcrumb-item.active {
            color: var(--primary-color);
            font-weight: 600;
        }

        /* ========================================
           CARDS & COMPONENTS
        ======================================== */
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            margin-bottom: 25px;
        }

        .card:hover {
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            font-size: 1.1rem;
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 10px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
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

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }

        /* ========================================
           RESPONSIVE
        ======================================== */
        @media (max-width: 992px) {
            .dashboard-sidebar {
                transform: translateX(-100%);
            }

            .dashboard-sidebar.show {
                transform: translateX(0);
            }

            .dashboard-main {
                margin-left: 0 !important;
            }

            .topbar-search-input {
                width: 200px;
            }

            .topbar-user-info {
                display: none;
            }

            .dashboard-content {
                padding: 20px;
            }
        }

        @media (max-width: 576px) {
            .dashboard-topbar {
                padding: 0 15px;
            }

            .topbar-search {
                display: none;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .dashboard-content {
                padding: 15px;
            }
        }

        /* Mobile Menu Overlay */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }

        /* Mobile Toggle Button */
        .mobile-menu-toggle {
            display: none;
        }

        @media (max-width: 992px) {
            .mobile-menu-toggle {
                display: flex;
            }
        }

        /* Additional custom styles from pages */
        @yield('styles')
    </style>
</head>
<body class="{{ request()->routeIs('admin.*') ? 'admin-space' : (request()->routeIs('teacher.*') ? 'teacher-space' : 'student-space') }}">
    
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="dashboard-sidebar" id="dashboardSidebar">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <a href="{{ route('home') }}" class="sidebar-logo">
                <i class="fas fa-graduation-cap"></i>
                <span class="sidebar-logo-text">InfiniSchool</span>
            </a>
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-angles-left"></i>
            </button>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="sidebar-nav">
            @yield('sidebar-menu')
        </nav>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-user-avatar">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">{{ Auth::user()->name }}</div>
                    <div class="sidebar-user-role">
                        @if(Auth::user()->role === 'admin')
                            Administrateur
                        @elseif(Auth::user()->role === 'teacher')
                            Enseignant
                        @else
                            Étudiant
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="dashboard-main" id="dashboardMain">
        <!-- Topbar -->
        <div class="dashboard-topbar">
            <div class="topbar-left">
                <!-- Mobile Menu Toggle -->
                <button class="topbar-icon-btn mobile-menu-toggle" id="mobileMenuToggle">
                    <i class="fas fa-bars"></i>
                </button>

                <!-- Search Bar -->
                <div class="topbar-search">
                    <i class="fas fa-search topbar-search-icon"></i>
                    <input type="text" class="topbar-search-input" placeholder="Rechercher...">
                </div>
            </div>

            <div class="topbar-right">
                <!-- Notifications -->
                <button class="topbar-icon-btn" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell"></i>
                    @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                        <span class="badge">{{ $unreadNotificationsCount }}</span>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><h6 class="dropdown-header">Notifications</h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('notifications.index') }}">Voir toutes les notifications</a></li>
                </ul>

                <!-- Messages -->
                @if(Auth::user()->role !== 'admin')
                    <button class="topbar-icon-btn" onclick="window.location='{{ route('messages.index') }}'">
                        <i class="fas fa-envelope"></i>
                        @if(isset($unreadMessagesCount) && $unreadMessagesCount > 0)
                            <span class="badge">{{ $unreadMessagesCount }}</span>
                        @endif
                    </button>
                @endif

                <!-- User Dropdown -->
                <div class="dropdown">
                    <div class="topbar-user-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="topbar-user-avatar">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="topbar-user-info">
                            <div class="topbar-user-name">{{ Auth::user()->name }}</div>
                            <div class="topbar-user-role">
                                @if(Auth::user()->role === 'admin')
                                    Administrateur
                                @elseif(Auth::user()->role === 'teacher')
                                    Enseignant
                                @else
                                    Étudiant
                                @endif
                            </div>
                        </div>
                        <i class="fas fa-chevron-down ms-2"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user me-2"></i>Mon Profil</a></li>
                        <li><a class="dropdown-item" href="{{ route('home') }}"><i class="fas fa-home me-2"></i>Retour à l'accueil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="dashboard-content">
            <!-- Flash Messages -->
            @if (session('success') || session('error') || session('info') || session('warning'))
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Succès !</strong> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Erreur !</strong> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Info :</strong> {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention !</strong> {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            @endif

            <!-- Page Content -->
            @yield('content')
        </div>
    </main>

    <!-- Bootstrap 5.3.3 JS Bundle (includes Popper) via CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js" integrity="sha512-7Pi/otdlbbCR+LnW+F7PwFcSDJOuUJB3OxtEHbg4vSMvzvJjde4Po1v4BR9Gdc9aXNUNFVUY+SK51wWT8WF0Gg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- Custom Dashboard JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('dashboardSidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const body = document.body;

            // Sidebar Toggle (Desktop)
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    body.classList.toggle('sidebar-collapsed');
                    
                    // Save state to localStorage
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                });

                // Restore sidebar state from localStorage
                const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                if (sidebarCollapsed) {
                    sidebar.classList.add('collapsed');
                    body.classList.add('sidebar-collapsed');
                }
            }

            // Mobile Menu Toggle
            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', function() {
                    sidebar.classList.add('show');
                    sidebarOverlay.classList.add('show');
                });
            }

            // Close sidebar on overlay click
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                });
            }

            // Close mobile menu when clicking on a link
            const sidebarLinks = sidebar.querySelectorAll('.sidebar-nav-link');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 992) {
                        sidebar.classList.remove('show');
                        sidebarOverlay.classList.remove('show');
                    }
                });
            });

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

            // Initialize all tooltips
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

            // Initialize all popovers
            const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
            const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));

            // Search functionality (placeholder)
            const searchInput = document.querySelector('.topbar-search-input');
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    // Implement search functionality here
                    console.log('Search:', e.target.value);
                });
            }
        });

        // Form validation helper
        (function() {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>

    <!-- Additional scripts from pages -->
    @yield('scripts')
</body>
</html>