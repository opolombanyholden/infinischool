@extends('layouts.dashboard')

@section('title', 'Rapport Assiduité')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('admin.reports.index') }}" class="text-decoration-none text-muted small">
                    <i class="fas fa-arrow-left me-1"></i>Retour aux rapports
                </a>
                <h1 class="h3 mb-1 fw-bold mt-2">
                    <i class="fas fa-clipboard-check text-warning me-2"></i>
                    Rapport Assiduité
                </h1>
                <p class="text-muted mb-0">Taux de complétion et engagement des étudiants</p>
            </div>
        </div>

        <!-- Info Alert -->
        <div class="alert alert-info border-0 mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle fa-2x me-3"></i>
                <div>
                    <h6 class="mb-1 fw-bold">Module en développement</h6>
                    <p class="mb-0">
                        Le suivi détaillé de l'assiduité nécessite l'intégration de données de progression des cours.
                        Cette fonctionnalité sera disponible prochainement.
                    </p>
                </div>
            </div>
        </div>

        <!-- Placeholder Stats -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-5">
                        <div class="rounded-circle bg-warning bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3"
                            style="width: 80px; height: 80px;">
                            <i class="fas fa-video fa-2x text-warning"></i>
                        </div>
                        <h2 class="fw-bold">{{ $stats['total_sessions'] }}</h2>
                        <p class="text-muted mb-0">Sessions suivies</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-5">
                        <div class="rounded-circle bg-success bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3"
                            style="width: 80px; height: 80px;">
                            <i class="fas fa-percentage fa-2x text-success"></i>
                        </div>
                        <h2 class="fw-bold">{{ $stats['avg_attendance'] }}%</h2>
                        <p class="text-muted mb-0">Taux de présence moyen</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-5">
                        <div class="rounded-circle bg-primary bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3"
                            style="width: 80px; height: 80px;">
                            <i class="fas fa-check-double fa-2x text-primary"></i>
                        </div>
                        <h2 class="fw-bold">{{ $stats['completion_rate'] }}%</h2>
                        <p class="text-muted mb-0">Taux de complétion</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coming Soon Features -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-rocket text-primary me-2"></i>
                    Fonctionnalités à venir
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Suivi de progression par étudiant
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Temps de visionnage des vidéos
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Taux de complétion par cours
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Résultats des quiz et examens
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Certificats générés
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Alertes d'abandon
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection