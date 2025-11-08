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
        $formations = Formation::active()
            ->published()
            ->orderBy('students_count', 'desc')
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
     * Page Équipe pédagogique
     */
    public function teachers()
    {
        $teachers = User::where('role', 'teacher')
            ->where('is_active', true)
            ->withCount('courses')
            ->get();

        return view('public.teachers', compact('teachers'));
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
                'answer' => 'Oui, tous nos cours sont dispensés en direct via visioconférence avec possibilité d\'interaction.',
            ],
            // ... autres FAQs
        ];

        return view('public.faq', compact('faqs'));
    }

    /**
     * Conditions Générales d'Utilisation
     */
    public function terms()
    {
        return view('public.terms');
    }

    /**
     * Politique de confidentialité
     */
    public function privacy()
    {
        return view('public.privacy');
    }
}