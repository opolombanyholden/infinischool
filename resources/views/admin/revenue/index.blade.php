@extends('layouts.dashboard')

@section('title', 'Gestion des Revenus')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">
                    <i class="fas fa-coins text-warning me-2"></i>
                    Gestion des Revenus
                </h1>
                <p class="text-muted mb-0">Analyse et suivi des revenus de la plateforme</p>
            </div>
            <a href="{{ route('admin.finance.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-chart-pie me-2"></i>Tableau de bord finances
            </a>
        </div>

        @php
            $totalRevenue = \App\Models\Payment::where('status', 'completed')->sum('amount');
            $monthRevenue = \App\Models\Payment::where('status', 'completed')
                ->whereMonth('paid_at', now()->month)
                ->sum('amount');
            $weekRevenue = \App\Models\Payment::where('status', 'completed')
                ->where('paid_at', '>=', now()->subWeek())
                ->sum('amount');
            
            $topFormations = \App\Models\Payment::where('status', 'completed')
                ->selectRaw('formation_id, SUM(amount) as total, COUNT(*) as count')
                ->with('formation')
                ->groupBy('formation_id')
                ->orderByDesc('total')
                ->limit(10)
                ->get();
            
            $monthlyRevenue = \App\Models\Payment::where('status', 'completed')
                ->whereYear('paid_at', now()->year)
                ->selectRaw('MONTH(paid_at) as month, SUM(amount) as total')
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        @endphp

        <!-- KPI Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 bg-gradient-success text-white" 
                     style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    <div class="card-body py-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-white-50 mb-2">Chiffre d'affaires total</h6>
                                <h2 class="fw-bold mb-0">{{ number_format($totalRevenue, 0, ',', ' ') }}</h2>
                                <small>FCFA</small>
                            </div>
                            <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                <i class="fas fa-chart-line fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted mb-2">Ce mois</h6>
                                <h2 class="fw-bold text-primary mb-0">{{ number_format($monthRevenue, 0, ',', ' ') }}</h2>
                                <small class="text-muted">FCFA</small>
                            </div>
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-calendar-alt fa-lg text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted mb-2">Cette semaine</h6>
                                <h2 class="fw-bold text-info mb-0">{{ number_format($weekRevenue, 0, ',', ' ') }}</h2>
                                <small class="text-muted">FCFA</small>
                            </div>
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-clock fa-lg text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Monthly Revenue -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-chart-bar text-primary me-2"></i>
                            Revenus mensuels {{ now()->year }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($monthlyRevenue->count() > 0)
                            @php 
                                $maxMonthly = $monthlyRevenue->max('total');
                                $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 
                                           'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                            @endphp
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tbody>
                                        @foreach($monthlyRevenue as $month)
                                            <tr>
                                                <td style="width: 100px;">{{ $months[$month->month] ?? 'N/A' }}</td>
                                                <td>
                                                    <div class="progress" style="height: 25px;">
                                                        <div class="progress-bar bg-success" 
                                                             style="width: {{ $maxMonthly > 0 ? ($month->total / $maxMonthly) * 100 : 0 }}%">
                                                            {{ number_format($month->total, 0, ',', ' ') }} FCFA
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-chart-bar fa-3x mb-3"></i>
                                <p>Aucune donnée pour cette année</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Top Formations -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-trophy text-warning me-2"></i>
                            Top formations
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if($topFormations->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($topFormations as $index => $item)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            @if($index < 3)
                                                <span class="badge bg-{{ $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'danger') }} me-3">
                                                    {{ $index + 1 }}
                                                </span>
                                            @else
                                                <span class="badge bg-light text-dark me-3">{{ $index + 1 }}</span>
                                            @endif
                                            <div>
                                                <div class="fw-semibold">{{ Str::limit($item->formation->title ?? 'N/A', 25) }}</div>
                                                <small class="text-muted">{{ $item->count }} ventes</small>
                                            </div>
                                        </div>
                                        <span class="badge bg-success-subtle text-success">
                                            {{ number_format($item->total, 0, ',', ' ') }} FCFA
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <p>Aucune donnée</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <a href="{{ route('admin.payments.index') }}" class="text-decoration-none">
                            <div class="p-3">
                                <i class="fas fa-credit-card fa-2x text-primary mb-2"></i>
                                <h6 class="mb-0">Voir les paiements</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('admin.reports.revenue') }}" class="text-decoration-none">
                            <div class="p-3">
                                <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                                <h6 class="mb-0">Rapport Revenus</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('admin.finance.index') }}" class="text-decoration-none">
                            <div class="p-3">
                                <i class="fas fa-calculator fa-2x text-warning mb-2"></i>
                                <h6 class="mb-0">Finance Dashboard</h6>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection