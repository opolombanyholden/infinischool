@extends('layouts.auth')

@section('title', 'Inscription Confirmée - InfiniSchool')

@section('content')
    <div class="text-center">
        <!-- Success Icon -->
        <div class="mb-4">
            <div class="success-icon">
                <i class="fas fa-check-circle text-success"></i>
            </div>
        </div>

        <!-- Title -->
        <h2 class="mb-3 fw-bold">Inscription Réussie !</h2>

        <p class="text-muted mb-2">
            Merci {{ session('name') }} de vous être inscrit(e) sur InfiniSchool.
        </p>

        <p class="text-muted mb-4">
            Un email de vérification a été envoyé à :<br>
            <strong class="text-dark">{{ session('email') }}</strong>
        </p>

        <!-- Steps Card -->
        <div class="card border-0 shadow-sm text-start mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-list-ol me-2"></i>
                <strong>Prochaines Étapes</strong>
            </div>
            <div class="card-body">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <strong>Vérifiez votre email</strong>
                        <p class="small text-muted mb-0">Cliquez sur le lien de vérification dans l'email que nous vous
                            avons envoyé</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <strong>Examen de votre dossier</strong>
                        <p class="small text-muted mb-0">Notre équipe va examiner votre inscription (généralement sous
                            24-48h)</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <strong>Notification de validation</strong>
                        <p class="small text-muted mb-0">Vous recevrez un email dès que votre compte sera validé</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <strong>Accès à votre espace</strong>
                        <p class="small text-muted mb-0">Une fois validé, vous pourrez accéder à l'espace e-learning</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Alert -->
        <div class="alert alert-info border-0 text-start">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Remarque importante :</strong><br>
            Vous ne pourrez pas vous connecter tant que votre inscription n'aura pas été validée par notre équipe.
        </div>

        <!-- Actions -->
        <div class="d-grid gap-2">
            <a href="{{ route('home') }}" class="btn btn-primary">
                <i class="fas fa-home me-2"></i>Retour à l'Accueil
            </a>
            <a href="{{ route('contact') }}" class="btn btn-outline-secondary">
                <i class="fas fa-envelope me-2"></i>Nous Contacter
            </a>
        </div>

        <!-- Back to Login -->
        <div class="mt-4">
            <p class="small text-muted">
                Vous avez déjà un compte validé ?
                <a href="{{ route('login') }}" class="auth-link">Se connecter</a>
            </p>
        </div>
    </div>

    <style>
        .success-icon i {
            font-size: 5rem;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .step-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .step-item:last-child {
            border-bottom: none;
        }

        .step-number {
            flex-shrink: 0;
            width: 35px;
            height: 35px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .step-content {
            flex-grow: 1;
        }

        .step-content strong {
            display: block;
            margin-bottom: 5px;
            color: var(--text-dark);
        }
    </style>
@endsection