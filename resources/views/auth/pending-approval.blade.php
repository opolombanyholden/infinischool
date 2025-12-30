@extends('layouts.app')

@section('title', 'Inscription En Attente - InfiniSchool')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-lg">
                    <div class="card-body text-center py-5 px-4">
                        <!-- Icon -->
                        <div class="mb-4">
                            <i class="fas fa-clock text-warning" style="font-size: 5rem;"></i>
                        </div>

                        <!-- Title -->
                        <h3 class="mb-3 fw-bold">Inscription En Cours de Traitement</h3>

                        <!-- Description -->
                        <p class="text-muted mb-4">
                            Bonjour <strong>{{ Auth::user()->name }}</strong>,<br>
                            Votre dossier d'inscription est actuellement en cours d'examen par notre équipe.
                        </p>

                        <!-- Status -->
                        <div class="alert alert-warning border-0 mb-4">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="fas fa-hourglass-half me-2"></i>
                                <span><strong>Statut :</strong> En Attente de Validation</span>
                            </div>
                        </div>

                        <!-- Email Verification Status -->
                        <div class="mb-4">
                            @if(Auth::user()->email_verified_at)
                                <div class="alert alert-success border-0">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <span class="fw-semibold">Votre email a été vérifié</span>
                                </div>
                            @else
                                <div class="alert alert-danger border-0">
                                    <i class="fas fa-times-circle me-2"></i>
                                    <span class="fw-semibold">Votre email n'a pas encore été vérifié</span>
                                </div>

                                <p class="small text-muted mb-3">
                                    Veuillez vérifier votre boîte mail et cliquer sur le lien de vérification.
                                </p>

                                @if (session('resent'))
                                    <div class="alert alert-success border-0">
                                        Un nouveau lien de vérification a été envoyé !
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('verification.resend') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-paper-plane me-2"></i>
                                        Renvoyer l'Email de Vérification
                                    </button>
                                </form>
                            @endif
                        </div>

                        <!-- Info Box -->
                        <div class="info-box p-3 mb-4">
                            <h6 class="fw-semibold mb-2">
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                Délai de Traitement
                            </h6>
                            <p class="small text-muted mb-0">
                                L'examen de votre dossier prend généralement <strong>24 à 48 heures</strong>.
                                Vous recevrez un email dès que votre inscription sera validée ou si des informations
                                complémentaires sont nécessaires.
                            </p>
                        </div>

                        <hr class="my-4">

                        <!-- Informations Soumises -->
                        <div class="text-start mb-4">
                            <h6 class="fw-semibold mb-3">Informations Soumises</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">Formation :</td>
                                    <td class="fw-semibold">
                                        {{ Auth::user()->desiredFormation->title ?? 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Niveau visé :</td>
                                    <td class="fw-semibold">
                                        {{ ucfirst(Auth::user()->desired_level ?? 'N/A') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Email :</td>
                                    <td>{{ Auth::user()->email }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Numéro étudiant :</td>
                                    <td><code>{{ Auth::user()->student_number }}</code></td>
                                </tr>
                            </table>
                        </div>

                        <!-- Actions -->
                        <div class="d-grid gap-2">
                            <a href="{{ route('contact') }}" class="btn btn-outline-primary">
                                <i class="fas fa-envelope me-2"></i>
                                Nous Contacter
                            </a>

                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-sign-out-alt me-2"></i>
                                    Se Déconnecter
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .info-box {
            background: rgba(13, 110, 253, 0.05);
            border-left: 4px solid #0d6efd;
            border-radius: 8px;
        }

        .table-borderless td {
            padding: 0.5rem 0;
        }
    </style>
@endsection