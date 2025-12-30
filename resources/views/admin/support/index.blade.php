@extends('layouts.dashboard')

@section('title', 'Support Utilisateurs')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">
                    <i class="fas fa-headset text-primary me-2"></i>
                    Support Utilisateurs
                </h1>
                <p class="text-muted mb-0">Gestion des tickets de support</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-secondary mb-0">{{ $stats['total'] }}</h4>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-danger">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-danger mb-0">{{ $stats['open'] }}</h4>
                        <small class="text-muted">Ouverts</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-warning mb-0">{{ $stats['in_progress'] }}</h4>
                        <small class="text-muted">En cours</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-success mb-0">{{ $stats['closed'] }}</h4>
                        <small class="text-muted">Résolus</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-danger mb-0">{{ $stats['high_priority'] }}</h4>
                        <small class="text-muted">Urgents</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-info mb-0">{{ $stats['unassigned'] }}</h4>
                        <small class="text-muted">Non assignés</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label for="status" class="form-label small text-muted">Statut</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Tous</option>
                            <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Ouvert</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours
                            </option>
                            <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Résolu</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="priority" class="form-label small text-muted">Priorité</label>
                        <select name="priority" id="priority" class="form-select">
                            <option value="">Toutes</option>
                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Haute</option>
                            <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Moyenne</option>
                            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Basse</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="assigned" class="form-label small text-muted">Assignation</label>
                        <select name="assigned" id="assigned" class="form-select">
                            <option value="">Tous</option>
                            <option value="unassigned" {{ request('assigned') === 'unassigned' ? 'selected' : '' }}>Non
                                assignés</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" {{ request('assigned') == $admin->id ? 'selected' : '' }}>
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="search" class="form-label small text-muted">Recherche</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}"
                            placeholder="Sujet, message, utilisateur...">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Filtrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tickets List -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-ticket-alt text-secondary me-2"></i>
                    Tickets de support
                </h5>
            </div>
            <div class="card-body p-0">
                @if($tickets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;">#</th>
                                    <th>Sujet</th>
                                    <th>Utilisateur</th>
                                    <th style="width: 100px;">Priorité</th>
                                    <th style="width: 110px;">Statut</th>
                                    <th>Assigné à</th>
                                    <th style="width: 140px;">Date</th>
                                    <th style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tickets as $ticket)
                                    <tr>
                                        <td class="fw-semibold">#{{ $ticket->id }}</td>
                                        <td>
                                            <a href="{{ route('admin.support.show', $ticket) }}"
                                                class="text-decoration-none fw-semibold">
                                                {{ Str::limit($ticket->subject, 40) }}
                                            </a>
                                            <div class="text-muted small">{{ Str::limit($ticket->message, 50) }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-secondary bg-opacity-25 me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px;">
                                                    <i class="fas fa-user text-secondary small"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold small">{{ $ticket->user->name ?? 'N/A' }}</div>
                                                    <small class="text-muted">{{ $ticket->user->email ?? '' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($ticket->priority === 'high')
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>Haute
                                                </span>
                                            @elseif($ticket->priority === 'medium')
                                                <span class="badge bg-warning text-dark">Moyenne</span>
                                            @else
                                                <span class="badge bg-secondary">Basse</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ticket->status === 'open')
                                                <span class="badge bg-danger-subtle text-danger">
                                                    <i class="fas fa-envelope-open me-1"></i>Ouvert
                                                </span>
                                            @elseif($ticket->status === 'in_progress')
                                                <span class="badge bg-warning-subtle text-warning">
                                                    <i class="fas fa-spinner me-1"></i>En cours
                                                </span>
                                            @else
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="fas fa-check-circle me-1"></i>Résolu
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ticket->assignedTo)
                                                <span class="badge bg-info-subtle text-info">
                                                    {{ $ticket->assignedTo->name }}
                                                </span>
                                            @else
                                                <span class="text-muted small">Non assigné</span>
                                            @endif
                                        </td>
                                        <td class="small text-muted">
                                            {{ $ticket->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.support.show', $ticket) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($ticket->status !== 'closed')
                                                <form action="{{ route('admin.support.resolve', $ticket) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Résoudre">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($tickets->hasPages())
                        <div class="card-footer bg-white">
                            {{ $tickets->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p class="mb-0">Aucun ticket trouvé</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection