<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
            <i class="fas fa-graduation-cap me-2" style="font-size: 1.8rem; color: var(--primary-color);"></i>
            <span style="color: var(--primary-color); font-weight: 700; font-size: 1.5rem;">Infini</span>
            <span style="color: var(--text-dark); font-weight: 700; font-size: 1.5rem;">School</span>
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" 
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Content -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <!-- Main Navigation Links -->
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                        <i class="fas fa-home me-1"></i> Accueil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('formations.*') ? 'active' : '' }}" href="{{ route('formations.index') }}">
                        <i class="fas fa-book me-1"></i> Formations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('teachers') ? 'active' : '' }}" href="{{ route('teachers') }}">
                        <i class="fas fa-chalkboard-teacher me-1"></i> Enseignants
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">
                        <i class="fas fa-info-circle me-1"></i> À propos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">
                        <i class="fas fa-envelope me-1"></i> Contact
                    </a>
                </li>
            </ul>

            <!-- Auth Buttons / User Menu -->
            <div class="d-flex align-items-center">
                @guest
                    <!-- Not Logged In -->
                    <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-sign-in-alt me-1"></i> Connexion
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i> Inscription
                    </a>
                @else
                    <!-- Logged In User -->
                    <div class="dropdown">
                        <a class="btn btn-outline-primary dropdown-toggle d-flex align-items-center" 
                           href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-2" style="font-size: 1.2rem;"></i>
                            <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                            <!-- Dashboard link based on role -->
                            @if(Auth::user()->role === 'admin')
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                        <i class="fas fa-tachometer-alt me-2 text-primary"></i> Dashboard Admin
                                    </a>
                                </li>
                            @elseif(Auth::user()->role === 'teacher')
                                <li>
                                    <a class="dropdown-item" href="{{ route('teacher.dashboard') }}">
                                        <i class="fas fa-chalkboard me-2 text-primary"></i> Dashboard Enseignant
                                    </a>
                                </li>
                            @elseif(Auth::user()->role === 'student')
                                <li>
                                    <a class="dropdown-item" href="{{ route('student.dashboard') }}">
                                        <i class="fas fa-user-graduate me-2 text-primary"></i> Dashboard Étudiant
                                    </a>
                                </li>
                            @endif
                            
                            <li><hr class="dropdown-divider"></li>
                            
                            <!-- Profile -->
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="fas fa-user-edit me-2 text-secondary"></i> Mon Profil
                                </a>
                            </li>
                            
                            <!-- Messages (if exists) -->
                            @if(Auth::user()->role === 'student' || Auth::user()->role === 'teacher')
                                <li>
                                    <a class="dropdown-item" href="{{ route('messages.index') }}">
                                        <i class="fas fa-comments me-2 text-secondary"></i> Messages
                                        @if(isset($unreadMessagesCount) && $unreadMessagesCount > 0)
                                            <span class="badge bg-danger rounded-pill ms-1">{{ $unreadMessagesCount }}</span>
                                        @endif
                                    </a>
                                </li>
                            @endif
                            
                            <!-- Notifications -->
                            <li>
                                <a class="dropdown-item" href="{{ route('notifications.index') }}">
                                    <i class="fas fa-bell me-2 text-secondary"></i> Notifications
                                    @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                                        <span class="badge bg-danger rounded-pill ms-1">{{ $unreadNotificationsCount }}</span>
                                    @endif
                                </a>
                            </li>
                            
                            <li><hr class="dropdown-divider"></li>
                            
                            <!-- Logout -->
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endguest
            </div>
        </div>
    </div>
</nav>

<style>
    /* Navbar Styling */
    .navbar {
        padding: 1rem 0;
        transition: all 0.3s ease;
    }

    .navbar-brand {
        transition: transform 0.3s ease;
    }

    .navbar-brand:hover {
        transform: scale(1.05);
    }

    .nav-link {
        font-weight: 500;
        font-size: 1rem;
        padding: 0.5rem 1rem !important;
        border-radius: 8px;
        transition: all 0.3s ease;
        position: relative;
    }

    .nav-link:hover {
        background-color: rgba(128, 0, 32, 0.05);
        color: var(--primary-color) !important;
    }

    .nav-link.active {
        color: var(--primary-color) !important;
        font-weight: 600;
    }

    .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 30px;
        height: 3px;
        background-color: var(--primary-color);
        border-radius: 2px;
    }

    /* Dropdown Menu Styling */
    .dropdown-menu {
        border: none;
        border-radius: 12px;
        padding: 0.5rem;
        margin-top: 0.5rem;
        min-width: 250px;
    }

    .dropdown-item {
        border-radius: 8px;
        padding: 0.6rem 1rem;
        transition: all 0.2s ease;
        font-size: 0.95rem;
    }

    .dropdown-item:hover {
        background-color: rgba(128, 0, 32, 0.08);
        transform: translateX(5px);
    }

    .dropdown-divider {
        margin: 0.5rem 0;
        opacity: 0.1;
    }

    /* Buttons in Navbar */
    .navbar .btn {
        padding: 0.5rem 1.2rem;
        font-size: 0.95rem;
        border-radius: 8px;
        font-weight: 600;
    }

    /* Sticky Navbar Shadow on Scroll */
    .navbar.scrolled {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    /* Mobile Responsive */
    @media (max-width: 991px) {
        .navbar-collapse {
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 10px;
        }

        .nav-link {
            margin: 0.2rem 0;
        }

        .navbar .d-flex {
            margin-top: 1rem;
            flex-direction: column;
            width: 100%;
        }

        .navbar .btn {
            width: 100%;
            margin: 0.3rem 0 !important;
        }

        .dropdown-menu {
            position: static !important;
            transform: none !important;
            border: none;
            box-shadow: none;
            margin-top: 0.5rem;
            background-color: white;
        }
    }

    /* Badge for notifications */
    .badge {
        font-size: 0.65rem;
        padding: 0.25em 0.5em;
    }

    /* User dropdown button */
    #userDropdown {
        border: 2px solid var(--primary-color);
    }

    #userDropdown:hover {
        background-color: var(--primary-color);
        color: white !important;
    }
</style>

<script>
    // Add scrolled class to navbar on scroll
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Close mobile menu on link click
    document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
        link.addEventListener('click', function() {
            const navbarToggler = document.querySelector('.navbar-toggler');
            const navbarCollapse = document.querySelector('.navbar-collapse');
            
            if (window.innerWidth < 992 && navbarCollapse.classList.contains('show')) {
                navbarToggler.click();
            }
        });
    });
</script>