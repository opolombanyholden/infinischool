@extends('layouts.dashboard')

@section('title', 'Avis Signalés')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">
                    <i class="fas fa-flag text-danger me-2"></i>
                    Avis Signalés
                </h1>
                <p class="text-muted mb-0">Avis nécessitant une modération particulière</p>
            </div>
            <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour à tous les avis
            </a>
        </div>

        <!-- Alert Info -->
        <div class="alert alert-info border-0 mb-4">
            <i class="fas fa-info-circle me-2"></i>
            Ces avis sont marqués comme potentiellement problématiques (note très basse ou contenu sensible).
        </div>

        <!-- Reviews List -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-danger text-white py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Avis à modérer ({{ $reviews->total() ?? $reviews->count() }})
                </h5>
            </div>
            <div class="card-body p-0">
                @if($reviews->count() > 0)
                    @foreach($reviews as $review)
                        <div class="border-bottom p-4">
                            <div class="row align-items-start">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="rounded-circle bg-danger bg-opacity-10 me-3 d-flex align-items-center justify-content-center"
                                            style="width: 48px; height: 48px;">
                                            <i class="fas fa-user text-danger"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="fw-bold mb-1">{{ $review->user->name ?? 'Utilisateur' }}</h6>
                                                    <small class="text-muted">{{ $review->user->email ?? '' }}</small>
                                                </div>
                                                <div class="text-end">
                                                    <div class="text-warning mb-1">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i
                                                                class="fas fa-star{{ $i <= $review->rating ? '' : ' text-muted opacity-25' }}"></i>
                                                        @endfor
                                                    </div>
                                                    <small class="text-muted">{{ $review->created_at->format('d/m/Y H:i') }}</small>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <span class="badge bg-secondary-subtle text-secondary mb-2">
                                                    {{ $review->formation->name ?? 'Formation' }}
                                                </span>
                                                <p class="mb-0 text-dark">
                                                    {{ $review->comment ?? 'Pas de commentaire' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex flex-column gap-2">
                                        <form action="{{ route('admin.reviews.approve', $review) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success w-100">
                                                <i class="fas fa-check me-2"></i>Approuver
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.reviews.reject', $review) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-warning w-100">
                                                <i class="fas fa-ban me-2"></i>Rejeter
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST"
                                            onsubmit="return confirm('Supprimer définitivement cet avis ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger w-100">
                                                <i class="fas fa-trash me-2"></i>Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if($reviews->hasPages())
                        <div class="card-footer bg-white">
                            {{ $reviews->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                        <p class="mb-0">Aucun avis signalé à modérer</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection