@extends('layouts.auth')

@section('title', 'Inscription - InfiniSchool')
@section('form-title', 'Créer un Compte')
@section('form-description', 'Rejoignez des milliers d\'apprenants')

@section('brand-title', 'Commencez Votre Parcours')
@section('brand-description', 'Inscrivez-vous gratuitement et accédez immédiatement à nos formations. Sans engagement, sans carte bancaire.')

@section('content')

<!-- Social Register -->
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

<!-- Register Form -->
<form method="POST" action="{{ route('register') }}" class="needs-validation" novalidate>
    @csrf

    <!-- Full Name -->
    <div class="form-group">
        <label for="name" class="form-label">Nom Complet</label>
        <div class="input-group">
            <i class="fas fa-user input-icon"></i>
            <input type="text" 
                   class="form-control with-icon @error('name') is-invalid @enderror" 
                   id="name" 
                   name="name" 
                   value="{{ old('name') }}" 
                   placeholder="Prénom Nom"
                   required 
                   autofocus>
        </div>
        @error('name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

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
                   required>
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
                   placeholder="Minimum 8 caractères"
                   required 
                   minlength="8">
            <span class="password-toggle" onclick="togglePassword('password')">
                <i class="fas fa-eye"></i>
            </span>
        </div>
        <small class="form-text text-muted">
            Au moins 8 caractères avec lettres et chiffres
        </small>
        @error('password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <!-- Password Confirmation -->
    <div class="form-group">
        <label for="password_confirmation" class="form-label">Confirmer le Mot de Passe</label>
        <div class="input-group">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" 
                   class="form-control with-icon" 
                   id="password_confirmation" 
                   name="password_confirmation" 
                   placeholder="Confirmez votre mot de passe"
                   required 
                   minlength="8">
            <span class="password-toggle" onclick="togglePassword('password_confirmation')">
                <i class="fas fa-eye"></i>
            </span>
        </div>
    </div>

    <!-- Role Selection -->
    <div class="form-group">
        <label class="form-label">Je m'inscris en tant que</label>
        <div class="role-selection">
            <div class="role-option">
                <input type="radio" class="form-check-input" id="role_student" name="role" value="student" 
                       {{ old('role', 'student') == 'student' ? 'checked' : '' }} required>
                <label for="role_student" class="role-label">
                    <i class="fas fa-user-graduate"></i>
                    <span>Étudiant</span>
                    <small>Accédez aux formations</small>
                </label>
            </div>
            <div class="role-option">
                <input type="radio" class="form-check-input" id="role_teacher" name="role" value="teacher" 
                       {{ old('role') == 'teacher' ? 'checked' : '' }}>
                <label for="role_teacher" class="role-label">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Enseignant</span>
                    <small>Partagez votre expertise</small>
                </label>
            </div>
        </div>
        @error('role')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <!-- Terms Acceptance -->
    <div class="form-group">
        <div class="form-check">
            <input class="form-check-input @error('terms') is-invalid @enderror" 
                   type="checkbox" 
                   id="terms" 
                   name="terms" 
                   required>
            <label class="form-check-label" for="terms">
                J'accepte les 
                <a href="{{ route('terms') }}" target="_blank" class="auth-link">Conditions Générales</a> 
                et la 
                <a href="{{ route('privacy') }}" target="_blank" class="auth-link">Politique de Confidentialité</a>
            </label>
        </div>
        @error('terms')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <!-- Newsletter -->
    <div class="form-group">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter" 
                   {{ old('newsletter') ? 'checked' : '' }}>
            <label class="form-check-label" for="newsletter">
                Je souhaite recevoir les actualités et offres d'InfiniSchool
            </label>
        </div>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-user-plus me-2"></i>Créer Mon Compte
    </button>

    <!-- Login Link -->
    <div class="text-center mt-4">
        <p class="mb-0" style="color: #6c757d;">
            Vous avez déjà un compte ? 
            <a href="{{ route('login') }}" class="auth-link">
                Se connecter
            </a>
        </p>
    </div>
</form>

@endsection

@section('styles')
<style>
    /* Role Selection */
    .role-selection {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .role-option {
        position: relative;
    }

    .role-option input[type="radio"] {
        position: absolute;
        opacity: 0;
    }

    .role-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 20px 15px;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }

    .role-label:hover {
        border-color: var(--primary-color);
        background: rgba(128, 0, 32, 0.02);
    }

    .role-option input[type="radio"]:checked + .role-label {
        border-color: var(--primary-color);
        background: rgba(128, 0, 32, 0.05);
    }

    .role-label i {
        font-size: 2rem;
        color: var(--primary-color);
        margin-bottom: 10px;
    }

    .role-label span {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 5px;
        font-size: 1rem;
    }

    .role-label small {
        color: var(--text-light);
        font-size: 0.85rem;
    }

    @media (max-width: 576px) {
        .role-selection {
            grid-template-columns: 1fr;
        }
    }
</style>
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

    // Password strength indicator
    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const hasLetter = /[a-zA-Z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        const hasMinLength = password.length >= 8;
        
        // You can add visual feedback here
        if (hasLetter && hasNumber && hasMinLength) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
        }
    });

    // Password confirmation validation
    document.getElementById('password_confirmation').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmation = this.value;
        
        if (password === confirmation && confirmation.length > 0) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            if (confirmation.length > 0) {
                this.classList.add('is-invalid');
            }
        }
    });
</script>
@endsection