{{-- Sidebar Menu Admin - Simplifié avec liens fonctionnels uniquement --}}

{{-- Section principale --}}
<div class="sidebar-nav-title">Principal</div>

<div class="sidebar-nav-item">
    <a href="{{ route('admin.dashboard') }}"
        class="sidebar-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt sidebar-nav-icon"></i>
        <span class="sidebar-nav-text">Tableau de bord</span>
    </a>
</div>

{{-- Gestion --}}
<div class="sidebar-nav-title">Gestion</div>

<div class="sidebar-nav-item">
    <a href="{{ route('admin.users.index') }}"
        class="sidebar-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
        <i class="fas fa-users sidebar-nav-icon"></i>
        <span class="sidebar-nav-text">Utilisateurs</span>
    </a>
</div>

{{-- Finances --}}
<div class="sidebar-nav-title">Finances</div>

<div class="sidebar-nav-item">
    <a href="{{ route('admin.payments.index') }}"
        class="sidebar-nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
        <i class="fas fa-credit-card sidebar-nav-icon"></i>
        <span class="sidebar-nav-text">Paiements</span>
    </a>
</div>

<div class="sidebar-nav-item">
    <a href="{{ route('admin.revenue.index') }}"
        class="sidebar-nav-link {{ request()->routeIs('admin.revenue.*') ? 'active' : '' }}">
        <i class="fas fa-coins sidebar-nav-icon"></i>
        <span class="sidebar-nav-text">Revenus</span>
    </a>
</div>

{{-- Modération --}}
<div class="sidebar-nav-title">Modération</div>

<div class="sidebar-nav-item">
    <a href="{{ route('admin.requests.index') }}"
        class="sidebar-nav-link {{ request()->routeIs('admin.requests.*') ? 'active' : '' }}">
        <i class="fas fa-tasks sidebar-nav-icon"></i>
        <span class="sidebar-nav-text">Demandes</span>
    </a>
</div>

<div class="sidebar-nav-item">
    <a href="{{ route('admin.reviews.index') }}"
        class="sidebar-nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
        <i class="fas fa-star sidebar-nav-icon"></i>
        <span class="sidebar-nav-text">Avis</span>
    </a>
</div>

<div class="sidebar-nav-item">
    <a href="{{ route('admin.support.index') }}"
        class="sidebar-nav-link {{ request()->routeIs('admin.support.*') ? 'active' : '' }}">
        <i class="fas fa-headset sidebar-nav-icon"></i>
        <span class="sidebar-nav-text">Support</span>
    </a>
</div>

{{-- Communication --}}
<div class="sidebar-nav-title">Communication</div>

<div class="sidebar-nav-item">
    <a href="{{ route('admin.alerts.index') }}"
        class="sidebar-nav-link {{ request()->routeIs('admin.alerts.*') ? 'active' : '' }}">
        <i class="fas fa-bell sidebar-nav-icon"></i>
        <span class="sidebar-nav-text">Alertes</span>
    </a>
</div>

{{-- Analytics --}}
<div class="sidebar-nav-title">Statistiques</div>

<div class="sidebar-nav-item">
    <a href="{{ route('admin.reports.index') }}"
        class="sidebar-nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
        <i class="fas fa-chart-bar sidebar-nav-icon"></i>
        <span class="sidebar-nav-text">Rapports</span>
    </a>
</div>

<div class="sidebar-nav-item">
    <a href="{{ route('admin.activity.index') }}"
        class="sidebar-nav-link {{ request()->routeIs('admin.activity.*') ? 'active' : '' }}">
        <i class="fas fa-stream sidebar-nav-icon"></i>
        <span class="sidebar-nav-text">Activités</span>
    </a>
</div>