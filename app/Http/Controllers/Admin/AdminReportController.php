<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Formation;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

/**
 * AdminReportController
 * 
 * Gère les rapports et analytics de la plateforme
 * 
 * @package App\Http\Controllers\Admin
 */
class AdminReportController extends Controller
{
    /**
     * Dashboard des rapports
     */
    public function index(): View
    {
        // Résumé des métriques
        $metrics = [
            'users' => [
                'total' => User::count(),
                'students' => User::where('role', 'student')->count(),
                'teachers' => User::where('role', 'teacher')->count(),
                'this_month' => User::whereMonth('created_at', now()->month)->count(),
            ],
            'formations' => [
                'total' => Formation::count(),
                'published' => Formation::where('status', 'published')->count(),
                'pending' => Formation::where('status', 'pending')->count(),
            ],
            'enrollments' => [
                'total' => Enrollment::count(),
                'this_month' => Enrollment::whereMonth('created_at', now()->month)->count(),
            ],
            'revenue' => [
                'total' => Payment::where('status', 'completed')->sum('amount'),
                'this_month' => Payment::where('status', 'completed')
                    ->whereMonth('paid_at', now()->month)
                    ->sum('amount'),
            ],
        ];

        return view('admin.reports.index', compact('metrics'));
    }

    /**
     * Rapport des revenus
     */
    public function revenue(Request $request): View
    {
        $period = $request->input('period', 'month');
        $startDate = $this->getPeriodStartDate($period);

        // Revenus par période
        $payments = Payment::where('status', 'completed')
            ->where('paid_at', '>=', $startDate)
            ->get();

        $totalRevenue = $payments->sum('amount');
        $avgTransaction = $payments->count() > 0 ? $totalRevenue / $payments->count() : 0;

        // Revenus par jour (pour graphique)
        $dailyRevenue = Payment::where('status', 'completed')
            ->where('paid_at', '>=', $startDate)
            ->selectRaw('DATE(paid_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top formations
        $topFormations = Payment::where('status', 'completed')
            ->where('paid_at', '>=', $startDate)
            ->selectRaw('formation_id, SUM(amount) as total, COUNT(*) as count')
            ->with('formation')
            ->groupBy('formation_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Méthodes de paiement
        $paymentMethods = Payment::where('status', 'completed')
            ->where('paid_at', '>=', $startDate)
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get();

        return view('admin.reports.revenue', compact(
            'period',
            'totalRevenue',
            'avgTransaction',
            'dailyRevenue',
            'topFormations',
            'paymentMethods',
            'payments'
        ));
    }

    /**
     * Rapport des utilisateurs
     */
    public function users(Request $request): View
    {
        $period = $request->input('period', 'month');
        $startDate = $this->getPeriodStartDate($period);

        // Statistiques utilisateurs
        $stats = [
            'total' => User::count(),
            'new' => User::where('created_at', '>=', $startDate)->count(),
            'students' => User::where('role', 'student')->count(),
            'teachers' => User::where('role', 'teacher')->count(),
            'admins' => User::where('role', 'admin')->count(),
        ];

        // Inscriptions par jour
        $dailySignups = User::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Inscriptions par rôle
        $byRole = User::selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->get();

        // Derniers inscrits
        $recentUsers = User::latest()
            ->limit(20)
            ->get();

        return view('admin.reports.users', compact('period', 'stats', 'dailySignups', 'byRole', 'recentUsers'));
    }

    /**
     * Rapport des formations
     */
    public function courses(Request $request): View
    {
        $period = $request->input('period', 'month');
        $startDate = $this->getPeriodStartDate($period);

        // Stats formations
        $stats = [
            'total' => Formation::count(),
            'published' => Formation::where('status', 'published')->count(),
            'pending' => Formation::where('status', 'pending')->count(),
            'draft' => Formation::where('status', 'draft')->count(),
        ];

        // Formations les plus populaires (par inscriptions)
        $popularFormations = Formation::withCount(['enrollments'])
            ->orderByDesc('enrollments_count')
            ->limit(10)
            ->get();

        // Formations par catégorie
        $byCategory = Formation::where('status', 'published')
            ->selectRaw('category_id, COUNT(*) as count')
            ->with('category')
            ->groupBy('category_id')
            ->get();

        // Revenus par formation
        $formationRevenue = Formation::withSum([
            'payments as revenue' => function ($q) {
                $q->where('status', 'completed');
            }
        ], 'amount')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        return view('admin.reports.courses', compact('period', 'stats', 'popularFormations', 'byCategory', 'formationRevenue'));
    }

    /**
     * Rapport d'assiduité (placeholder)
     */
    public function attendance(Request $request): View
    {
        // Pour l'instant, données simulées car le modèle Attendance n'existe peut-être pas
        $stats = [
            'total_sessions' => 0,
            'avg_attendance' => 0,
            'completion_rate' => 0,
        ];

        return view('admin.reports.attendance', compact('stats'));
    }

    /**
     * Récupère la date de début selon la période
     */
    private function getPeriodStartDate(string $period): Carbon
    {
        return match ($period) {
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'quarter' => Carbon::now()->subMonths(3),
            'year' => Carbon::now()->subYear(),
            'all' => Carbon::create(2020, 1, 1),
            default => Carbon::now()->subMonth(),
        };
    }
}
