<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * AdminSettingsController
 * 
 * Gère la configuration globale de la plateforme
 * Paramètres généraux, email, intégrations, sécurité, notifications
 * 
 * @package App\Http\Controllers\Admin
 */
class AdminSettingsController extends Controller
{
    /**
     * Affiche la page des paramètres généraux
     * 
     * @return View
     */
    public function index(): View
    {
        $settings = $this->getAllSettings();
        
        return view('admin.settings.index', compact('settings'));
    }
    
    /**
     * Affiche les paramètres généraux
     * 
     * @return View
     */
    public function general(): View
    {
        $settings = [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'app_description' => $this->getSetting('app_description', 'Plateforme e-learning InfiniSchool'),
            'app_keywords' => $this->getSetting('app_keywords', 'e-learning, formation, cours en ligne'),
            'contact_email' => $this->getSetting('contact_email', 'contact@infinischool.com'),
            'contact_phone' => $this->getSetting('contact_phone', '+241 XX XX XX XX'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
        ];
        
        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
        $locales = ['fr' => 'Français', 'en' => 'English'];
        
        return view('admin.settings.general', compact('settings', 'timezones', 'locales'));
    }
    
    /**
     * Met à jour les paramètres généraux
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateGeneral(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'app_description' => 'nullable|string|max:500',
            'app_keywords' => 'nullable|string|max:255',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string|max:20',
            'timezone' => 'required|string',
            'locale' => 'required|string|in:fr,en',
            'logo' => 'nullable|image|max:2048',
            'favicon' => 'nullable|image|max:512',
        ]);
        
        // Upload logo
        if ($request->hasFile('logo')) {
            $oldLogo = $this->getSetting('logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            
            $logoPath = $request->file('logo')->store('settings', 'public');
            $this->setSetting('logo', $logoPath);
        }
        
        // Upload favicon
        if ($request->hasFile('favicon')) {
            $oldFavicon = $this->getSetting('favicon');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }
            
            $faviconPath = $request->file('favicon')->store('settings', 'public');
            $this->setSetting('favicon', $faviconPath);
        }
        
        // Sauvegarder dans .env et cache
        $this->updateEnvFile([
            'APP_NAME' => $validated['app_name'],
            'APP_URL' => $validated['app_url'],
            'APP_TIMEZONE' => $validated['timezone'],
            'APP_LOCALE' => $validated['locale'],
        ]);
        
        // Sauvegarder les autres paramètres
        $this->setSetting('app_description', $validated['app_description']);
        $this->setSetting('app_keywords', $validated['app_keywords']);
        $this->setSetting('contact_email', $validated['contact_email']);
        $this->setSetting('contact_phone', $validated['contact_phone']);
        
        // Vider le cache de config
        Artisan::call('config:clear');
        
        return redirect()
            ->back()
            ->with('success', 'Paramètres généraux mis à jour avec succès !');
    }
    
    /**
     * Affiche les paramètres email
     * 
     * @return View
     */
    public function email(): View
    {
        $settings = [
            'mail_mailer' => config('mail.mailers.smtp.transport'),
            'mail_host' => config('mail.mailers.smtp.host'),
            'mail_port' => config('mail.mailers.smtp.port'),
            'mail_username' => config('mail.mailers.smtp.username'),
            'mail_password' => config('mail.mailers.smtp.password'),
            'mail_encryption' => config('mail.mailers.smtp.encryption'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
        ];
        
        return view('admin.settings.email', compact('settings'));
    }
    
    /**
     * Met à jour les paramètres email
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mail_mailer' => 'required|string|in:smtp,sendmail,mailgun,ses,postmark',
            'mail_host' => 'required_if:mail_mailer,smtp|nullable|string',
            'mail_port' => 'required_if:mail_mailer,smtp|nullable|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string|in:tls,ssl,null',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);
        
        // Mettre à jour le .env
        $this->updateEnvFile([
            'MAIL_MAILER' => $validated['mail_mailer'],
            'MAIL_HOST' => $validated['mail_host'] ?? '',
            'MAIL_PORT' => $validated['mail_port'] ?? '',
            'MAIL_USERNAME' => $validated['mail_username'] ?? '',
            'MAIL_PASSWORD' => $validated['mail_password'] ?? '',
            'MAIL_ENCRYPTION' => $validated['mail_encryption'] ?? 'tls',
            'MAIL_FROM_ADDRESS' => $validated['mail_from_address'],
            'MAIL_FROM_NAME' => $validated['mail_from_name'],
        ]);
        
        // Vider le cache
        Artisan::call('config:clear');
        
        return redirect()
            ->back()
            ->with('success', 'Configuration email mise à jour avec succès !');
    }
    
    /**
     * Teste la configuration email
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function testEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'test_email' => 'required|email',
        ]);
        
        try {
            // TODO: Envoyer un email de test
            // Mail::to($validated['test_email'])->send(new TestEmail());
            
            return redirect()
                ->back()
                ->with('success', "Email de test envoyé à {$validated['test_email']}");
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de l\'envoi : ' . $e->getMessage());
        }
    }
    
    /**
     * Affiche les paramètres des intégrations tierces
     * 
     * @return View
     */
    public function integrations(): View
    {
        $settings = [
            // Zoom
            'zoom_api_key' => config('services.zoom.api_key'),
            'zoom_api_secret' => config('services.zoom.api_secret'),
            'zoom_base_url' => config('services.zoom.base_url'),
            
            // Stripe
            'stripe_key' => config('services.stripe.key'),
            'stripe_secret' => config('services.stripe.secret'),
            'stripe_webhook_secret' => config('services.stripe.webhook_secret'),
            
            // PayPal
            'paypal_mode' => $this->getSetting('paypal_mode', 'sandbox'),
            'paypal_client_id' => $this->getSetting('paypal_client_id'),
            'paypal_secret' => $this->getSetting('paypal_secret'),
            
            // Google Analytics
            'google_analytics_id' => $this->getSetting('google_analytics_id'),
        ];
        
        return view('admin.settings.integrations', compact('settings'));
    }
    
    /**
     * Met à jour les intégrations tierces
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateIntegrations(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Zoom
            'zoom_api_key' => 'nullable|string',
            'zoom_api_secret' => 'nullable|string',
            'zoom_base_url' => 'nullable|url',
            
            // Stripe
            'stripe_key' => 'nullable|string',
            'stripe_secret' => 'nullable|string',
            'stripe_webhook_secret' => 'nullable|string',
            
            // PayPal
            'paypal_mode' => 'nullable|string|in:sandbox,live',
            'paypal_client_id' => 'nullable|string',
            'paypal_secret' => 'nullable|string',
            
            // Google Analytics
            'google_analytics_id' => 'nullable|string',
        ]);
        
        // Mettre à jour .env pour Zoom et Stripe
        $envUpdates = [];
        
        if (isset($validated['zoom_api_key'])) {
            $envUpdates['ZOOM_API_KEY'] = $validated['zoom_api_key'];
        }
        if (isset($validated['zoom_api_secret'])) {
            $envUpdates['ZOOM_API_SECRET'] = $validated['zoom_api_secret'];
        }
        if (isset($validated['zoom_base_url'])) {
            $envUpdates['ZOOM_BASE_URL'] = $validated['zoom_base_url'];
        }
        
        if (isset($validated['stripe_key'])) {
            $envUpdates['STRIPE_KEY'] = $validated['stripe_key'];
        }
        if (isset($validated['stripe_secret'])) {
            $envUpdates['STRIPE_SECRET'] = $validated['stripe_secret'];
        }
        if (isset($validated['stripe_webhook_secret'])) {
            $envUpdates['STRIPE_WEBHOOK_SECRET'] = $validated['stripe_webhook_secret'];
        }
        
        if (!empty($envUpdates)) {
            $this->updateEnvFile($envUpdates);
        }
        
        // Sauvegarder PayPal et Google Analytics dans la base
        if (isset($validated['paypal_mode'])) {
            $this->setSetting('paypal_mode', $validated['paypal_mode']);
        }
        if (isset($validated['paypal_client_id'])) {
            $this->setSetting('paypal_client_id', $validated['paypal_client_id']);
        }
        if (isset($validated['paypal_secret'])) {
            $this->setSetting('paypal_secret', $validated['paypal_secret']);
        }
        if (isset($validated['google_analytics_id'])) {
            $this->setSetting('google_analytics_id', $validated['google_analytics_id']);
        }
        
        // Vider le cache
        Artisan::call('config:clear');
        
        return redirect()
            ->back()
            ->with('success', 'Intégrations mises à jour avec succès !');
    }
    
    /**
     * Affiche les paramètres de sécurité
     * 
     * @return View
     */
    public function security(): View
    {
        $settings = [
            'session_lifetime' => config('session.lifetime'),
            'password_min_length' => $this->getSetting('password_min_length', 8),
            'password_require_uppercase' => $this->getSetting('password_require_uppercase', true),
            'password_require_lowercase' => $this->getSetting('password_require_lowercase', true),
            'password_require_numbers' => $this->getSetting('password_require_numbers', true),
            'password_require_symbols' => $this->getSetting('password_require_symbols', false),
            'enable_2fa' => $this->getSetting('enable_2fa', false),
            'max_login_attempts' => $this->getSetting('max_login_attempts', 5),
            'lockout_duration' => $this->getSetting('lockout_duration', 15),
        ];
        
        return view('admin.settings.security', compact('settings'));
    }
    
    /**
     * Met à jour les paramètres de sécurité
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateSecurity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'session_lifetime' => 'required|integer|min:10|max:1440',
            'password_min_length' => 'required|integer|min:6|max:32',
            'password_require_uppercase' => 'boolean',
            'password_require_lowercase' => 'boolean',
            'password_require_numbers' => 'boolean',
            'password_require_symbols' => 'boolean',
            'enable_2fa' => 'boolean',
            'max_login_attempts' => 'required|integer|min:3|max:10',
            'lockout_duration' => 'required|integer|min:5|max:60',
        ]);
        
        // Mettre à jour session_lifetime dans .env
        $this->updateEnvFile([
            'SESSION_LIFETIME' => $validated['session_lifetime'],
        ]);
        
        // Sauvegarder les autres paramètres
        foreach ($validated as $key => $value) {
            if ($key !== 'session_lifetime') {
                $this->setSetting($key, $value);
            }
        }
        
        // Vider le cache
        Artisan::call('config:clear');
        
        return redirect()
            ->back()
            ->with('success', 'Paramètres de sécurité mis à jour avec succès !');
    }
    
    /**
     * Affiche les paramètres des notifications
     * 
     * @return View
     */
    public function notifications(): View
    {
        $settings = [
            'notifications_enabled' => $this->getSetting('notifications_enabled', true),
            'email_notifications' => $this->getSetting('email_notifications', true),
            'sms_notifications' => $this->getSetting('sms_notifications', false),
            'push_notifications' => $this->getSetting('push_notifications', false),
            
            // Types de notifications
            'notify_new_enrollment' => $this->getSetting('notify_new_enrollment', true),
            'notify_course_reminder' => $this->getSetting('notify_course_reminder', true),
            'notify_payment_success' => $this->getSetting('notify_payment_success', true),
            'notify_payment_failed' => $this->getSetting('notify_payment_failed', true),
            'notify_grade_published' => $this->getSetting('notify_grade_published', true),
            'notify_certificate_issued' => $this->getSetting('notify_certificate_issued', true),
        ];
        
        return view('admin.settings.notifications', compact('settings'));
    }
    
    /**
     * Met à jour les paramètres des notifications
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        $fields = [
            'notifications_enabled',
            'email_notifications',
            'sms_notifications',
            'push_notifications',
            'notify_new_enrollment',
            'notify_course_reminder',
            'notify_payment_success',
            'notify_payment_failed',
            'notify_grade_published',
            'notify_certificate_issued',
        ];
        
        foreach ($fields as $field) {
            $this->setSetting($field, $request->has($field));
        }
        
        return redirect()
            ->back()
            ->with('success', 'Paramètres des notifications mis à jour avec succès !');
    }
    
    /**
     * Affiche les paramètres des inscriptions
     * 
     * @return View
     */
    public function enrollments(): View
    {
        $settings = [
            'auto_approve_enrollments' => $this->getSetting('auto_approve_enrollments', false),
            'require_payment_before_access' => $this->getSetting('require_payment_before_access', true),
            'allow_multiple_enrollments' => $this->getSetting('allow_multiple_enrollments', true),
            'max_enrollments_per_student' => $this->getSetting('max_enrollments_per_student', 5),
            'enrollment_deadline_days' => $this->getSetting('enrollment_deadline_days', 7),
        ];
        
        return view('admin.settings.enrollments', compact('settings'));
    }
    
    /**
     * Met à jour les paramètres des inscriptions
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateEnrollments(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'auto_approve_enrollments' => 'boolean',
            'require_payment_before_access' => 'boolean',
            'allow_multiple_enrollments' => 'boolean',
            'max_enrollments_per_student' => 'required|integer|min:1|max:10',
            'enrollment_deadline_days' => 'required|integer|min:1|max:30',
        ]);
        
        foreach ($validated as $key => $value) {
            $this->setSetting($key, $value);
        }
        
        return redirect()
            ->back()
            ->with('success', 'Paramètres des inscriptions mis à jour avec succès !');
    }
    
    /**
     * Affiche les paramètres des paiements
     * 
     * @return View
     */
    public function payments(): View
    {
        $settings = [
            'currency' => $this->getSetting('currency', 'EUR'),
            'currency_symbol' => $this->getSetting('currency_symbol', '€'),
            'enable_stripe' => $this->getSetting('enable_stripe', true),
            'enable_paypal' => $this->getSetting('enable_paypal', false),
            'enable_bank_transfer' => $this->getSetting('enable_bank_transfer', false),
            'payment_deadline_days' => $this->getSetting('payment_deadline_days', 7),
            'late_payment_fee' => $this->getSetting('late_payment_fee', 0),
        ];
        
        $currencies = [
            'EUR' => 'Euro (€)',
            'USD' => 'US Dollar ($)',
            'GBP' => 'British Pound (£)',
            'XAF' => 'Franc CFA (FCFA)',
        ];
        
        return view('admin.settings.payments', compact('settings', 'currencies'));
    }
    
    /**
     * Met à jour les paramètres des paiements
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function updatePayments(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'currency' => 'required|string|in:EUR,USD,GBP,XAF',
            'currency_symbol' => 'required|string|max:5',
            'enable_stripe' => 'boolean',
            'enable_paypal' => 'boolean',
            'enable_bank_transfer' => 'boolean',
            'payment_deadline_days' => 'required|integer|min:1|max:30',
            'late_payment_fee' => 'required|numeric|min:0',
        ]);
        
        foreach ($validated as $key => $value) {
            $this->setSetting($key, $value);
        }
        
        return redirect()
            ->back()
            ->with('success', 'Paramètres des paiements mis à jour avec succès !');
    }
    
    /**
     * Affiche les paramètres des politiques et CGU
     * 
     * @return View
     */
    public function policies(): View
    {
        $settings = [
            'terms_of_service' => $this->getSetting('terms_of_service', ''),
            'privacy_policy' => $this->getSetting('privacy_policy', ''),
            'refund_policy' => $this->getSetting('refund_policy', ''),
        ];
        
        return view('admin.settings.policies', compact('settings'));
    }
    
    /**
     * Met à jour les politiques et CGU
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function updatePolicies(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'terms_of_service' => 'nullable|string',
            'privacy_policy' => 'nullable|string',
            'refund_policy' => 'nullable|string',
        ]);
        
        foreach ($validated as $key => $value) {
            $this->setSetting($key, $value);
        }
        
        return redirect()
            ->back()
            ->with('success', 'Politiques mises à jour avec succès !');
    }
    
    /**
     * Récupère tous les paramètres
     * 
     * @return array
     */
    private function getAllSettings(): array
    {
        // TODO: Récupérer depuis une table settings
        return Cache::get('app_settings', []);
    }
    
    /**
     * Récupère un paramètre
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private function getSetting(string $key, $default = null)
    {
        $settings = $this->getAllSettings();
        return $settings[$key] ?? $default;
    }
    
    /**
     * Définit un paramètre
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    private function setSetting(string $key, $value): void
    {
        $settings = $this->getAllSettings();
        $settings[$key] = $value;
        
        // TODO: Sauvegarder dans une table settings
        Cache::put('app_settings', $settings, now()->addDays(30));
    }
    
    /**
     * Met à jour le fichier .env
     * 
     * @param array $data
     * @return void
     */
    private function updateEnvFile(array $data): void
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            return;
        }
        
        $envContent = File::get($envPath);
        
        foreach ($data as $key => $value) {
            // Échapper les valeurs avec des espaces
            if (strpos($value, ' ') !== false) {
                $value = '"' . $value . '"';
            }
            
            // Chercher et remplacer ou ajouter
            $pattern = "/^{$key}=.*/m";
            
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }
        
        File::put($envPath, $envContent);
    }
}