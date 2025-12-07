<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RoleMiddleware
 * 
 * Vérifie que l'utilisateur authentifié possède le rôle requis.
 * 
 * Usage dans routes: 
 *   - middleware('role:admin')
 *   - middleware('role:teacher')  
 *   - middleware('role:student')
 *   - middleware('role:teacher,admin') // plusieurs rôles autorisés
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  Rôles autorisés
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Vérifier que l'utilisateur est authentifié
        if (!$request->user()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentification requise.'
                ], 401);
            }
            
            return redirect()->route('login')
                ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        // Récupérer le rôle de l'utilisateur
        $userRole = $request->user()->role ?? 'guest';

        // Vérifier si l'utilisateur a l'un des rôles autorisés
        if (!in_array($userRole, $roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé pour votre rôle.'
                ], 403);
            }
            
            // Rediriger vers le dashboard approprié selon le rôle
            return $this->redirectToDashboard($userRole);
        }

        return $next($request);
    }

    /**
     * Rediriger l'utilisateur vers son dashboard approprié
     */
    protected function redirectToDashboard(string $role): Response
    {
        $message = 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.';

        switch ($role) {
            case 'admin':
                return redirect()->route('admin.dashboard')->with('warning', $message);
            
            case 'teacher':
                return redirect()->route('teacher.dashboard')->with('warning', $message);
            
            case 'student':
                return redirect()->route('student.dashboard')->with('warning', $message);
            
            default:
                return redirect()->route('home')->with('error', $message);
        }
    }
}