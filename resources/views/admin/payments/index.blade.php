@extends('layouts.dashboard')

@section('title', 'Gestion des Paiements')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">
                    <i class="fas fa-credit-card text-success me-2"></i>
                    Gestion des Paiements
                </h1>
                <p class="text-muted mb-0">Suivi et gestion des transactions</p>
            </div>
            <a href="{{ route('admin.finance.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-chart-pie me-2"></i>Tableau de bord finances
            </a>
        </div>

        @php
            $payments = \App\Models\Payment::with(['student', 'formation'])
                ->latest('created_at')
                ->paginate(20);

            $stats = [
                'total' => \App\Models\Payment::count(),
                'completed' => \App\Models\Payment::where('status', 'completed')->count(),
                'pending' => \App\Models\Payment::where('status', 'pending')->count(),
                'failed' => \App\Models\Payment::where('status', 'failed')->count(),
                'total_amount' => \App\Models\Payment::where('status', 'completed')->sum('amount'),
            ];
        @endphp

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-secondary mb-0">{{ $stats['total'] }}</h4>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm h-100 border-start border-3 border-success">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-success mb-0">{{ $stats['completed'] }}</h4>
                        <small class="text-muted">Complétés</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-warning mb-0">{{ $stats['pending'] }}</h4>
                        <small class="text-muted">En attente</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-danger mb-0">{{ $stats['failed'] }}</h4>
                        <small class="text-muted">Échoués</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card border-0 shadow-sm h-100 bg-success text-white">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold mb-0">{{ number_format($stats['total_amount'], 0, ',', ' ') }}</h4>
                        <small>FCFA Total</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payments List -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-list text-secondary me-2"></i>
                    Historique des paiements
                </h5>
            </div>
            <div class="card-body p-0">
                @if($payments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">ID</th>
                                    <th>Utilisateur</th>
                                    <th>Formation</th>
                                    <th class="text-end">Montant</th>
                                    <th>Méthode</th>
                                    <th style="width: 110px;">Statut</th>
                                    <th style="width: 140px;">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                    <tr>
                                        <td class="fw-semibold">
                                            <code>#{{ $payment->id }}</code>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-secondary bg-opacity-25 me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px;">
                                                    <i class="fas fa-user text-secondary small"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold small">{{ $payment->student->name ?? 'N/A' }}</div>
                                                    <small class="text-muted">{{ $payment->student->email ?? '' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-muted small">{{ Str::limit($payment->formation->title ?? 'N/A', 30) }}</td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($payment->amount, 0, ',', ' ') }} <span class="text-muted">FCFA</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                {{ ucfirst($payment->payment_method ?? 'N/A') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($payment->status === 'completed')
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="fas fa-check-circle me-1"></i>Complété
                                                </span>
                                            @elseif($payment->status === 'pending')
                                                <span class="badge bg-warning-subtle text-warning">
                                                    <i class="fas fa-clock me-1"></i>En attente
                                                </span>
                                            @elseif($payment->status === 'failed')
                                                <span class="badge bg-danger-subtle text-danger">
                                                    <i class="fas fa-times-circle me-1"></i>Échoué
                                                </span>
                                            @elseif($payment->status === 'refunded')
                                                <span class="badge bg-info-subtle text-info">
                                                    <i class="fas fa-undo me-1"></i>Remboursé
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">{{ $payment->status }}</span>
                                            @endif
                                        </td>
                                        <td class="small text-muted">
                                            {{ ($payment->paid_at ?? $payment->created_at)->format('d/m/Y H:i') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($payments->hasPages())
                        <div class="card-footer bg-white">
                            {{ $payments->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-credit-card fa-3x mb-3"></i>
                        <p class="mb-0">Aucun paiement trouvé</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection