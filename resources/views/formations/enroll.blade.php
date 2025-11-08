@extends('layouts.app')

@section('title', 'Inscription - ' . $formation->name . ' - InfiniSchool')
@section('description', 'Inscrivez-vous à la formation ' . $formation->name . ' et commencez votre apprentissage dès maintenant.')

@section('content')

<!-- Page Header -->
<section class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-header-title">Inscription à la Formation</h1>
                <p class="page-header-subtitle">{{ $formation->name }}</p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('formations.index') }}">Formations</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('formations.show', $formation->slug) }}">{{ Str::limit($formation->name, 30) }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Inscription</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Enrollment Section -->
<section class="enrollment-section py-5">
    <div class="container">
        <!-- Progress Steps -->
        <div class="enrollment-steps mb-5">
            <div class="step-item active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-label">Vérification</div>
            </div>
            <div class="step-line"></div>
            <div class="step-item" data-step="2">
                <div class="step-number">2</div>
                <div class="step-label">Informations</div>
            </div>
            <div class="step-line"></div>
            <div class="step-item" data-step="3">
                <div class="step-number">3</div>
                <div class="step-label">Paiement</div>
            </div>
            <div class="step-line"></div>
            <div class="step-item" data-step="4">
                <div class="step-number">4</div>
                <div class="step-label">Confirmation</div>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div class="enrollment-content">
                    
                    <!-- Step 1: Verification -->
                    <div class="step-content active" id="step1">
                        <h3 class="step-title">
                            <i class="fas fa-check-circle text-primary me-2"></i>
                            Vérification des Prérequis
                        </h3>
                        
                        @if($formation->prerequisites && count(json_decode($formation->prerequisites, true)) > 0)
                            <div class="alert alert-warning">
                                <h6 class="alert-heading">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Cette formation nécessite des prérequis
                                </h6>
                                <ul class="mb-0">
                                    @foreach(json_decode($formation->prerequisites, true) as $prerequisite)
                                        <li>{{ $prerequisite }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                Aucun prérequis nécessaire pour cette formation.
                            </div>
                        @endif

                        <!-- Formation Details -->
                        <div class="formation-details-box">
                            <h5 class="mb-3">Détails de la Formation</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="detail-item">
                                        <i class="fas fa-clock text-primary me-2"></i>
                                        <strong>Durée :</strong> {{ $formation->duration_weeks }} semaines
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-item">
                                        <i class="fas fa-signal text-primary me-2"></i>
                                        <strong>Niveau :</strong> {{ ucfirst($formation->level) }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-item">
                                        <i class="fas fa-tag text-primary me-2"></i>
                                        <strong>Catégorie :</strong> {{ $formation->category }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-item">
                                        <i class="fas fa-users text-primary me-2"></i>
                                        <strong>Places :</strong> {{ $formation->max_students_per_class }} étudiants max
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Confirmation Checkbox -->
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="prerequisitesCheck" required>
                            <label class="form-check-label" for="prerequisitesCheck">
                                Je confirme avoir pris connaissance des prérequis et des détails de la formation.
                            </label>
                        </div>

                        <div class="step-actions mt-4">
                            <button type="button" class="btn btn-primary" onclick="nextStep(2)" id="step1Btn" disabled>
                                Continuer <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Information -->
                    <div class="step-content" id="step2">
                        <h3 class="step-title">
                            <i class="fas fa-user text-primary me-2"></i>
                            Vos Informations
                        </h3>

                        <form id="enrollmentForm">
                            @csrf
                            <input type="hidden" name="formation_id" value="{{ $formation->id }}">

                            <!-- Personal Information -->
                            <h5 class="form-section-title">Informations Personnelles</h5>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="{{ Auth::check() ? explode(' ', Auth::user()->name)[0] : '' }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="{{ Auth::check() && count(explode(' ', Auth::user()->name)) > 1 ? explode(' ', Auth::user()->name)[1] : '' }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="{{ Auth::check() ? Auth::user()->email : '' }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           placeholder="+241 XX XX XX XX" required>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <h5 class="form-section-title mt-4">Informations Complémentaires</h5>
                            
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="motivation" class="form-label">Pourquoi souhaitez-vous suivre cette formation ? <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="motivation" name="motivation" rows="4" 
                                              placeholder="Parlez-nous de vos objectifs et motivations..." required></textarea>
                                    <small class="form-text text-muted">Minimum 50 caractères</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="experience_level" class="form-label">Niveau d'expérience <span class="text-danger">*</span></label>
                                    <select class="form-control" id="experience_level" name="experience_level" required>
                                        <option value="">Sélectionnez...</option>
                                        <option value="debutant">Débutant</option>
                                        <option value="intermediaire">Intermédiaire</option>
                                        <option value="avance">Avancé</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="professional_status" class="form-label">Statut professionnel</label>
                                    <select class="form-control" id="professional_status" name="professional_status">
                                        <option value="">Sélectionnez...</option>
                                        <option value="etudiant">Étudiant</option>
                                        <option value="employe">Employé</option>
                                        <option value="independant">Indépendant</option>
                                        <option value="chercheur_emploi">Chercheur d'emploi</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>
                            </div>

                            <div class="step-actions mt-4">
                                <button type="button" class="btn btn-outline-secondary" onclick="prevStep(1)">
                                    <i class="fas fa-arrow-left me-2"></i>Retour
                                </button>
                                <button type="button" class="btn btn-primary" onclick="nextStep(3)">
                                    Continuer <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Step 3: Payment -->
                    <div class="step-content" id="step3">
                        <h3 class="step-title">
                            <i class="fas fa-credit-card text-primary me-2"></i>
                            Paiement
                        </h3>

                        <!-- Payment Method Selection -->
                        <div class="payment-methods">
                            <h5 class="mb-3">Choisissez votre mode de paiement</h5>
                            
                            <div class="payment-method-item">
                                <input type="radio" name="payment_method" id="payment_card" value="card" checked>
                                <label for="payment_card">
                                    <i class="fas fa-credit-card"></i>
                                    <div>
                                        <strong>Carte Bancaire</strong>
                                        <small>Paiement sécurisé par Stripe</small>
                                    </div>
                                </label>
                            </div>

                            <div class="payment-method-item">
                                <input type="radio" name="payment_method" id="payment_mobile" value="mobile">
                                <label for="payment_mobile">
                                    <i class="fas fa-mobile-alt"></i>
                                    <div>
                                        <strong>Mobile Money</strong>
                                        <small>Airtel Money, MTN Mobile Money</small>
                                    </div>
                                </label>
                            </div>

                            <div class="payment-method-item">
                                <input type="radio" name="payment_method" id="payment_transfer" value="transfer">
                                <label for="payment_transfer">
                                    <i class="fas fa-university"></i>
                                    <div>
                                        <strong>Virement Bancaire</strong>
                                        <small>Délai de traitement 2-3 jours</small>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Payment Details (Card) -->
                        <div id="cardPaymentDetails" class="payment-details mt-4">
                            <h5 class="mb-3">Informations de Paiement</h5>
                            <div class="alert alert-info">
                                <i class="fas fa-lock me-2"></i>
                                Vos informations de paiement sont sécurisées et cryptées.
                            </div>
                            <div id="card-element" class="form-control" style="height: 45px; padding: 12px;">
                                <!-- Stripe Card Element will be inserted here -->
                            </div>
                            <small class="form-text text-muted">
                                Paiement sécurisé par Stripe. Vos données bancaires ne sont jamais stockées.
                            </small>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="termsCheck" required>
                            <label class="form-check-label" for="termsCheck">
                                J'accepte les <a href="{{ route('terms') }}" target="_blank">Conditions Générales de Vente</a> 
                                et la <a href="{{ route('privacy') }}" target="_blank">Politique de Confidentialité</a>
                            </label>
                        </div>

                        <div class="step-actions mt-4">
                            <button type="button" class="btn btn-outline-secondary" onclick="prevStep(2)">
                                <i class="fas fa-arrow-left me-2"></i>Retour
                            </button>
                            <button type="button" class="btn btn-success" onclick="processPayment()" id="paymentBtn" disabled>
                                <i class="fas fa-lock me-2"></i>Payer {{ number_format($formation->discount_price ?? $formation->price, 0, ',', ' ') }} FCFA
                            </button>
                        </div>
                    </div>

                    <!-- Step 4: Confirmation -->
                    <div class="step-content" id="step4">
                        <div class="text-center py-5">
                            <div class="success-icon mb-4">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h3 class="mb-3">Inscription Confirmée !</h3>
                            <p class="lead mb-4">
                                Félicitations ! Vous êtes maintenant inscrit à la formation <strong>{{ $formation->name }}</strong>
                            </p>
                            
                            <div class="confirmation-details">
                                <p><i class="fas fa-envelope text-primary me-2"></i>Un email de confirmation a été envoyé à votre adresse.</p>
                                <p><i class="fas fa-calendar text-primary me-2"></i>Vous recevrez les informations de début de formation sous 24h.</p>
                                <p><i class="fas fa-graduation-cap text-primary me-2"></i>Accédez dès maintenant à votre espace étudiant.</p>
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('student.dashboard') }}" class="btn btn-primary btn-lg me-2">
                                    <i class="fas fa-tachometer-alt me-2"></i>Mon Tableau de Bord
                                </a>
                                <a href="{{ route('formations.index') }}" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-book me-2"></i>Autres Formations
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="col-lg-4">
                <div class="order-summary sticky-top" style="top: 90px;">
                    <h5 class="summary-title">Récapitulatif</h5>
                    
                    <!-- Formation Image -->
                    @if($formation->image)
                        <div class="summary-image mb-3">
                            <img src="{{ Storage::url($formation->image) }}" alt="{{ $formation->name }}" class="img-fluid rounded">
                        </div>
                    @endif

                    <!-- Formation Info -->
                    <h6 class="formation-name">{{ $formation->name }}</h6>
                    <p class="formation-category text-muted">
                        <i class="fas fa-tag me-1"></i>{{ $formation->category }}
                    </p>

                    <!-- Price Details -->
                    <div class="price-details">
                        <div class="price-row">
                            <span>Prix de base</span>
                            <span>{{ number_format($formation->price, 0, ',', ' ') }} FCFA</span>
                        </div>
                        @if($formation->discount_price)
                            <div class="price-row discount">
                                <span>Réduction</span>
                                <span>-{{ number_format($formation->price - $formation->discount_price, 0, ',', ' ') }} FCFA</span>
                            </div>
                        @endif
                        <hr>
                        <div class="price-row total">
                            <span>Total à payer</span>
                            <span>{{ number_format($formation->discount_price ?? $formation->price, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>

                    <!-- What's Included -->
                    <div class="whats-included mt-4">
                        <h6 class="mb-3">Cette formation inclut :</h6>
                        <ul class="included-list">
                            <li><i class="fas fa-check text-success me-2"></i>Accès à vie au contenu</li>
                            <li><i class="fas fa-check text-success me-2"></i>Cours en direct avec formateurs</li>
                            <li><i class="fas fa-check text-success me-2"></i>Certificat de réussite</li>
                            <li><i class="fas fa-check text-success me-2"></i>Communauté d'étudiants</li>
                            <li><i class="fas fa-check text-success me-2"></i>Support 24/7</li>
                        </ul>
                    </div>

                    <!-- Guarantee -->
                    <div class="guarantee mt-4 p-3 text-center" style="background: #f8f9fa; border-radius: 10px;">
                        <i class="fas fa-shield-alt text-success" style="font-size: 2rem; margin-bottom: 10px;"></i>
                        <p class="mb-0" style="font-size: 0.9rem; line-height: 1.6;">
                            <strong>Garantie satisfait ou remboursé</strong><br>
                            30 jours pour changer d'avis
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('styles')
<style>
    /* Page Header */
    .page-header-section {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        padding: 60px 0 50px;
    }

    .page-header-title {
        color: white;
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .page-header-subtitle {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1.1rem;
        margin-bottom: 20px;
    }

    .page-header-section .breadcrumb {
        background: transparent;
    }

    .page-header-section .breadcrumb-item,
    .page-header-section .breadcrumb-item a {
        color: rgba(255, 255, 255, 0.8);
    }

    .page-header-section .breadcrumb-item.active {
        color: white;
    }

    /* Progress Steps */
    .enrollment-steps {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 800px;
        margin: 0 auto;
        position: relative;
    }

    .step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 2;
    }

    .step-number {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    .step-item.active .step-number,
    .step-item.completed .step-number {
        background: var(--primary-color);
        color: white;
    }

    .step-label {
        font-size: 0.9rem;
        font-weight: 600;
        color: #6c757d;
    }

    .step-item.active .step-label {
        color: var(--primary-color);
    }

    .step-line {
        flex: 1;
        height: 2px;
        background: #e9ecef;
        margin: 0 -10px;
        margin-bottom: 40px;
    }

    /* Enrollment Content */
    .enrollment-content {
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    }

    .step-content {
        display: none;
    }

    .step-content.active {
        display: block;
        animation: fadeIn 0.5s;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .step-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 25px;
        color: var(--text-dark);
    }

    .formation-details-box {
        background: #f8f9fa;
        padding: 25px;
        border-radius: 12px;
        margin-top: 20px;
    }

    .detail-item {
        padding: 10px 0;
    }

    /* Form Sections */
    .form-section-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 20px;
        color: var(--text-dark);
        padding-bottom: 10px;
        border-bottom: 2px solid var(--primary-color);
    }

    /* Payment Methods */
    .payment-methods {
        margin-top: 20px;
    }

    .payment-method-item {
        position: relative;
        margin-bottom: 15px;
    }

    .payment-method-item input[type="radio"] {
        position: absolute;
        opacity: 0;
    }

    .payment-method-item label {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 20px;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .payment-method-item label:hover {
        border-color: var(--primary-color);
        background: rgba(128, 0, 32, 0.02);
    }

    .payment-method-item input[type="radio"]:checked + label {
        border-color: var(--primary-color);
        background: rgba(128, 0, 32, 0.05);
    }

    .payment-method-item label i {
        font-size: 1.8rem;
        color: var(--primary-color);
    }

    /* Success Icon */
    .success-icon i {
        font-size: 5rem;
        color: #28a745;
    }

    /* Order Summary */
    .order-summary {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    }

    .summary-title {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 20px;
        color: var(--text-dark);
    }

    .formation-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-dark);
    }

    .price-details {
        margin-top: 20px;
    }

    .price-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        font-size: 1rem;
    }

    .price-row.discount {
        color: #28a745;
        font-weight: 600;
    }

    .price-row.total {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--primary-color);
    }

    .included-list {
        list-style: none;
        padding: 0;
    }

    .included-list li {
        padding: 8px 0;
        font-size: 0.95rem;
    }

    /* Step Actions */
    .step-actions {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .enrollment-steps {
            flex-wrap: wrap;
        }

        .step-line {
            display: none;
        }

        .enrollment-content {
            padding: 30px 20px;
        }
    }

    @media (max-width: 576px) {
        .page-header-title {
            font-size: 2rem;
        }

        .step-number {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .step-actions {
            flex-direction: column;
        }

        .step-actions button {
            width: 100%;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    let currentStep = 1;

    // Prerequisites check
    document.getElementById('prerequisitesCheck').addEventListener('change', function() {
        document.getElementById('step1Btn').disabled = !this.checked;
    });

    // Terms check
    document.getElementById('termsCheck').addEventListener('change', function() {
        document.getElementById('paymentBtn').disabled = !this.checked;
    });

    function nextStep(step) {
        // Hide current step
        document.getElementById('step' + currentStep).classList.remove('active');
        document.querySelector('.step-item[data-step="' + currentStep + '"]').classList.remove('active');
        document.querySelector('.step-item[data-step="' + currentStep + '"]').classList.add('completed');

        // Show next step
        currentStep = step;
        document.getElementById('step' + currentStep).classList.add('active');
        document.querySelector('.step-item[data-step="' + currentStep + '"]').classList.add('active');

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function prevStep(step) {
        // Hide current step
        document.getElementById('step' + currentStep).classList.remove('active');
        document.querySelector('.step-item[data-step="' + currentStep + '"]').classList.remove('active');

        // Show previous step
        currentStep = step;
        document.getElementById('step' + currentStep).classList.add('active');
        document.querySelector('.step-item[data-step="' + currentStep + '"]').classList.add('active');

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function processPayment() {
        // Simulate payment processing
        const paymentBtn = document.getElementById('paymentBtn');
        paymentBtn.disabled = true;
        paymentBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Traitement en cours...';

        // Simulate API call
        setTimeout(() => {
            nextStep(4);
        }, 2000);
    }

    // Form validation for step 2
    document.getElementById('enrollmentForm').addEventListener('submit', function(e) {
        e.preventDefault();
    });
</script>
@endsection