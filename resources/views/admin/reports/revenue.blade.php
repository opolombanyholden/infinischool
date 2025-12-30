@extends('layouts.dashboard')

@section('title', 'Rapport Revenus')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('admin.reports.index') }}" class="text-decoration-none text-muted small">
                    <i class="fas fa-arrow-left me-1"></i>Retour aux rapports
                </a>
                <h1 class="h3 mb-1 fw-bold mt-2">
                    <i class="fas fa-chart-line text-success me-2"></i>
                    Rapport Revenus
                </h1>
                <p class="text-muted mb-0">Analyse détaillée des revenus et paiements</p>
            </div>
            <form method="GET" class="d-flex gap-2">
                <select name="period" class="form-select" onchange="this.form.submit()">
                    <option value="week" {{ $period === 'week' ? 'selected' : '' }}>7 derniers jours</option>
                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>30 derniers jours</option>
                    <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>3 derniers mois</option>
                    <option value="year" {{ $period === 'year' ? 'selected' : '' }}>Cette année</option>
                    <option value="all" {{ $period === 'all' ? 'selected' : '' }}>Tout</option>
                </select>
            </form>
        </div>

        <!-- KPIs -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Chiffre d'affaires</h6>
                        <h2 class="fw-bold text-success">{{ number_format($totalRevenue, 0, ',', ' ') }} FCFA</h2>
                        <small class="text-muted">Sur la période sélectionnée</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Nombre de transactions</h6>
                        <h2 class="fw-bold">{{ $payments->count() }}</h2>
                        <small class="text-muted">Paiements complétés</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Panier moyen</h6>
                        <h2 class="fw-bold">{{ number_format($avgTransaction, 0, ',', ' ') }} FCFA</h2>
                        <small class="text-muted">Par transaction</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Daily Revenue Chart -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-chart-area text-success me-2"></i>
                            Évolution des revenus
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($dailyRevenue->count() > 0)
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Date</th>
                                            <th class="text-end">Montant</th>
                                            <th style="width: 50%;">Progression</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $maxDaily = $dailyRevenue->max('total'); @endphp
                                        @foreach($dailyRevenue as $day)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($day->date)->format('d/m/Y') }}</td>
                                                <td class="text-end fw-semibold">{{ number_format($day->total, 0, ',', ' ') }} FCFA</td>
                                                <td>
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar bg-success" 
                                                             style="width: {{ $maxDaily > 0 ? ($day->total / $maxDaily) * 100 : 0 }}%"></div>
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
                                <p>Aucune donnée pour cette période</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-credit-card text-primary me-2"></i>
                            Méthodes de paiement
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($paymentMethods->count() > 0)
                            @foreach($paymentMethods as $method)
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                    <div>
                                        <h6 class="mb-0 fw-bold">{{ ucfirst($method->payment_method ?? 'Autre') }}</h6>
                                        <small class="text-muted">{{ $method->count }} transactions</small>
                                    </div>
                                    <span class="badge bg-success-subtle text-success">
                                        {{ number_format($method->total, 0, ',', ' ') }} FCFA
                                    </span>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted text-center">Aucune donnée</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Formations -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-trophy text-warning me-2"></i>
                    Top 10 Formations par revenus
                </h5>
            </div>
            <div class="card-body p-0">
                @if($topFormations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Formation</th>
                                    <th class="text-center">Ventes</th>
                                    <th class="text-end">Revenus</th>
                                    <th style="width: 30%;">Part</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topFormations as $index => $item)
                                    <tr>
                                        <td>
                                            @if($index < 3)
                                                <span class="badge bg-{{ $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'danger') }}">
                                                    {{ $index + 1 }}
                                                </span>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </td>
                                        <td class="fw-semibold">{{ $item->formation->name ?? 'Formation supprimée' }}</td>
                                        <td class="text-center">{{ $item->count }}</td>
                                        <td class="text-end fw-bold text-success">{{ number_format($item->total, 0, ',', ' ') }} FCFA</td>
                                        <td>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-success" 
                                                     style="width: {{ $totalRevenue > 0 ? ($item->total / $totalRevenue) * 100 : 0 }}%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <p>Aucune donnée disponible</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
