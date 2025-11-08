@extends('layouts.dashboard')

@section('title', 'Administration - InfiniSchool')
@section('description', 'Tableau de bord administrateur pour gérer la plateforme InfiniSchool')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header avec vue d'ensemble -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="h3 mb-2 fw-bold">
                        <i class="fas fa-shield-alt text-danger me-2"></i>
                        Tableau de bord administrateur
                    </h1>
                    <p class="text-muted mb-0">
                        <i class="far fa-calendar-alt me-1"></i>
                        {{ \Carbon\Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                        <span class="mx-2">|</span>
                        <span class="badge bg-success-subtle text-success">
                            <i class="fas fa-circle fa-xs pulse me-1"></i>Système opérationnel
                        </span>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#alertModal">
                        <i class="fas fa-exclamation-triangle me-2"></i>Envoyer alerte
                    </button>
                    <a href="{{ route('admin.settings') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-cog me-2"></i>Paramètres
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards principales -->
    <div class="row g-4 mb-4">
        <!-- Total utilisateurs -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">Voir tous</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.users.create') }}">Ajouter</a></li>
                            </ul>
                        </div>
                    </div>
                    <h3 class="h2 fw-bold mb-1">{{ number_format($stats['total_users'] ?? 0, 0, ',', ' ') }}</h3>
                    <p class="text-muted mb-2 small">Utilisateurs inscrits</p>
                    <div class="d-flex justify-content-between text-muted small">
                        <span><i class="fas fa-user-graduate me-1"></i>{{ $stats['students'] ?? 0 }} étudiants</span>
                        <span><i class="fas fa-chalkboard-teacher me-1"></i>{{ $stats['teachers'] ?? 0 }} formateurs</span>
                    </div>
                    <div class="mt-3 pt-2 border-top">
                        <small class="text-success">
                            <i class="fas fa-arrow-up me-1"></i>
                            +{{ $stats['new_users_this_month'] ?? 0 }} ce mois
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total formations -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="icon-box bg-info bg-opacity-10 text-info rounded-3 p-3">
                            <i class="fas fa-book-open fa-2x"></i>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.formations.index') }}">Voir toutes</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.formations.pending') }}">En attente</a></li>
                            </ul>
                        </div>
                    </div>
                    <h3 class="h2 fw-bold mb-1">{{ $stats['total_formations'] ?? 0 }}</h3>
                    <p class="text-muted mb-2 small">Formations actives</p>
                    <div class="d-flex justify-content-between text-muted small">
                        <span class="text-success"><i class="fas fa-check-circle me-1"></i>{{ $stats['published_formations'] ?? 0 }} publiées</span>
                        <span class="text-warning"><i class="fas fa-clock me-1"></i>{{ $stats['pending_formations'] ?? 0 }} en attente</span>
                    </div>
                    <div class="mt-3 pt-2 border-top">
                        <small class="text-info">
                            <i class="fas fa-plus me-1"></i>
                            +{{ $stats['new_formations_this_month'] ?? 0 }} ce mois
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenus totaux -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="icon-box bg-success bg-opacity-10 text-success rounded-3 p-3">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.revenue.index') }}">Détails</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.reports.revenue') }}">Rapport</a></li>
                            </ul>
                        </div>
                    </div>
                    <h3 class="h2 fw-bold mb-1">{{ number_format($stats['total_revenue'] ?? 0, 0, ',', ' ') }}</h3>
                    <p class="text-muted mb-2 small">Revenus totaux (FCFA)</p>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($stats['revenue_progress'] ?? 65) }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>Objectif mensuel</span>
                        <span>{{ $stats['revenue_progress'] ?? 65 }}%</span>
                    </div>
                    <div class="mt-3 pt-2 border-top">
                        <small class="text-success">
                            <i class="fas fa-arrow-up me-1"></i>
                            {{ number_format($stats['monthly_revenue'] ?? 0, 0, ',', ' ') }} FCFA ce mois
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Taux de satisfaction -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="icon-box bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                            <i class="fas fa-star fa-2x"></i>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.reviews.index') }}">Tous les avis</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.reviews.flagged') }}">Signalés</a></li>
                            </ul>
                        </div>
                    </div>
                    <h3 class="h2 fw-bold mb-1">
                        {{ number_format($stats['platform_rating'] ?? 0, 1) }}
                        <small class="fs-6 text-muted">/5</small>
                    </h3>
                    <p class="text-muted mb-2 small">Satisfaction globale</p>
                    <div class="mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= ($stats['platform_rating'] ?? 0) ? 'text-warning' : 'text-muted opacity-25' }} small"></i>
                        @endfor
                    </div>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>{{ number_format($stats['total_reviews'] ?? 0, 0, ',', ' ') }} avis</span>
                        <span class="text-success">{{ $stats['positive_reviews_percentage'] ?? 0 }}% positifs</span>
                    </div>
                    <div class="mt-3 pt-2 border-top">
                        <small class="text-muted">
                            <i class="fas fa-comments me-1"></i>
                            {{ $stats['reviews_this_month'] ?? 0 }} nouveaux avis
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats secondaires -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-graduation-cap fa-3x text-primary mb-3"></i>
                    <h4 class="fw-bold">{{ number_format($stats['total_enrollments'] ?? 0, 0, ',', ' ') }}</h4>
                    <p class="text-muted mb-0 small">Inscriptions totales</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-certificate fa-3x text-success mb-3"></i>
                    <h4 class="fw-bold">{{ number_format($stats['certificates_issued'] ?? 0, 0, ',', ' ') }}</h4>
                    <p class="text-muted mb-0 small">Certificats délivrés</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-video fa-3x text-danger mb-3"></i>
                    <h4 class="fw-bold">{{ number_format($stats['live_classes_today'] ?? 0) }}</h4>
                    <p class="text-muted mb-0 small">Cours en direct aujourd'hui</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-percent fa-3x text-info mb-3"></i>
                    <h4 class="fw-bold">{{ $stats['completion_rate'] ?? 0 }}%</h4>
                    <p class="text-muted mb-0 small">Taux de complétion</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Colonne principale (8 colonnes) -->
        <div class="col-12 col-xl-8">
            <!-- Graphiques revenus -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-chart-area text-success me-2"></i>
                            Évolution des revenus
                        </h5>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary active">7 jours</button>
                            <button type="button" class="btn btn-outline-primary">30 jours</button>
                            <button type="button" class="btn btn-outline-primary">12 mois</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="80"></canvas>
                </div>
            </div>

            <!-- Activités récentes -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-stream text-info me-2"></i>
                            Activités de la plateforme
                        </h5>
                        <a href="{{ route('admin.activity.index') }}" class="btn btn-sm btn-outline-primary">
                            Voir tout <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Utilisateur</th>
                                    <th>Action</th>
                                    <th>Détails</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivities ?? [] as $activity)
                                <tr>
                                    <td>
                                        <span class="badge bg-{{ $activity->type === 'enrollment' ? 'primary' : ($activity->type === 'payment' ? 'success' : 'info') }}-subtle 
                                                     text-{{ $activity->type === 'enrollment' ? 'primary' : ($activity->type === 'payment' ? 'success' : 'info') }}">
                                            <i class="fas fa-{{ $activity->type === 'enrollment' ? 'user-plus' : ($activity->type === 'payment' ? 'dollar-sign' : 'info-circle') }} me-1"></i>
                                            {{ ucfirst($activity->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $activity->user->avatar ?? '/images/default-avatar.png' }}" 
                                                 alt="Avatar" 
                                                 class="rounded-circle me-2" 
                                                 width="32" 
                                                 height="32">
                                            <div>
                                                <div class="fw-semibold small">{{ $activity->user->full_name ?? 'Utilisateur' }}</div>
                                                <small class="text-muted">{{ $activity->user->email ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $activity->action ?? 'Action' }}</td>
                                    <td class="text-muted small">{{ Str::limit($activity->details ?? '', 50) }}</td>
                                    <td class="text-muted small">
                                        <i class="far fa-clock me-1"></i>
                                        {{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p class="mb-0">Aucune activité récente</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Formations populaires -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-fire text-danger me-2"></i>
                            Formations les plus populaires
                        </h5>
                        <a href="{{ route('admin.formations.analytics') }}" class="btn btn-sm btn-outline-primary">
                            Analytiques <i class="fas fa-chart-bar ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @forelse($popularFormations ?? [] as $formation)
                        <div class="col-12 col-md-6">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-semibold mb-0">{{ $formation->name }}</h6>
                                        <span class="badge bg-primary">{{ $formation->enrollments_count ?? 0 }} inscrits</span>
                                    </div>
                                    <p class="text-muted small mb-2">Par {{ $formation->teacher->full_name ?? 'Enseignant' }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-star text-warning me-1"></i>
                                            <span class="fw-semibold">{{ number_format($formation->average_rating ?? 0, 1) }}</span>
                                            <small class="text-muted">({{ $formation->reviews_count ?? 0 }})</small>
                                        </div>
                                        <span class="text-success fw-semibold">{{ number_format($formation->revenue ?? 0, 0, ',', ' ') }} FCFA</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-chart-line fa-3x mb-3"></i>
                                <p class="mb-0">Aucune donnée disponible</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne latérale (4 colonnes) -->
        <div class="col-12 col-xl-4">
            <!-- Actions administratives rapides -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient-danger text-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-tools me-2"></i>Actions administratives
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary text-start">
                            <i class="fas fa-users-cog me-2"></i>Gérer les utilisateurs
                        </a>
                        <a href="{{ route('admin.formations.pending') }}" class="btn btn-outline-warning text-start position-relative">
                            <i class="fas fa-clock me-2"></i>Valider formations
                            @if(($pendingFormations ?? 0) > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                                {{ $pendingFormations }}
                            </span>
                            @endif
                        </a>
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-success text-start">
                            <i class="fas fa-money-check-alt me-2"></i>Gérer les paiements
                        </a>
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-info text-start">
                            <i class="fas fa-file-alt me-2"></i>Rapports & Analytics
                        </a>
                        <a href="{{ route('admin.support.index') }}" class="btn btn-outline-secondary text-start position-relative">
                            <i class="fas fa-headset me-2"></i>Support utilisateurs
                            @if(($openTickets ?? 0) > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $openTickets }}
                            </span>
                            @endif
                        </a>
                        <a href="{{ route('admin.settings') }}" class="btn btn-outline-secondary text-start">
                            <i class="fas fa-cog me-2"></i>Paramètres système
                        </a>
                    </div>
                </div>
            </div>

            <!-- État système -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-server text-info me-2"></i>
                        État du système
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Serveur web</span>
                            <span class="badge bg-success-subtle text-success">
                                <i class="fas fa-check-circle me-1"></i>Opérationnel
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Base de données</span>
                            <span class="badge bg-success-subtle text-success">
                                <i class="fas fa-check-circle me-1"></i>Connectée
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Zoom API</span>
                            <span class="badge bg-success-subtle text-success">
                                <i class="fas fa-check-circle me-1"></i>Active
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Stripe</span>
                            <span class="badge bg-success-subtle text-success">
                                <i class="fas fa-check-circle me-1"></i>Connecté
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Stockage</span>
                            <div>
                                <span class="text-muted small me-2">{{ $system['storage_used'] ?? 0 }}GB / {{ $system['storage_total'] ?? 100 }}GB</span>
                                <span class="badge bg-{{ ($system['storage_percentage'] ?? 0) > 80 ? 'danger' : 'success' }}-subtle 
                                             text-{{ ($system['storage_percentage'] ?? 0) > 80 ? 'danger' : 'success' }}">
                                    {{ $system['storage_percentage'] ?? 0 }}%
                                </span>
                            </div>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-{{ ($system['storage_percentage'] ?? 0) > 80 ? 'danger' : 'success' }}" 
                                 role="progressbar" 
                                 style="width: {{ $system['storage_percentage'] ?? 0 }}%">
                            </div>
                        </div>
                    </div>
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Dernière sauvegarde</span>
                            <span class="small">{{ $system['last_backup'] ?? 'Aujourd\'hui 03:00' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Demandes en attente -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-tasks text-warning me-2"></i>
                        Demandes en attente
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($pendingRequests ?? [] as $request)
                    <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                        <div class="me-3">
                            <div class="rounded-circle bg-{{ $request->type === 'approval' ? 'warning' : 'info' }} bg-opacity-10 p-2">
                                <i class="fas fa-{{ $request->type === 'approval' ? 'exclamation-circle' : 'question-circle' }} 
                                   text-{{ $request->type === 'approval' ? 'warning' : 'info' }}"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 small fw-semibold">{{ $request->title ?? 'Demande' }}</h6>
                            <p class="text-muted small mb-2">{{ Str::limit($request->description ?? '', 60) }}</p>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.requests.show', $request->id) }}" class="btn btn-xs btn-outline-primary">
                                    Traiter
                                </a>
                                <button class="btn btn-xs btn-outline-secondary">Ignorer</button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-check-double fa-2x mb-2"></i>
                        <p class="mb-0 small">Aucune demande en attente</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Alertes et notifications -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-bell text-danger me-2"></i>
                        Alertes système
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($systemAlerts ?? [] as $alert)
                    <div class="alert alert-{{ $alert->level ?? 'info' }} border-0 mb-2" role="alert">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-{{ $alert->level === 'danger' ? 'exclamation-triangle' : 'info-circle' }} me-2 mt-1"></i>
                            <div class="flex-grow-1">
                                <strong class="small">{{ $alert->title ?? 'Alerte' }}</strong>
                                <p class="mb-0 small">{{ $alert->message ?? 'Message d\'alerte' }}</p>
                            </div>
                            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-shield-alt fa-2x mb-2 text-success"></i>
                        <p class="mb-0 small">Aucune alerte système</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Envoyer alerte -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="alertModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                    Envoyer une alerte
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.alerts.send') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="alertType" class="form-label">Type d'alerte</label>
                        <select class="form-select" id="alertType" name="type" required>
                            <option value="">Sélectionner...</option>
                            <option value="info">Information</option>
                            <option value="warning">Avertissement</option>
                            <option value="danger">Urgent</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="alertTitle" class="form-label">Titre</label>
                        <input type="text" class="form-control" id="alertTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="alertMessage" class="form-label">Message</label>
                        <textarea class="form-control" id="alertMessage" name="message" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Destinataires</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="allUsers" name="recipients[]" value="all">
                            <label class="form-check-label" for="allUsers">Tous les utilisateurs</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="studentsOnly" name="recipients[]" value="students">
                            <label class="form-check-label" for="studentsOnly">Étudiants uniquement</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="teachersOnly" name="recipients[]" value="teachers">
                            <label class="form-check-label" for="teachersOnly">Enseignants uniquement</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-paper-plane me-2"></i>Envoyer l'alerte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Styles personnalisés */
.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.bg-gradient-danger {
    background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
}

.icon-box {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.btn-xs {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

/* Responsive */
@media (max-width: 768px) {
    .icon-box {
        width: 50px;
        height: 50px;
    }
    
    .icon-box i {
        font-size: 1.2rem !important;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Graphique des revenus
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels'] ?? ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']) !!},
                datasets: [{
                    label: 'Revenus (FCFA)',
                    data: {!! json_encode($chartData['revenue'] ?? [12000, 19000, 15000, 25000, 22000, 30000, 28000]) !!},
                    borderColor: '#800020',
                    backgroundColor: 'rgba(128, 0, 32, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' FCFA';
                            }
                        }
                    }
                }
            }
        });
    }

    // Animation des barres de progression
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach((bar, index) => {
        const width = bar.style.width;
        bar.style.width = '0';
        setTimeout(() => {
            bar.style.width = width;
            bar.style.transition = 'width 1s ease';
        }, 100 * (index + 1));
    });
});

// Auto-refresh toutes les 5 minutes
setInterval(() => {
    if (document.visibilityState === 'visible') {
        location.reload();
    }
}, 300000);
</script>
@endsection