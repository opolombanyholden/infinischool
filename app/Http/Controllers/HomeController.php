<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Models\Subject;
use App\Models\User;
use App\Models\Certificate;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Afficher la page d'accueil
     */
    public function index()
    {
        // Formations populaires (limitées à 6)
        // ✅ CORRECTION: Changé 'students_count' en 'enrolled_count'
        $formations = Formation::active()
            ->published()
            ->orderBy('enrolled_count', 'desc')  // ✅ CORRIGÉ
            ->limit(6)
            ->get();

        // Statistiques globales
        $stats = [
            'total_students' => User::where('role', 'student')->count(),
            'total_teachers' => User::where('role', 'teacher')->count(),
            'total_formations' => Formation::active()->count(),
            'total_certificates' => Certificate::count(),
        ];

        // Témoignages (à implémenter avec un modèle Testimonial si nécessaire)
        $testimonials = []; // TODO: Ajouter modèle Testimonial

        return view('welcome', compact('formations', 'stats', 'testimonials'));
    }

    /**
     * Page À propos
     */
    public function about()
    {
        $stats = [
            'years_experience' => 5,
            'total_students' => User::where('role', 'student')->count(),
            'total_courses' => Subject::count(),
            'success_rate' => 95,
        ];

        return view('public.about', compact('stats'));
    }

    /**
     * Page Enseignants
     */
    public function teachers()
    {
        $teachers = User::where('role', 'teacher')
            ->where('status', 'active')  // ✅ Ajouté filtre sur le statut
            ->withCount('courses')
            ->get();

        return view('public.teachers', compact('teachers'));
    }

    /**
     * Page Contact
     */
    public function contact()
    {
        return view('public.contact');
    }

    /**
     * Traiter le formulaire de contact
     */
    public function sendContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        // TODO: Envoyer email ou stocker dans DB
        // Mail::to(config('mail.from.address'))->send(new ContactMail($validated));

        return back()->with('success', 'Votre message a été envoyé avec succès !');
    }

    /**
     * Page Mentions légales
     */
    public function legal()
    {
        return view('public.legal');
    }

    /**
     * Page Conditions générales
     */
    public function terms()
    {
        return view('public.terms');
    }

    /**
     * Page Politique de confidentialité
     */
    public function privacy()
    {
        return view('public.privacy');
    }

    /**
     * Page FAQ
     */
    public function faq()
    {
        // TODO: Créer modèle FAQ si nécessaire
        $faqs = [
            [
                'question' => 'Comment s\'inscrire à une formation ?',
                'answer' => 'Cliquez sur "Nos Formations", choisissez la formation désirée et suivez les étapes d\'inscription.',
            ],
            [
                'question' => 'Les cours sont-ils en direct ?',
                'answer' => 'Oui ! Tous nos cours se déroulent en direct avec nos enseignants via visioconférence.',
            ],
            [
                'question' => 'Puis-je obtenir un certificat ?',
                'answer' => 'Oui, vous recevrez un certificat officiel après avoir complété votre formation avec succès.',
            ],
            [
                'question' => 'Quelle est la durée des formations ?',
                'answer' => 'La durée varie selon les formations, de quelques semaines à plusieurs mois. Consultez la fiche de chaque formation pour plus de détails.',
            ],
            [
                'question' => 'Comment contacter un enseignant ?',
                'answer' => 'Vous pouvez contacter vos enseignants via la messagerie interne de votre espace étudiant.',
            ],
        ];

        return view('public.faq', compact('faqs'));
    }
}