@extends('layouts.app')

@section('title', 'Blog - InfiniSchool')
@section('description', 'Découvrez nos articles et actualités sur la formation en ligne, les tendances du e-learning et nos conseils pour réussir.')

@section('content')

<!-- Page Header -->
<section class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-header-title">Notre Blog</h1>
                <p class="page-header-subtitle">
                    Actualités, conseils et ressources pour votre apprentissage
                </p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Blog</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Blog Section -->
<section class="blog-section py-5">
    <div class="container">
        @if($posts->count() > 0)
            <div class="row g-4">
                @foreach($posts as $post)
                <div class="col-md-6 col-lg-4">
                    <article class="blog-card">
                        <div class="blog-image">
                            <img src="{{ $post->image ?? asset('images/blog-placeholder.jpg') }}" alt="{{ $post->title }}">
                            <span class="blog-category">{{ $post->category ?? 'Actualité' }}</span>
                        </div>
                        <div class="blog-content">
                            <div class="blog-meta">
                                <span><i class="fas fa-calendar me-1"></i>{{ $post->created_at->format('d M Y') }}</span>
                                <span><i class="fas fa-user me-1"></i>{{ $post->author->name ?? 'Admin' }}</span>
                            </div>
                            <h3 class="blog-title">
                                <a href="#">{{ $post->title }}</a>
                            </h3>
                            <p class="blog-excerpt">{{ Str::limit($post->excerpt, 120) }}</p>
                            <a href="#" class="btn-read-more">
                                Lire la suite <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </article>
                </div>
                @endforeach
            </div>
        @else
            <!-- Coming Soon State -->
            <div class="coming-soon-wrapper">
                <div class="coming-soon-content text-center">
                    <div class="coming-soon-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <h2 class="coming-soon-title">Blog en construction</h2>
                    <p class="coming-soon-description">
                        Nous travaillons actuellement sur notre blog pour vous proposer des articles 
                        de qualité sur la formation en ligne, les tendances du e-learning et des 
                        conseils pratiques pour votre réussite.
                    </p>
                    <div class="coming-soon-features">
                        <div class="feature-item">
                            <i class="fas fa-lightbulb"></i>
                            <span>Conseils d'apprentissage</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-chart-line"></i>
                            <span>Tendances du marché</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-user-graduate"></i>
                            <span>Témoignages étudiants</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-briefcase"></i>
                            <span>Carrière & emploi</span>
                        </div>
                    </div>
                    <div class="newsletter-box">
                        <h4>Soyez informé du lancement</h4>
                        <p>Inscrivez-vous pour recevoir nos premiers articles</p>
                        <form class="newsletter-form">
                            <div class="input-group">
                                <input type="email" class="form-control" placeholder="Votre adresse email">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-bell me-2"></i>M'alerter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
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

    /* Blog Cards */
    .blog-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        height: 100%;
    }

    .blog-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .blog-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }

    .blog-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .blog-card:hover .blog-image img {
        transform: scale(1.1);
    }

    .blog-category {
        position: absolute;
        top: 15px;
        left: 15px;
        background: var(--primary-color);
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .blog-content {
        padding: 25px;
    }

    .blog-meta {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
        font-size: 0.85rem;
        color: var(--text-light);
    }

    .blog-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .blog-title a {
        color: var(--text-dark);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .blog-title a:hover {
        color: var(--primary-color);
    }

    .blog-excerpt {
        color: var(--text-light);
        line-height: 1.7;
        margin-bottom: 15px;
    }

    .btn-read-more {
        color: var(--primary-color);
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-read-more:hover {
        color: var(--primary-dark);
    }

    /* Coming Soon */
    .coming-soon-wrapper {
        max-width: 700px;
        margin: 0 auto;
    }

    .coming-soon-content {
        background: white;
        padding: 60px 40px;
        border-radius: 20px;
        box-shadow: 0 5px 30px rgba(0, 0, 0, 0.08);
    }

    .coming-soon-icon {
        width: 100px;
        height: 100px;
        background: rgba(128, 0, 32, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
    }

    .coming-soon-icon i {
        font-size: 3rem;
        color: var(--primary-color);
    }

    .coming-soon-title {
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 20px;
    }

    .coming-soon-description {
        color: var(--text-light);
        line-height: 1.8;
        margin-bottom: 30px;
    }

    .coming-soon-features {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 40px;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--text-dark);
        font-weight: 500;
    }

    .feature-item i {
        width: 40px;
        height: 40px;
        background: rgba(128, 0, 32, 0.1);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
    }

    .newsletter-box {
        background: #f8f9fa;
        padding: 30px;
        border-radius: 15px;
    }

    .newsletter-box h4 {
        font-weight: 700;
        margin-bottom: 10px;
        color: var(--text-dark);
    }

    .newsletter-box p {
        color: var(--text-light);
        margin-bottom: 20px;
    }

    .newsletter-form .input-group {
        max-width: 450px;
        margin: 0 auto;
    }

    .newsletter-form .form-control {
        padding: 12px 20px;
        border: 2px solid #e9ecef;
        border-radius: 10px 0 0 10px;
    }

    .newsletter-form .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: none;
    }

    .newsletter-form .btn {
        border-radius: 0 10px 10px 0;
        padding: 12px 25px;
    }

    @media (max-width: 768px) {
        .page-header-title {
            font-size: 2rem;
        }

        .coming-soon-content {
            padding: 40px 25px;
        }

        .coming-soon-features {
            grid-template-columns: 1fr;
        }

        .newsletter-form .input-group {
            flex-direction: column;
        }

        .newsletter-form .form-control,
        .newsletter-form .btn {
            border-radius: 10px;
            width: 100%;
        }

        .newsletter-form .btn {
            margin-top: 10px;
        }
    }
</style>
@endsection
