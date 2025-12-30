@extends('layouts.dashboard')

@section('title', 'Rapports & Analytics')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">
                    <i class="fas fa-chart-bar text-primary me-2"></i>
                    Rapports & Analytics
                </h1>
                <p class="text-muted mb-0">Vue d'ensemble des performances de la plateforme</p>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted mb-2">Utilisateurs</h6>
                                <h2 class="fw-bold mb-1">{{ number_format($metrics['users']['total']) }}</h2>
                                <small class="text-success">
                                    <i class="fas fa-arrow-up me-1"></i>+{{ $metrics['users']['this_month'] }} ce mois
                                </small>
                            </div>
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="fas fa-users fa-lg text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted mb-2">Formations</h6>
                                <h2 class="fw-bold mb-1">{{ number_format($metrics['formations']['total']) }}</h2>
                                <small class="text-muted">
                                    {{ $metrics['formations']['published'] }} publiées
                                </small>
                            </div>
                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                <i class="fas fa-graduation-cap fa-lg text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted mb-2">Inscriptions</h6>
                                <h2 class="fw-bold mb-1">{{ number_format($metrics['enrollments']['total']) }}</h2>
                                <small class="text-success">
                                    <i class="fas fa-arrow-up me-1"></i>+{{ $metrics['enrollments']['this_month'] }} ce mois
                                </small>
                            </div>
                            <div class="rounded-circle bg-info bg-opacity-10 p-3">
                                <i class="fas fa-user-plus fa-lg text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted mb-2">Revenus</h6>
                                <h2 class="fw-bold mb-1">{{ number_format($metrics['revenue']['total'], 0, ',', ' ') }}</h2>
                                <small class="text-success">
                                    <i
                                        class="fas fa-arrow-up me-1"></i>{{ number_format($metrics['revenue']['this_month'], 0, ',', ' ') }}
                                    FCFA ce mois
                                </small>
                            </div>
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                                <i class="fas fa-money-bill-wave fa-lg text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Links -->
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('admin.reports.revenue') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 transition-all hover-shadow">
                        <div class="card-body text-center py-5">
                            <div class="rounded-circle bg-success bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3"
                                style="width: 80px; height: 80px;">
                                <i class="fas fa-chart-line fa-2x text-success"></i>
                            </div>
                            <h5 class="fw-bold text-dark">Rapport Revenus</h5>
                            <p class="text-muted small mb-0">Analyse des revenus et paiements</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('admin.reports.users') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 transition-all hover-shadow">
                        <div class="card-body text-center py-5">
                            <div class="rounded-circle bg-primary bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3"
                                style="width: 80px; height: 80px;">
                                <i class="fas fa-users fa-2x text-primary"></i>
                            </div>
                            <h5 class="fw-bold text-dark">Rapport Utilisateurs</h5>
                            <p class="text-muted small mb-0">Statistiques des inscriptions</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('admin.reports.courses') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 transition-all hover-shadow">
                        <div class="card-body text-center py-5">
                            <div class="rounded-circle bg-info bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3"
                                style="width: 80px; height: 80px;">
                                <i class="fas fa-book-open fa-2x text-info"></i>
                            </div>
                            <h5 class="fw-bold text-dark">Rapport Formations</h5>
                            <p class="text-muted small mb-0">Performance des cours</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('admin.reports.attendance') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 transition-all hover-shadow">
                        <div class="card-body text-center py-5">
                            <div class="rounded-circle bg-warning bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3"
                                style="width: 80px; height: 80px;">
                                <i class="fas fa-clipboard-check fa-2x text-warning"></i>
                            </div>
                            <h5 class="fw-bold text-dark">Rapport Assiduité</h5>
                            <p class="text-muted small mb-0">Taux de complétion</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Users breakdown -->
        <div class="row g-4 mt-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-users text-primary me-2"></i>
                            Répartition Utilisateurs
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Étudiants</span>
                                <span class="fw-bold">{{ $metrics['users']['students'] }}</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-primary"
                                    style="width: {{ $metrics['users']['total'] > 0 ? ($metrics['users']['students'] / $metrics['users']['total']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Enseignants</span>
                                <span class="fw-bold">{{ $metrics['users']['teachers'] }}</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success"
                                    style="width: {{ $metrics['users']['total'] > 0 ? ($metrics['users']['teachers'] / $metrics['users']['total']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-graduation-cap text-success me-2"></i>
                            État des Formations
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Publiées</span>
                                <span class="fw-bold text-success">{{ $metrics['formations']['published'] }}</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success"
                                    style="width: {{ $metrics['formations']['total'] > 0 ? ($metrics['formations']['published'] / $metrics['formations']['total']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span>En attente</span>
                                <span class="fw-bold text-warning">{{ $metrics['formations']['pending'] }}</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-warning"
                                    style="width: {{ $metrics['formations']['total'] > 0 ? ($metrics['formations']['pending'] / $metrics['formations']['total']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
        }

        .transition-all {
            transition: all 0.3s ease;
        }
    </style>
@endsection