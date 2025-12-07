@extends('layouts.app')

@section('title', 'FAQ - InfiniSchool')
@section('description', 'Questions fréquemment posées sur InfiniSchool. Trouvez toutes les réponses à vos questions sur nos formations, inscriptions et certificats.')

@section('content')

<!-- Page Header -->
<section class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-header-title">Foire Aux Questions</h1>
                <p class="page-header-subtitle">
                    Trouvez rapidement les réponses à vos questions
                </p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">FAQ</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section py-5">
    <div class="container">
        <div class="row">
            <!-- FAQ Navigation -->
            <div class="col-lg-3 mb-4 mb-lg-0">
                <div class="faq-nav sticky-top" style="top: 100px;">
                    <h5 class="faq-nav-title mb-3">Catégories</h5>
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="#general">
                            <i class="fas fa-info-circle me-2"></i>Général
                        </a>
                        <a class="nav-link" href="#inscription">
                            <i class="fas fa-user-plus me-2"></i>Inscription
                        </a>
                        <a class="nav-link" href="#certificats">
                            <i class="fas fa-certificate me-2"></i>Certificats
                        </a>
                        <a class="nav-link" href="#technique">
                            <i class="fas fa-cog me-2"></i>Technique
                        </a>
                    </nav>
                </div>
            </div>

            <!-- FAQ Content -->
            <div class="col-lg-9">
                @foreach($faqs as $category => $questions)
                <div class="faq-category mb-5" id="{{ $category }}">
                    <h3 class="faq-category-title mb-4">
                        @switch($category)
                            @case('general')
                                <i class="fas fa-info-circle me-2 text-primary"></i>Questions Générales
                                @break
                            @case('inscription')
                                <i class="fas fa-user-plus me-2 text-primary"></i>Inscription & Paiement
                                @break
                            @case('certificats')
                                <i class="fas fa-certificate me-2 text-primary"></i>Certificats
                                @break
                            @case('technique')
                                <i class="fas fa-cog me-2 text-primary"></i>Support Technique
                                @break
                        @endswitch
                    </h3>

                    <div class="accordion" id="accordion{{ ucfirst($category) }}">
                        @foreach($questions as $index => $faq)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $category }}{{ $index }}">
                                <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse{{ $category }}{{ $index }}" 
                                        aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" 
                                        aria-controls="collapse{{ $category }}{{ $index }}">
                                    {{ $faq['question'] }}
                                </button>
                            </h2>
                            <div id="collapse{{ $category }}{{ $index }}" 
                                 class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" 
                                 aria-labelledby="heading{{ $category }}{{ $index }}" 
                                 data-bs-parent="#accordion{{ ucfirst($category) }}">
                                <div class="accordion-body">
                                    {{ $faq['answer'] }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                <!-- Still Have Questions -->
                <div class="still-questions-box text-center p-5 bg-light rounded-4">
                    <h4 class="mb-3">Vous n'avez pas trouvé votre réponse ?</h4>
                    <p class="text-muted mb-4">
                        Notre équipe de support est là pour vous aider
                    </p>
                    <a href="{{ route('contact') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-envelope me-2"></i>Contactez-nous
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('styles')
<style>
    :root {
        --primary-color: #800020;
        --primary-dark: #5c0017;
        --text-dark: #1a1a2e;
        --text-light: #6c757d;
    }

    .page-header-section {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        padding: 80px 0;
        margin-bottom: 0;
    }

    .page-header-title {
        font-size: 3rem;
        font-weight: 800;
        color: white;
        margin-bottom: 15px;
    }

    .page-header-subtitle {
        font-size: 1.2rem;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 20px;
    }

    .breadcrumb {
        background: transparent;
        margin: 0;
    }

    .breadcrumb-item a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
    }

    .breadcrumb-item.active {
        color: white;
    }

    .faq-nav {
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    }

    .faq-nav-title {
        font-weight: 700;
        color: var(--text-dark);
    }

    .faq-nav .nav-link {
        color: var(--text-light);
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 5px;
        transition: all 0.3s ease;
    }

    .faq-nav .nav-link:hover,
    .faq-nav .nav-link.active {
        background: rgba(128, 0, 32, 0.1);
        color: var(--primary-color);
    }

    .faq-category-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .accordion-item {
        border: none;
        background: white;
        border-radius: 12px;
        margin-bottom: 15px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .accordion-button {
        background: white;
        color: var(--text-dark);
        font-weight: 600;
        font-size: 1.05rem;
        padding: 20px 25px;
        border: none;
        box-shadow: none;
    }

    .accordion-button:not(.collapsed) {
        background: var(--primary-color);
        color: white;
    }

    .accordion-button:focus {
        box-shadow: none;
        border: none;
    }

    .accordion-body {
        padding: 25px;
        color: var(--text-light);
        line-height: 1.8;
    }

    .still-questions-box {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    @media (max-width: 992px) {
        .page-header-title {
            font-size: 2.5rem;
        }

        .faq-nav {
            position: static !important;
        }
    }
</style>
@endsection
