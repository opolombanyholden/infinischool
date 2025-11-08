<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Constructor - Middleware auth
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Rediriger vers le dashboard approprié selon le rôle
     */
    public function index()
    {
        $user = auth()->user();

        return match($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            default => redirect()->route('home')->with('error', 'Rôle utilisateur non reconnu.'),
        };
    }
}