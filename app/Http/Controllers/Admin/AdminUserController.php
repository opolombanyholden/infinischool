<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

/**
 * AdminUserController
 * 
 * Gère la gestion complète des utilisateurs de la plateforme
 * CRUD, filtres avancés, import/export, validation enseignants
 * 
 * @package App\Http\Controllers\Admin
 */
class AdminUserController extends Controller
{
    /**
     * Affiche la liste des utilisateurs avec filtres
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = User::query();
        
        // Filtre par rôle
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        
        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtre par date d'inscription
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Recherche multi-critères
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Tri
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Pagination
        $perPage = $request->input('per_page', 25);
        $users = $query->paginate($perPage)->withQueryString();
        
        // Statistiques globales
        $stats = [
            'total' => User::count(),
            'students' => User::where('role', 'student')->count(),
            'teachers' => User::where('role', 'teacher')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'active' => User::where('status', 'active')->count(),
            'pending' => User::where('status', 'pending')->count(),
            'suspended' => User::where('status', 'suspended')->count(),
        ];
        
        return view('admin.users.index', compact('users', 'stats'));
    }
    
    /**
     * Affiche le formulaire de création d'utilisateur
     * 
     * @return View
     */
    public function create(): View
    {
        return view('admin.users.create');
    }
    
    /**
     * Enregistre un nouvel utilisateur
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['admin', 'teacher', 'student'])],
            'status' => ['required', Rule::in(['active', 'pending', 'suspended', 'banned'])],
            'avatar' => 'nullable|image|max:2048',
            'bio' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date|before:today',
        ]);
        
        // Upload avatar si présent
        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')
                ->store('avatars', 'public');
        }
        
        // Hash du mot de passe
        $validated['password'] = Hash::make($validated['password']);
        
        // Créer l'utilisateur
        $user = User::create($validated);
        
        // TODO: Envoyer email de bienvenue
        // Mail::to($user->email)->send(new WelcomeEmail($user));
        
        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Utilisateur créé avec succès !');
    }
    
    /**
     * Affiche les détails d'un utilisateur
     * 
     * @param User $user
     * @return View
     */
    public function show(User $user): View
    {
        // Statistiques de l'utilisateur
        $stats = $this->getUserStats($user);
        
        // Activité récente
        $recentActivity = $this->getUserActivity($user, 10);
        
        // Données spécifiques selon le rôle
        $roleData = $this->getRoleSpecificData($user);
        
        return view('admin.users.show', compact('user', 'stats', 'recentActivity', 'roleData'));
    }
    
    /**
     * Affiche le formulaire d'édition d'utilisateur
     * 
     * @param User $user
     * @return View
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }
    
    /**
     * Met à jour un utilisateur
     * 
     * @param Request $request
     * @param User $user
     * @return RedirectResponse
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in(['admin', 'teacher', 'student'])],
            'status' => ['required', Rule::in(['active', 'pending', 'suspended', 'banned'])],
            'avatar' => 'nullable|image|max:2048',
            'bio' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date|before:today',
        ]);
        
        // Upload avatar si présent
        if ($request->hasFile('avatar')) {
            // Supprimer l'ancien avatar
            if ($user->avatar) {
                \Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')
                ->store('avatars', 'public');
        }
        
        // Hash du mot de passe si modifié
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        
        // Mise à jour
        $user->update($validated);
        
        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Utilisateur mis à jour avec succès !');
    }
    
    /**
     * Supprime un utilisateur
     * 
     * @param User $user
     * @return RedirectResponse
     */
    public function destroy(User $user): RedirectResponse
    {
        // Empêcher la suppression de son propre compte
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte !');
        }
        
        // TODO: Gérer les contraintes d'intégrité (cascade ou soft delete)
        // Pour l'instant, on vérifie juste qu'il n'a pas de cours actifs
        if ($user->role === 'teacher') {
            $activeCourses = Course::where('teacher_id', $user->id)
                ->where('status', 'scheduled')
                ->count();
            
            if ($activeCourses > 0) {
                return redirect()
                    ->route('admin.users.show', $user)
                    ->with('error', "Impossible de supprimer : cet enseignant a {$activeCourses} cours programmés.");
            }
        }
        
        if ($user->role === 'student') {
            $activeEnrollments = Enrollment::where('user_id', $user->id)
                ->where('status', 'active')
                ->count();
            
            if ($activeEnrollments > 0) {
                return redirect()
                    ->route('admin.users.show', $user)
                    ->with('error', "Impossible de supprimer : cet étudiant a {$activeEnrollments} inscriptions actives.");
            }
        }
        
        // Supprimer l'avatar
        if ($user->avatar) {
            \Storage::disk('public')->delete($user->avatar);
        }
        
        $user->delete();
        
        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès !');
    }
    
    /**
     * Suspend un utilisateur
     * 
     * @param User $user
     * @return RedirectResponse
     */
    public function suspend(User $user): RedirectResponse
    {
        $user->update(['status' => 'suspended']);
        
        // TODO: Envoyer notification à l'utilisateur
        
        return redirect()
            ->back()
            ->with('success', 'Utilisateur suspendu avec succès !');
    }
    
    /**
     * Active un utilisateur
     * 
     * @param User $user
     * @return RedirectResponse
     */
    public function activate(User $user): RedirectResponse
    {
        $user->update(['status' => 'active']);
        
        // TODO: Envoyer notification à l'utilisateur
        
        return redirect()
            ->back()
            ->with('success', 'Utilisateur activé avec succès !');
    }
    
    /**
     * Bannit un utilisateur
     * 
     * @param User $user
     * @return RedirectResponse
     */
    public function ban(User $user): RedirectResponse
    {
        $user->update(['status' => 'banned']);
        
        // TODO: Envoyer notification à l'utilisateur
        
        return redirect()
            ->back()
            ->with('success', 'Utilisateur banni avec succès !');
    }
    
    /**
     * Actions en masse sur les utilisateurs
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['activate', 'suspend', 'ban', 'delete'])],
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);
        
        $userIds = $validated['user_ids'];
        $action = $validated['action'];
        
        // Empêcher l'action sur son propre compte
        if (in_array(auth()->id(), $userIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas effectuer cette action sur votre propre compte !',
            ], 422);
        }
        
        DB::beginTransaction();
        
        try {
            switch ($action) {
                case 'activate':
                    User::whereIn('id', $userIds)->update(['status' => 'active']);
                    $message = count($userIds) . ' utilisateur(s) activé(s)';
                    break;
                    
                case 'suspend':
                    User::whereIn('id', $userIds)->update(['status' => 'suspended']);
                    $message = count($userIds) . ' utilisateur(s) suspendu(s)';
                    break;
                    
                case 'ban':
                    User::whereIn('id', $userIds)->update(['status' => 'banned']);
                    $message = count($userIds) . ' utilisateur(s) banni(s)';
                    break;
                    
                case 'delete':
                    // TODO: Vérifier les contraintes avant suppression
                    User::whereIn('id', $userIds)->delete();
                    $message = count($userIds) . ' utilisateur(s) supprimé(s)';
                    break;
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'action : ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Export des utilisateurs en CSV
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $query = User::query();
        
        // Appliquer les mêmes filtres que l'index
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $users = $query->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users-' . date('Y-m-d') . '.csv"',
        ];
        
        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, ['ID', 'Nom', 'Email', 'Téléphone', 'Rôle', 'Statut', 'Date d\'inscription']);
            
            // Données
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone,
                    $user->role,
                    $user->status,
                    $user->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Import des utilisateurs depuis CSV
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function import(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        
        try {
            // TODO: Implémenter l'import avec Laravel Excel ou traitement manuel
            // Excel::import(new UsersImport, $request->file('file'));
            
            return redirect()->route('admin.users.index')
                ->with('success', 'Import effectué avec succès !');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }
    
    /**
     * Récupère les statistiques d'un utilisateur
     * 
     * @param User $user
     * @return array
     */
    private function getUserStats(User $user): array
    {
        $stats = [
            'member_since' => $user->created_at->diffForHumans(),
            'last_login' => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais',
            'total_logins' => DB::table('user_login_history')
                ->where('user_id', $user->id)
                ->count(),
        ];
        
        // Statistiques selon le rôle
        switch ($user->role) {
            case 'student':
                $stats['enrollments'] = Enrollment::where('user_id', $user->id)->count();
                $stats['completed_courses'] = DB::table('attendances')
                    ->where('user_id', $user->id)
                    ->where('status', 'present')
                    ->count();
                $stats['avg_grade'] = DB::table('grades')
                    ->where('user_id', $user->id)
                    ->avg('grade') ?? 0;
                break;
                
            case 'teacher':
                $stats['classes'] = DB::table('class_teacher')
                    ->where('teacher_id', $user->id)
                    ->count();
                $stats['total_courses'] = DB::table('courses')
                    ->where('teacher_id', $user->id)
                    ->count();
                $stats['total_students'] = DB::table('enrollments')
                    ->join('courses', 'enrollments.course_id', '=', 'courses.id')
                    ->where('courses.teacher_id', $user->id)
                    ->distinct('enrollments.user_id')
                    ->count('enrollments.user_id');
                break;
        }
        
        return $stats;
    }
    
    /**
     * Récupère l'activité récente d'un utilisateur
     * 
     * @param User $user
     * @param int $limit
     * @return array
     */
    private function getUserActivity(User $user, int $limit = 10): array
    {
        // TODO: Implémenter avec une table activity_logs
        return [];
    }
    
    /**
     * Récupère les données spécifiques au rôle
     * 
     * @param User $user
     * @return array
     */
    private function getRoleSpecificData(User $user): array
    {
        $data = [];
        
        switch ($user->role) {
            case 'student':
                $data['enrollments'] = Enrollment::with('formation')
                    ->where('user_id', $user->id)
                    ->latest()
                    ->limit(5)
                    ->get();
                $data['payments'] = Payment::where('student_id', $user->id)
                    ->latest()
                    ->limit(5)
                    ->get();
                break;
                
            case 'teacher':
                $data['courses'] = Course::with('subject')
                    ->where('teacher_id', $user->id)
                    ->latest()
                    ->limit(5)
                    ->get();
                $data['classes'] = DB::table('class_teacher')
                    ->join('classes', 'class_teacher.class_id', '=', 'classes.id')
                    ->where('class_teacher.teacher_id', $user->id)
                    ->get();
                break;
        }
        
        return $data;
    }
}