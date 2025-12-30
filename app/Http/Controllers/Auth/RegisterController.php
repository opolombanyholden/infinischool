<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Formation;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Redirection après inscription
     */
    protected $redirectTo = '/registration/confirmed';

    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Afficher le formulaire d'inscription
     */
    public function showRegistrationForm()
    {
        $formations = Formation::published()->orderBy('title')->get();
        return view('auth.register', compact('formations'));
    }

    /**
     * Validation des données d'inscription
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            // Informations personnelles
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],

            // Adresse
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],

            // Formation
            'desired_formation_id' => ['required', 'exists:formations,id'],
            'desired_level' => ['required', 'in:bac,licence,master,doctorat,autre'],

            // Sécurité
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['required', 'accepted'],
        ], [
            'name.required' => 'Le nom complet est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'address.required' => 'L\'adresse complète est obligatoire.',
            'city.required' => 'La ville est obligatoire.',
            'country.required' => 'Le pays est obligatoire.',
            'desired_formation_id.required' => 'Veuillez sélectionner une formation.',
            'desired_formation_id.exists' => 'La formation sélectionnée n\'existe pas.',
            'desired_level.required' => 'Veuillez indiquer le niveau visé.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'terms.accepted' => 'Vous devez accepter les conditions générales.',
        ]);
    }

    /**
     * Créer un nouvel utilisateur
     */
    protected function create(array $data)
    {
        // Générer un numéro étudiant unique
        $studentNumber = 'ETU' . date('Y') . str_pad(User::where('role', 'student')->count() + 1, 5, '0', STR_PAD_LEFT);

        $user = User::create([
            // Informations de base
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'],

            // Adresse
            'address' => $data['address'],
            'city' => $data['city'],
            'country' => $data['country'],

            // Rôle et statut
            'role' => 'student',
            'status' => 'inactive', // Inactif jusqu'à validation admin

            // Formation souhaitée
            'desired_formation_id' => $data['desired_formation_id'],
            'desired_level' => $data['desired_level'],

            // Statut d'inscription
            'registration_status' => 'pending',

            // Numéro étudiant
            'student_number' => $studentNumber,
        ]);

        return $user;
    }

    /**
     * L'utilisateur a été enregistré
     */
    protected function registered(Request $request, $user)
    {
        // Déconnecter immédiatement l'utilisateur
        // (il ne peut pas se connecter tant que son inscription n'est pas validée)
        $this->guard()->logout();

        // Rediriger vers page de confirmation
        return redirect()->route('registration.confirmed')
            ->with('email', $user->email)
            ->with('name', $user->name);
    }
}
