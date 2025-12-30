@extends('layouts.dashboard')

@section('title', 'Gestion des Avis')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">
                    <i class="fas fa-star text-warning me-2"></i>
                    Gestion des Avis
                </h1>
                <p class="text-muted mb-0">Modération et suivi des avis sur les formations</p>
            </div>
            <a href="{{ route('admin.reviews.flagged') }}" class="btn btn-outline-danger">
                <i class="fas fa-flag me-2"></i>Avis signalés
                @if($stats['pending'] > 0)
                    <span class="badge bg-danger ms-1">{{ $stats['pending'] }}</span>
                @endif
            </a>
        </div>

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
                        <h4 class="fw-bold text-success mb-0">{{ $stats['approved'] }}</h4>
                        <small class="text-muted">Approuvés</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-danger mb-0">{{ $stats['rejected'] }}</h4>
                        <small class="text-muted">Rejetés</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-warning mb-0">
                            <i class="fas fa-star fa-sm"></i> {{ $stats['average_rating'] }}
                        </h4>
                        <small class="text-muted">Note moyenne</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="status" class="form-label small text-muted">Statut</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Tous</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvés</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejetés</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="formation_id" class="form-label small text-muted">Formation</label>
                        <select name="formation_id" id="formation_id" class="form-select">
                            <option value="">Toutes</option>
                            @foreach($formations as $formation)
                                <option value="{{ $formation->id }}" {{ request('formation_id') == $formation->id ? 'selected' : '' }}>
                                    {{ Str::limit($formation->title, 30) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="rating" class="form-label small text-muted">Note</label>
                        <select name="rating" id="rating" class="form-select">
                            <option value="">Toutes</option>
                            @for($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} étoile(s)</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="search" class="form-label small text-muted">Recherche</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Mot-clé...">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Filtrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reviews List -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-comments text-secondary me-2"></i>
                    Liste des avis
                </h5>
            </div>
            <div class="card-body p-0">
                @if($reviews->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Formation</th>
                                    <th>Utilisateur</th>
                                    <th style="width: 100px;">Note</th>
                                    <th>Commentaire</th>
                                    <th style="width: 100px;">Statut</th>
                                    <th style="width: 140px;">Date</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reviews as $review)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold small">{{ Str::limit($review->formation->title ?? 'N/A', 25) }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-secondary bg-opacity-25 me-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 32px; height: 32px;">
                                                    <i class="fas fa-user text-secondary small"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold small">{{ $review->user->name ?? 'Utilisateur' }}</div>
                                                    <small class="text-muted">{{ $review->user->email ?? '' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-warning">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o text-muted' }} small"></i>
                                                @endfor
                                            </div>
                                            <small class="text-muted">{{ $review->rating }}/5</small>
                                        </td>
                                        <td class="text-muted small">
                                            {{ Str::limit($review->comment ?? 'Pas de commentaire', 60) }}
                                        </td>
                                        <td>
                                            @if($review->status === 'approved')
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="fas fa-check me-1"></i>Approuvé
                                                </span>
                                            @elseif($review->status === 'pending')
                                                <span class="badge bg-warning-subtle text-warning">
                                                    <i class="fas fa-clock me-1"></i>En attente
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">
                                                    <i class="fas fa-times me-1"></i>Rejeté
                                                </span>
                                            @endif
                                        </td>
                                        <td class="small text-muted">
                                            {{ $review->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                @if($review->status !== 'approved')
                                                    <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-success" title="Approuver">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($review->status !== 'rejected')
                                                    <form action="{{ route('admin.reviews.reject', $review) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-warning" title="Rejeter">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Supprimer cet avis ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($reviews->hasPages())
                        <div class="card-footer bg-white">
                            {{ $reviews->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-comments fa-3x mb-3"></i>
                        <p class="mb-0">Aucun avis trouvé</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection