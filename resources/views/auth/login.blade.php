@extends('layouts.auth')

@section('title', 'Connexion - InfiniSchool')
@section('form-title', 'Connexion')
@section('form-description', 'Accédez à votre espace personnel')

@section('brand-title', 'Bienvenue sur InfiniSchool')
@section('brand-description', 'Connectez-vous pour accéder à vos cours, suivre votre progression et interagir avec vos formateurs.')

@section('content')

<!-- Social Login -->
<div class="social-login">
    <a href="{{ route('auth.google') }}" class="btn-social btn-google">
        <i class="fab fa-google"></i>
        <span>Google</span>
    </a>
    <a href="{{ route('auth.linkedin') }}" class="btn-social btn-linkedin">
        <i class="fab fa-linkedin"></i>
        <span>LinkedIn</span>
    </a>
</div>

<!-- Divider -->
<div class="auth-divider">
    <span>Ou avec votre email</span>
</div>

<!-- Login Form -->
<form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
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

    <!-- Password -->
    <div class="form-group">
        <label for="password" class="form-label">Mot de Passe</label>
        <div class="input-group">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" 
                   class="form-control with-icon @error('password') is-invalid @enderror" 
                   id="password" 
                   name="password" 
                   placeholder="••••••••"
                   required>
            <span class="password-toggle" onclick="togglePassword('password')">
                <i class="fas fa-eye"></i>
            </span>
        </div>
        @error('password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <!-- Remember Me & Forgot Password -->
    <div class="form-group d-flex justify-content-between align-items-center">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label" for="remember">
                Se souvenir de moi
            </label>
        </div>
        <a href="{{ route('password.request') }}" class="auth-link">
            Mot de passe oublié ?
        </a>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-sign-in-alt me-2"></i>Se Connecter
    </button>

    <!-- Register Link -->
    <div class="text-center mt-4">
        <p class="mb-0" style="color: #6c757d;">
            Vous n'avez pas de compte ? 
            <a href="{{ route('register') }}" class="auth-link">
                Créer un compte gratuitement
            </a>
        </p>
    </div>
</form>

@endsection

@section('scripts')
<script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const toggle = field.nextElementSibling;
        const icon = toggle.querySelector('i');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection