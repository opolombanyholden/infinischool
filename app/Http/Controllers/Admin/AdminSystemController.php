<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Recording;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * AdminSystemController
 * 
 * Gère le monitoring système, alertes, logs et maintenance
 * Surveillance serveur, performance, cache, backups
 * 
 * @package App\Http\Controllers\Admin
 */
class AdminSystemController extends Controller
{
    /**
     * Affiche le dashboard système
     * 
     * @return View
     */
    public function index(): View
    {
        // Informations serveur
        $serverInfo = $this->getServerInfo();
        
        // Utilisation ressources
        $resourceUsage = $this->getResourceUsage();
        
        // Santé de la base de données
        $databaseHealth = $this->getDatabaseHealth();
        
        // Alertes actives
        $activeAlerts = $this->getActiveAlerts();
        
        // Statistiques système
        $systemStats = $this->getSystemStats();
        
        // Derniers logs d'erreur
        $recentErrors = $this->getRecentErrors(10);
        
        // Cache stats
        $cacheStats = $this->getCacheStats();
        
        return view('admin.system.index', compact(
            'serverInfo',
            'resourceUsage',
            'databaseHealth',
            'activeAlerts',
            'systemStats',
            'recentErrors',
            'cacheStats'
        ));
    }
    
    /**
     * Affiche les logs du système
     * 
     * @param Request $request
     * @return View
     */
    public function logs(Request $request): View
    {
        $level = $request->input('level', 'all');
        $date = $request->input('date', Carbon::today()->toDateString());
        
        // Lire le fichier de log Laravel
        $logFile = storage_path("logs/laravel-{$date}.log");
        
        $logs = [];
        if (File::exists($logFile)) {
            $logs = $this->parseLogFile($logFile, $level);
        }
        
        // Statistiques des logs
        $stats = [
            'emergency' => collect($logs)->where('level', 'emergency')->count(),
            'alert' => collect($logs)->where('level', 'alert')->count(),
            'critical' => collect($logs)->where('level', 'critical')->count(),
            'error' => collect($logs)->where('level', 'error')->count(),
            'warning' => collect($logs)->where('level', 'warning')->count(),
            'notice' => collect($logs)->where('level', 'notice')->count(),
            'info' => collect($logs)->where('level', 'info')->count(),
            'debug' => collect($logs)->where('level', 'debug')->count(),
        ];
        
        // Dates disponibles
        $availableDates = $this->getAvailableLogDates();
        
        return view('admin.system.logs', compact('logs', 'stats', 'level', 'date', 'availableDates'));
    }
    
    /**
     * Affiche les détails d'une alerte
     * 
     * @param string $type
     * @return View
     */
    public function alertDetails(string $type): View
    {
        $alertData = $this->getAlertData($type);
        
        return view('admin.system.alert-details', compact('alertData', 'type'));
    }
    
    /**
     * Vide le cache de l'application
     * 
     * @return RedirectResponse
     */
    public function clearCache(): RedirectResponse
    {
        try {
            // Vider tous les caches
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            // Vider le cache applicatif
            Cache::flush();
            
            return redirect()
                ->back()
                ->with('success', 'Tous les caches ont été vidés avec succès !');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors du vidage du cache : ' . $e->getMessage());
        }
    }
    
    /**
     * Optimise l'application
     * 
     * @return RedirectResponse
     */
    public function optimize(): RedirectResponse
    {
        try {
            // Optimiser les routes, config et vues
            Artisan::call('optimize');
            
            return redirect()
                ->back()
                ->with('success', 'Application optimisée avec succès !');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de l\'optimisation : ' . $e->getMessage());
        }
    }
    
    /**
     * Affiche la page de gestion du stockage
     * 
     * @return View
     */
    public function storage(): View
    {
        $storageStats = $this->getStorageStats();
        
        // Fichiers par type
        $filesByType = [
            'avatars' => $this->getDirectorySize('public/avatars'),
            'formations' => $this->getDirectorySize('public/formations'),
            'recordings' => $this->getDirectorySize('recordings'),
            'resources' => $this->getDirectorySize('resources'),
            'temp' => $this->getDirectorySize('public/temp'),
        ];
        
        // Enregistrements les plus volumineux
        $largestRecordings = Recording::orderByDesc('file_size')
            ->limit(10)
            ->get();
        
        return view('admin.system.storage', compact('storageStats', 'filesByType', 'largestRecordings'));
    }
    
    /**
     * Nettoie les fichiers temporaires
     * 
     * @return RedirectResponse
     */
    public function cleanupTemp(): RedirectResponse
    {
        try {
            $deletedCount = 0;
            $freedSpace = 0;
            
            // Nettoyer les fichiers temp de plus de 24h
            $files = Storage::disk('public')->files('temp');
            
            foreach ($files as $file) {
                $lastModified = Storage::disk('public')->lastModified($file);
                
                if ($lastModified < now()->subDay()->timestamp) {
                    $fileSize = Storage::disk('public')->size($file);
                    Storage::disk('public')->delete($file);
                    $deletedCount++;
                    $freedSpace += $fileSize;
                }
            }
            
            $freedSpaceMB = round($freedSpace / 1024 / 1024, 2);
            
            return redirect()
                ->back()
                ->with('success', "{$deletedCount} fichiers supprimés, {$freedSpaceMB} MB libérés.");
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors du nettoyage : ' . $e->getMessage());
        }
    }
    
    /**
     * Affiche la page de gestion des backups
     * 
     * @return View
     */
    public function backups(): View
    {
        $backups = $this->getBackupsList();
        
        $backupStats = [
            'total' => count($backups),
            'total_size' => collect($backups)->sum('size'),
            'last_backup' => collect($backups)->max('date'),
        ];
        
        return view('admin.system.backups', compact('backups', 'backupStats'));
    }
    
    /**
     * Crée un nouveau backup
     * 
     * @return RedirectResponse
     */
    public function createBackup(): RedirectResponse
    {
        try {
            // TODO: Implémenter la création de backup
            // - Dump de la base de données
            // - Archiver les fichiers importants
            // - Stocker dans storage/backups
            
            Artisan::call('backup:run');
            
            return redirect()
                ->back()
                ->with('success', 'Backup créé avec succès !');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la création du backup : ' . $e->getMessage());
        }
    }
    
    /**
     * Télécharge un backup
     * 
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadBackup(string $filename)
    {
        $path = storage_path('backups/' . $filename);
        
        if (!File::exists($path)) {
            abort(404, 'Backup non trouvé.');
        }
        
        return response()->download($path);
    }
    
    /**
     * Supprime un backup
     * 
     * @param string $filename
     * @return RedirectResponse
     */
    public function deleteBackup(string $filename): RedirectResponse
    {
        try {
            $path = storage_path('backups/' . $filename);
            
            if (File::exists($path)) {
                File::delete($path);
                
                return redirect()
                    ->back()
                    ->with('success', 'Backup supprimé avec succès.');
            }
            
            return redirect()
                ->back()
                ->with('error', 'Backup non trouvé.');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }
    
    /**
     * Affiche la page de maintenance
     * 
     * @return View
     */
    public function maintenance(): View
    {
        $isDown = File::exists(storage_path('framework/down'));
        
        return view('admin.system.maintenance', compact('isDown'));
    }
    
    /**
     * Active le mode maintenance
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function enableMaintenance(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'message' => 'nullable|string|max:500',
            'retry' => 'nullable|integer|min:60',
        ]);
        
        try {
            $options = [];
            
            if (!empty($validated['message'])) {
                $options['message'] = $validated['message'];
            }
            
            if (!empty($validated['retry'])) {
                $options['retry'] = $validated['retry'];
            }
            
            Artisan::call('down', $options);
            
            return redirect()
                ->back()
                ->with('success', 'Mode maintenance activé.');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur : ' . $e->getMessage());
        }
    }
    
    /**
     * Désactive le mode maintenance
     * 
     * @return RedirectResponse
     */
    public function disableMaintenance(): RedirectResponse
    {
        try {
            Artisan::call('up');
            
            return redirect()
                ->back()
                ->with('success', 'Mode maintenance désactivé.');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur : ' . $e->getMessage());
        }
    }
    
    /**
     * API : Récupère les métriques en temps réel
     * 
     * @return JsonResponse
     */
    public function metrics(): JsonResponse
    {
        return response()->json([
            'timestamp' => now()->toIso8601String(),
            'server' => $this->getServerInfo(),
            'resources' => $this->getResourceUsage(),
            'database' => $this->getDatabaseHealth(),
            'cache' => $this->getCacheStats(),
        ]);
    }
    
    /**
     * Récupère les informations serveur
     * 
     * @return array
     */
    private function getServerInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'server_os' => PHP_OS,
            'server_time' => now()->format('Y-m-d H:i:s'),
            'timezone' => config('app.timezone'),
            'uptime' => $this->getServerUptime(),
        ];
    }
    
    /**
     * Récupère l'utilisation des ressources
     * 
     * @return array
     */
    private function getResourceUsage(): array
    {
        // CPU (approximation)
        $load = sys_getloadavg();
        $cpuUsage = $load[0] ?? 0;
        
        // Mémoire
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->returnBytes(ini_get('memory_limit'));
        $memoryPercent = $memoryLimit > 0 ? ($memoryUsage / $memoryLimit) * 100 : 0;
        
        // Disque
        $diskTotal = disk_total_space('/');
        $diskFree = disk_free_space('/');
        $diskUsed = $diskTotal - $diskFree;
        $diskPercent = ($diskUsed / $diskTotal) * 100;
        
        return [
            'cpu' => [
                'load' => round($cpuUsage, 2),
                'percent' => round(min($cpuUsage * 10, 100), 1), // Approximation
            ],
            'memory' => [
                'used' => $this->formatBytes($memoryUsage),
                'total' => $this->formatBytes($memoryLimit),
                'percent' => round($memoryPercent, 1),
            ],
            'disk' => [
                'used' => $this->formatBytes($diskUsed),
                'free' => $this->formatBytes($diskFree),
                'total' => $this->formatBytes($diskTotal),
                'percent' => round($diskPercent, 1),
            ],
        ];
    }
    
    /**
     * Récupère la santé de la base de données
     * 
     * @return array
     */
    private function getDatabaseHealth(): array
    {
        try {
            // Test de connexion
            $connectionTime = microtime(true);
            DB::connection()->getPdo();
            $connectionTime = round((microtime(true) - $connectionTime) * 1000, 2);
            
            // Nombre de tables
            $tables = DB::select('SHOW TABLES');
            $tablesCount = count($tables);
            
            // Taille de la base de données
            $dbName = DB::getDatabaseName();
            $sizeQuery = DB::select("
                SELECT 
                    SUM(data_length + index_length) as size 
                FROM information_schema.TABLES 
                WHERE table_schema = ?
            ", [$dbName]);
            
            $dbSize = $sizeQuery[0]->size ?? 0;
            
            // Processus actifs
            $processes = DB::select('SHOW PROCESSLIST');
            $activeProcesses = count($processes);
            
            return [
                'status' => 'healthy',
                'connection_time' => $connectionTime . ' ms',
                'tables_count' => $tablesCount,
                'size' => $this->formatBytes($dbSize),
                'active_connections' => $activeProcesses,
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Récupère les alertes actives
     * 
     * @return array
     */
    private function getActiveAlerts(): array
    {
        $alerts = [];
        
        // Disque > 80%
        $diskPercent = $this->getResourceUsage()['disk']['percent'];
        if ($diskPercent > 80) {
            $alerts[] = [
                'type' => 'disk',
                'level' => $diskPercent > 90 ? 'critical' : 'warning',
                'message' => "Espace disque à {$diskPercent}%",
                'action' => route('admin.system.storage'),
            ];
        }
        
        // Mémoire > 80%
        $memoryPercent = $this->getResourceUsage()['memory']['percent'];
        if ($memoryPercent > 80) {
            $alerts[] = [
                'type' => 'memory',
                'level' => $memoryPercent > 90 ? 'critical' : 'warning',
                'message' => "Utilisation mémoire à {$memoryPercent}%",
                'action' => route('admin.system.index'),
            ];
        }
        
        // Erreurs récentes
        $recentErrors = $this->getRecentErrors(5);
        if (count($recentErrors) > 10) {
            $alerts[] = [
                'type' => 'errors',
                'level' => 'warning',
                'message' => count($recentErrors) . " erreurs dans les dernières 24h",
                'action' => route('admin.system.logs'),
            ];
        }
        
        // Backup ancien
        $lastBackup = $this->getLastBackupDate();
        if ($lastBackup && $lastBackup->diffInDays(now()) > 7) {
            $alerts[] = [
                'type' => 'backup',
                'level' => 'warning',
                'message' => "Dernier backup il y a " . $lastBackup->diffForHumans(),
                'action' => route('admin.system.backups'),
            ];
        }
        
        return $alerts;
    }
    
    /**
     * Récupère les statistiques système
     * 
     * @return array
     */
    private function getSystemStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_courses' => Course::count(),
            'live_courses' => Course::where('status', 'live')->count(),
            'total_recordings' => Recording::count(),
            'cache_hits' => 0, // TODO: Implémenter avec Redis stats
        ];
    }
    
    /**
     * Récupère les erreurs récentes
     * 
     * @param int $limit
     * @return array
     */
    private function getRecentErrors(int $limit = 10): array
    {
        $logFile = storage_path('logs/laravel-' . Carbon::today()->toDateString() . '.log');
        
        if (!File::exists($logFile)) {
            return [];
        }
        
        $errors = $this->parseLogFile($logFile, 'error');
        
        return array_slice($errors, 0, $limit);
    }
    
    /**
     * Récupère les statistiques du cache
     * 
     * @return array
     */
    private function getCacheStats(): array
    {
        // TODO: Implémenter avec Redis si disponible
        return [
            'driver' => config('cache.default'),
            'status' => 'active',
        ];
    }
    
    /**
     * Parse un fichier de log
     * 
     * @param string $file
     * @param string $level
     * @return array
     */
    private function parseLogFile(string $file, string $level = 'all'): array
    {
        $logs = [];
        $content = File::get($file);
        
        // Pattern pour parser les logs Laravel
        $pattern = '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.+)/';
        
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $logLevel = strtolower($match[2]);
            
            if ($level === 'all' || $logLevel === $level) {
                $logs[] = [
                    'timestamp' => $match[1],
                    'level' => $logLevel,
                    'message' => $match[3],
                ];
            }
        }
        
        return array_reverse($logs); // Plus récent en premier
    }
    
    /**
     * Récupère les dates de logs disponibles
     * 
     * @return array
     */
    private function getAvailableLogDates(): array
    {
        $files = File::files(storage_path('logs'));
        $dates = [];
        
        foreach ($files as $file) {
            $filename = $file->getFilename();
            if (preg_match('/laravel-(\d{4}-\d{2}-\d{2})\.log/', $filename, $matches)) {
                $dates[] = $matches[1];
            }
        }
        
        return array_reverse($dates);
    }
    
    /**
     * Récupère les statistiques de stockage
     * 
     * @return array
     */
    private function getStorageStats(): array
    {
        $storagePath = storage_path('app');
        
        return [
            'total' => $this->getDirectorySize($storagePath),
            'total_formatted' => $this->formatBytes($this->getDirectorySize($storagePath)),
        ];
    }
    
    /**
     * Récupère la taille d'un répertoire
     * 
     * @param string $path
     * @return int
     */
    private function getDirectorySize(string $path): int
    {
        $size = 0;
        
        if (!File::exists(storage_path('app/' . $path))) {
            return 0;
        }
        
        foreach (File::allFiles(storage_path('app/' . $path)) as $file) {
            $size += $file->getSize();
        }
        
        return $size;
    }
    
    /**
     * Récupère la liste des backups
     * 
     * @return array
     */
    private function getBackupsList(): array
    {
        $backupsPath = storage_path('backups');
        
        if (!File::exists($backupsPath)) {
            return [];
        }
        
        $files = File::files($backupsPath);
        $backups = [];
        
        foreach ($files as $file) {
            $backups[] = [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'size_formatted' => $this->formatBytes($file->getSize()),
                'date' => Carbon::createFromTimestamp($file->getMTime()),
            ];
        }
        
        return collect($backups)->sortByDesc('date')->values()->toArray();
    }
    
    /**
     * Récupère la date du dernier backup
     * 
     * @return Carbon|null
     */
    private function getLastBackupDate(): ?Carbon
    {
        $backups = $this->getBackupsList();
        
        return !empty($backups) ? $backups[0]['date'] : null;
    }
    
    /**
     * Récupère les données d'une alerte
     * 
     * @param string $type
     * @return array
     */
    private function getAlertData(string $type): array
    {
        switch ($type) {
            case 'disk':
                return $this->getResourceUsage()['disk'];
            case 'memory':
                return $this->getResourceUsage()['memory'];
            case 'errors':
                return ['errors' => $this->getRecentErrors(50)];
            default:
                return [];
        }
    }
    
    /**
     * Récupère l'uptime du serveur (Linux)
     * 
     * @return string|null
     */
    private function getServerUptime(): ?string
    {
        if (PHP_OS_FAMILY === 'Linux' && File::exists('/proc/uptime')) {
            $uptime = File::get('/proc/uptime');
            $seconds = (int) explode(' ', $uptime)[0];
            
            $days = floor($seconds / 86400);
            $hours = floor(($seconds % 86400) / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            
            return "{$days}j {$hours}h {$minutes}m";
        }
        
        return null;
    }
    
    /**
     * Convertit une chaîne de bytes en nombre
     * 
     * @param string $size
     * @return int
     */
    private function returnBytes(string $size): int
    {
        $unit = strtolower(substr($size, -1));
        $value = (int) $size;
        
        switch ($unit) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
    
    /**
     * Formate un nombre de bytes
     * 
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}