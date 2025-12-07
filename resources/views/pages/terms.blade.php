@extends('layouts.app')

@section('title', 'Conditions d\'Utilisation - InfiniSchool')
@section('description', 'Conditions générales d\'utilisation de la plateforme InfiniSchool.')

@section('content')

<!-- Page Header -->
<section class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-header-title">Conditions d'Utilisation</h1>
                <p class="page-header-subtitle">
                    Dernière mise à jour : {{ date('d/m/Y') }}
                </p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Conditions d'utilisation</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Content Section -->
<section class="legal-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="legal-content">
                    
                    <div class="legal-block">
                        <h2>1. Objet</h2>
                        <p>
                            Les présentes conditions générales d'utilisation (CGU) ont pour objet de définir les modalités 
                            d'accès et d'utilisation des services proposés par la plateforme InfiniSchool.
                        </p>
                        <p>
                            En accédant à notre site et en utilisant nos services, vous acceptez sans réserve les présentes 
                            conditions d'utilisation.
                        </p>
                    </div>

                    <div class="legal-block">
                        <h2>2. Accès aux services</h2>
                        <p>
                            L'accès à la plateforme InfiniSchool est ouvert à toute personne physique majeure ou mineure 
                            autorisée par son représentant légal. L'inscription est gratuite et nécessite la création 
                            d'un compte utilisateur.
                        </p>
                        <p>
                            L'utilisateur s'engage à fournir des informations exactes et à les maintenir à jour. 
                            Il est responsable de la confidentialité de ses identifiants de connexion.
                        </p>
                    </div>

                    <div class="legal-block">
                        <h2>3. Services proposés</h2>
                        <p>InfiniSchool propose les services suivants :</p>
                        <ul>
                            <li>Formations en ligne en direct avec des formateurs qualifiés</li>
                            <li>Accès aux enregistrements des cours</li>
                            <li>Ressources pédagogiques téléchargeables</li>
                            <li>Suivi de progression personnalisé</li>
                            <li>Délivrance de certificats de formation</li>
                        </ul>
                    </div>

                    <div class="legal-block">
                        <h2>4. Inscription et paiement</h2>
                        <p>
                            L'inscription à une formation est soumise au paiement préalable du prix indiqué. 
                            Les prix sont affichés en Francs CFA (FCFA) toutes taxes comprises.
                        </p>
                        <p>
                            Les moyens de paiement acceptés sont : carte bancaire, mobile money (Airtel Money, Moov Money), 
                            et virement bancaire.
                        </p>
                    </div>

                    <div class="legal-block">
                        <h2>5. Droit de rétractation</h2>
                        <p>
                            Conformément à la législation en vigueur, vous disposez d'un délai de 7 jours à compter de 
                            votre inscription pour exercer votre droit de rétractation, à condition de n'avoir pas 
                            commencé à suivre la formation.
                        </p>
                        <p>
                            Pour exercer ce droit, contactez notre service client à l'adresse : contact@infinischool.com
                        </p>
                    </div>

                    <div class="legal-block">
                        <h2>6. Propriété intellectuelle</h2>
                        <p>
                            L'ensemble des contenus présents sur la plateforme (cours, vidéos, documents, logos, etc.) 
                            sont protégés par le droit d'auteur et sont la propriété exclusive d'InfiniSchool ou de 
                            ses partenaires.
                        </p>
                        <p>
                            Toute reproduction, représentation, modification ou distribution sans autorisation préalable 
                            est strictement interdite.
                        </p>
                    </div>

                    <div class="legal-block">
                        <h2>7. Obligations de l'utilisateur</h2>
                        <p>L'utilisateur s'engage à :</p>
                        <ul>
                            <li>Utiliser la plateforme de manière loyale et conforme aux lois en vigueur</li>
                            <li>Ne pas partager ses identifiants de connexion avec des tiers</li>
                            <li>Ne pas télécharger ou diffuser les contenus de formation</li>
                            <li>Respecter les autres utilisateurs et les formateurs</li>
                            <li>Ne pas perturber le bon déroulement des cours</li>
                        </ul>
                    </div>

                    <div class="legal-block">
                        <h2>8. Responsabilité</h2>
                        <p>
                            InfiniSchool s'engage à mettre en œuvre tous les moyens nécessaires pour assurer 
                            un accès continu à la plateforme. Toutefois, nous ne pouvons garantir une disponibilité 
                            permanente en cas de maintenance ou de force majeure.
                        </p>
                    </div>

                    <div class="legal-block">
                        <h2>9. Modification des CGU</h2>
                        <p>
                            InfiniSchool se réserve le droit de modifier les présentes conditions à tout moment. 
                            Les utilisateurs seront informés de toute modification par email ou notification sur le site.
                        </p>
                    </div>

                    <div class="legal-block">
                        <h2>10. Contact</h2>
                        <p>
                            Pour toute question relative aux présentes CGU, vous pouvez nous contacter :
                        </p>
                        <ul>
                            <li>Email : contact@infinischool.com</li>
                            <li>Téléphone : +241 01 12 34 56 7</li>
                            <li>Adresse : Boulevard Triomphal, Libreville, Gabon</li>
                        </ul>
                    </div>

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
    }

    .page-header-title {
        font-size: 3rem;
        font-weight: 800;
        color: white;
        margin-bottom: 15px;
    }

    .page-header-subtitle {
        font-size: 1.1rem;
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

    .legal-content {
        background: white;
        padding: 50px;
        border-radius: 20px;
        box-shadow: 0 5px 30px rgba(0, 0, 0, 0.08);
    }

    .legal-block {
        margin-bottom: 40px;
    }

    .legal-block h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }

    .legal-block p {
        color: var(--text-light);
        line-height: 1.8;
        margin-bottom: 15px;
    }

    .legal-block ul {
        color: var(--text-light);
        line-height: 2;
        padding-left: 20px;
    }

    .legal-block ul li {
        margin-bottom: 8px;
    }

    @media (max-width: 768px) {
        .page-header-title {
            font-size: 2rem;
        }

        .legal-content {
            padding: 30px 20px;
        }
    }
</style>
@endsection
