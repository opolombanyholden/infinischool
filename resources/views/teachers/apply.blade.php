@extends('layouts.app')

@section('title', 'Devenir Enseignant - InfiniSchool')

@section('styles')
<style>
    .apply-hero {
        background: linear-gradient(135deg, #800020 0%, #5c0017 100%);
        color: white;
        padding: 80px 0;
    }
    
    .apply-form-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        padding: 40px;
        margin-top: -60px;
        position: relative;
        z-index: 10;
    }
    
    .form-section {
        margin-bottom: 30px;
        padding-bottom: 30px;
        border-bottom: 1px solid #eee;
    }
    
    .form-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .section-title {
        color: #800020;
        font-weight: 600;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .section-title i {
        font-size: 1.2rem;
    }
    
    .form-label {
        font-weight: 500;
        color: #333;
    }
    
    .form-control:focus,
    .form-select:focus {
        border-color: #800020;
        box-shadow: 0 0 0 0.2rem rgba(128, 0, 32, 0.15);
    }
    
    .btn-burgundy {
        background: linear-gradient(135deg, #800020, #a00028);
        border: none;
        color: white;
        padding: 12px 40px;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .btn-burgundy:hover {
        background: linear-gradient(135deg, #600018, #800020);
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(128, 0, 32, 0.3);
        color: white;
    }
    
    .benefits-list {
        list-style: none;
        padding: 0;
    }
    
    .benefits-list li {
        padding: 10px 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .benefits-list li i {
        color: #28a745;
        font-size: 1.2rem;
    }
    
    .file-upload-area {
        border: 2px dashed #ddd;
        border-radius: 10px;
        padding: 30px;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .file-upload-area:hover,
    .file-upload-area.dragover {
        border-color: #800020;
        background: rgba(128, 0, 32, 0.05);
    }
    
    .file-upload-area i {
        font-size: 2.5rem;
        color: #800020;
        margin-bottom: 10px;
    }
    
    .char-counter {
        font-size: 0.85rem;
        color: #666;
        text-align: right;
    }
    
    .char-counter.warning {
        color: #ffc107;
    }
    
    .char-counter.danger {
        color: #dc3545;
    }
</style>
@endsection

@section('content')
<!-- Hero Section -->
<section class="apply-hero">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Devenez Enseignant InfiniSchool</h1>
        <p class="lead mb-0">Partagez votre expertise et inspirez la prochaine génération de talents</p>
    </div>
</section>

<!-- Main Content -->
<section class="pb-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="apply-form-card">
                    
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Veuillez corriger les erreurs suivantes :</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    <form action="{{ route('teacher.apply.submit') }}" method="POST" enctype="multipart/form-data" id="applicationForm">
                        @csrf
                        
                        <!-- Section: Informations personnelles -->
                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="bi bi-person"></i>
                                Informations personnelles
                            </h4>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                           id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                           id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}" 
                                           placeholder="+241 XX XX XX XX" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Section: Expertise -->
                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="bi bi-mortarboard"></i>
                                Expertise et qualifications
                            </h4>
                            
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label for="specialization" class="form-label">Domaine d'expertise <span class="text-danger">*</span></label>
                                    <select class="form-select @error('specialization') is-invalid @enderror" 
                                            id="specialization" name="specialization" required>
                                        <option value="">Sélectionnez votre domaine</option>
                                        @foreach($specializations as $value => $label)
                                            <option value="{{ $value }}" {{ old('specialization') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('specialization')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="experience_years" class="form-label">Années d'expérience <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('experience_years') is-invalid @enderror" 
                                           id="experience_years" name="experience_years" min="0" max="50"
                                           value="{{ old('experience_years') }}" required>
                                    @error('experience_years')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-12">
                                    <label for="qualifications" class="form-label">Qualifications et diplômes <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('qualifications') is-invalid @enderror" 
                                              id="qualifications" name="qualifications" rows="3"
                                              placeholder="Listez vos diplômes, certifications et formations pertinentes..." required>{{ old('qualifications') }}</textarea>
                                    @error('qualifications')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-12">
                                    <label for="bio" class="form-label">Biographie professionnelle <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('bio') is-invalid @enderror" 
                                              id="bio" name="bio" rows="5" maxlength="2000"
                                              placeholder="Présentez-vous, votre parcours et votre approche pédagogique..." required>{{ old('bio') }}</textarea>
                                    <div class="char-counter" id="bioCounter">0 / 2000 caractères</div>
                                    @error('bio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Section: Documents -->
                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="bi bi-file-earmark-text"></i>
                                Documents
                            </h4>
                            
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="cv" class="form-label">CV / Curriculum Vitae <span class="text-danger">*</span></label>
                                    <div class="file-upload-area" id="cvDropArea">
                                        <i class="bi bi-cloud-upload"></i>
                                        <p class="mb-1">Glissez votre CV ici ou cliquez pour sélectionner</p>
                                        <small class="text-muted">PDF, DOC ou DOCX (max 5 Mo)</small>
                                        <input type="file" class="d-none @error('cv') is-invalid @enderror" 
                                               id="cv" name="cv" accept=".pdf,.doc,.docx" required>
                                    </div>
                                    <div id="cvFileName" class="mt-2 text-success d-none">
                                        <i class="bi bi-check-circle me-1"></i>
                                        <span></span>
                                    </div>
                                    @error('cv')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="linkedin" class="form-label">Profil LinkedIn</label>
                                    <input type="url" class="form-control @error('linkedin') is-invalid @enderror" 
                                           id="linkedin" name="linkedin" value="{{ old('linkedin') }}"
                                           placeholder="https://linkedin.com/in/votre-profil">
                                    @error('linkedin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="website" class="form-label">Site web / Portfolio</label>
                                    <input type="url" class="form-control @error('website') is-invalid @enderror" 
                                           id="website" name="website" value="{{ old('website') }}"
                                           placeholder="https://votre-site.com">
                                    @error('website')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Section: Motivation -->
                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="bi bi-heart"></i>
                                Motivation
                            </h4>
                            
                            <div class="mb-3">
                                <label for="motivation" class="form-label">Pourquoi souhaitez-vous rejoindre InfiniSchool ? <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('motivation') is-invalid @enderror" 
                                          id="motivation" name="motivation" rows="4" maxlength="1000"
                                          placeholder="Expliquez vos motivations et ce que vous pouvez apporter à nos étudiants..." required>{{ old('motivation') }}</textarea>
                                <div class="char-counter" id="motivationCounter">0 / 1000 caractères</div>
                                @error('motivation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Conditions -->
                        <div class="form-check mb-4">
                            <input class="form-check-input @error('terms_accepted') is-invalid @enderror" 
                                   type="checkbox" id="terms_accepted" name="terms_accepted" value="1" required>
                            <label class="form-check-label" for="terms_accepted">
                                J'accepte les <a href="{{ route('terms') }}" target="_blank" class="text-burgundy">conditions générales</a> 
                                et la <a href="{{ route('privacy') }}" target="_blank" class="text-burgundy">politique de confidentialité</a> 
                                d'InfiniSchool <span class="text-danger">*</span>
                            </label>
                            @error('terms_accepted')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Submit -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-burgundy btn-lg">
                                <i class="bi bi-send me-2"></i>
                                Soumettre ma candidature
                            </button>
                            <p class="text-muted small mt-3">
                                Nous examinerons votre candidature et vous contacterons dans les 5 jours ouvrables.
                            </p>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Sidebar Benefits -->
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-star text-warning me-2"></i>
                            Avantages enseignant
                        </h5>
                        
                        <ul class="benefits-list">
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Revenus attractifs (jusqu'à 70% des ventes)</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Flexibilité totale sur vos horaires</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Outils pédagogiques modernes (Zoom intégré)</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Support technique dédié</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Communauté d'enseignants active</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Formation continue gratuite</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Visibilité auprès de milliers d'étudiants</span>
                            </li>
                        </ul>
                        
                        <hr class="my-4">
                        
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-question-circle me-2"></i>
                            Questions fréquentes
                        </h6>
                        
                        <div class="accordion accordion-flush" id="faqAccordion">
                            <div class="accordion-item border-0">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed px-0 py-2" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#faq1">
                                        Combien de temps prend l'approbation ?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body px-0 text-muted small">
                                        Nous examinons chaque candidature sous 5 jours ouvrables.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-0">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed px-0 py-2" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#faq2">
                                        Puis-je enseigner plusieurs matières ?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body px-0 text-muted small">
                                        Oui, vous pouvez créer des formations dans plusieurs domaines de compétence.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counters
    const bioTextarea = document.getElementById('bio');
    const bioCounter = document.getElementById('bioCounter');
    const motivationTextarea = document.getElementById('motivation');
    const motivationCounter = document.getElementById('motivationCounter');
    
    function updateCounter(textarea, counter, max) {
        const length = textarea.value.length;
        counter.textContent = `${length} / ${max} caractères`;
        
        counter.classList.remove('warning', 'danger');
        if (length > max * 0.9) {
            counter.classList.add('danger');
        } else if (length > max * 0.7) {
            counter.classList.add('warning');
        }
    }
    
    bioTextarea.addEventListener('input', () => updateCounter(bioTextarea, bioCounter, 2000));
    motivationTextarea.addEventListener('input', () => updateCounter(motivationTextarea, motivationCounter, 1000));
    
    // Initialize counters
    updateCounter(bioTextarea, bioCounter, 2000);
    updateCounter(motivationTextarea, motivationCounter, 1000);
    
    // File upload drag & drop
    const cvDropArea = document.getElementById('cvDropArea');
    const cvInput = document.getElementById('cv');
    const cvFileName = document.getElementById('cvFileName');
    
    cvDropArea.addEventListener('click', () => cvInput.click());
    
    cvDropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        cvDropArea.classList.add('dragover');
    });
    
    cvDropArea.addEventListener('dragleave', () => {
        cvDropArea.classList.remove('dragover');
    });
    
    cvDropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        cvDropArea.classList.remove('dragover');
        
        if (e.dataTransfer.files.length) {
            cvInput.files = e.dataTransfer.files;
            showFileName(e.dataTransfer.files[0]);
        }
    });
    
    cvInput.addEventListener('change', (e) => {
        if (e.target.files.length) {
            showFileName(e.target.files[0]);
        }
    });
    
    function showFileName(file) {
        cvFileName.querySelector('span').textContent = file.name;
        cvFileName.classList.remove('d-none');
    }
});
</script>
@endsection
