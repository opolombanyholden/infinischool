@extends('layouts.auth')

@section('title', 'Mot de passe oublié - InfiniSchool')
@section('form-title', 'Mot de Passe Oublié ?')
@section('form-description', 'Réinitialisez votre mot de passe en toute sécurité')

@section('brand-title', 'Pas de Panique !')
@section('brand-description', 'Nous allons vous aider à récupérer l\'accès à votre compte. Entrez votre email et suivez les instructions que nous vous enverrons.')

@section('content')

<!-- Info Alert -->
<div class="alert alert-info" role="alert">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Information :</strong> Entrez l'adresse email associée à votre compte. 
    Nous vous enverrons un lien sécurisé pour réinitialiser votre mot de passe.
</div>

<!-- Forgot Password Form -->
<form method="POST" action="{{ route('password.email') }}" class="needs-validation" novalidate>
    @csrf

    <!-- Email -->
    <div class="form-group">
        <label for="email" class="form-label">Adresse Email</label>
        <div class="input-group">
            <i class="fas fa-envelope input-icon"></i>
            <input type="email" 
                   class="form-control with-icon @error('email') is-invalid @enderror" 
                   id="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   placeholder="votre@email.com"
                   required 
                   autofocus>
        </div>
        @error('email')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-paper-plane me-2"></i>Envoyer le Lien de Réinitialisation
    </button>

    <!-- Back to Login -->
    <div class="text-center mt-4">
        <a href="{{ route('login') }}" class="auth-link">
            <i class="fas fa-arrow-left me-2"></i>Retour à la connexion
        </a>
    </div>
</form>

<!-- Help Section -->
<div class="help-section mt-4 p-3" style="background: #f8f9fa; border-radius: 10px;">
    <h6 class="mb-2" style="color: #212529; font-weight: 600;">
        <i class="fas fa-question-circle me-2" style="color: var(--primary-color);"></i>
        Besoin d'aide ?
    </h6>
    <p class="mb-2" style="color: #6c757d; font-size: 0.9rem; line-height: 1.6;">
        Si vous ne recevez pas l'email dans quelques minutes :
    </p>
    <ul style="color: #6c757d; font-size: 0.9rem; line-height: 1.6; margin-bottom: 0;">
        <li>Vérifiez votre dossier spam/courrier indésirable</li>
        <li>Assurez-vous d'avoir entré la bonne adresse email</li>
        <li>Contactez notre <a href="{{ route('contact') }}" class="auth-link">support</a> si le problème persiste</li>
    </ul>
</div>

@endsection

@section('styles')
<style>
    .help-section ul {
        padding-left: 20px;
    }

    .help-section li {
        margin-bottom: 8px;
    }

    .alert {
        border-radius: 10px;
        border: none;
        margin-bottom: 25px;
    }
</style>
@endsection