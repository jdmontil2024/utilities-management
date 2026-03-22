<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Show the settings index page.
     */
    public function index()
    {
        $settings = [
            'general' => $this->getGeneralSettings(),
            'billing' => $this->getBillingSettings(),
            'notifications' => $this->getNotificationSettings(),
            'maintenance' => $this->getMaintenanceSettings(),
            'system' => $this->getSystemSettings(),
        ];

        return view('settings.index', compact('settings'));
    }

    /**
     * Update general settings.
     */
    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:20',
            'company_email' => 'nullable|email|max:255',
            'company_website' => 'nullable|url|max:255',
            'timezone' => 'required|timezone',
            'date_format' => 'required|in:Y-m-d,m/d/Y,d/m/Y',
            'time_format' => 'required|in:H:i,h:i A',
            'currency' => 'required|in:USD,EUR,GBP,INR',
            'currency_symbol' => 'required|string|max:5',
            'logo' => 'nullable|image|max:2048',
            'favicon' => 'nullable|image|max:1024',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('settings', 'public');
            $validated['logo_path'] = $path;
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('settings', 'public');
            $validated['favicon_path'] = $path;
        }

        // Save settings (you might want to use a settings table or config file)
        foreach ($validated as $key => $value) {
            if (!in_array($key, ['logo', 'favicon'])) {
                $this->saveSetting($key, $value);
            }
        }

        // Clear cache
        Cache::forget('settings.general');

        return redirect()->route('settings.index')
            ->with('success', 'General settings updated successfully.');
    }

    /**
     * Update billing settings.
     */
    public function updateBilling(Request $request)
    {
        $validated = $request->validate([
            'invoice_prefix' => 'required|string|max:10',
            'invoice_start_number' => 'required|integer|min:1',
            'payment_terms' => 'required|integer|min:1|max:90',
            'late_fee_percentage' => 'required|numeric|min:0|max:100',
            'late_fee_fixed' => 'required|numeric|min:0',
            'tax_enabled' => 'required|boolean',
            'default_tax_rate' => 'required_if:tax_enabled,true|numeric|min:0|max:100',
            'auto_generate_bills' => 'required|boolean',
            'bill_generation_day' => 'required|integer|min:1|max:28',
            'auto_send_bills' => 'required|boolean',
            'payment_methods' => 'required|array',
            'payment_methods.*' => 'in:credit_card,bank_transfer,cash,check',
        ]);

        foreach ($validated as $key => $value) {
            $this->saveSetting('billing.' . $key, $value);
        }

        Cache::forget('settings.billing');

        return redirect()->route('settings.index')
            ->with('success', 'Billing settings updated successfully.');
    }

    /**
     * Update notification settings.
     */
    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'email_enabled' => 'required|boolean',
            'smtp_host' => 'required_if:email_enabled,true|string',
            'smtp_port' => 'required_if:email_enabled,true|integer',
            'smtp_username' => 'nullable|string',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'nullable|in:tls,ssl',
            'from_email' => 'required_if:email_enabled,true|email',
            'from_name' => 'required_if:email_enabled,true|string',
            
            'sms_enabled' => 'required|boolean',
            'sms_provider' => 'required_if:sms_enabled,true|string',
            'sms_api_key' => 'required_if:sms_enabled,true|string',
            'sms_sender_id' => 'required_if:sms_enabled,true|string',
            
            'notification_bill_due' => 'required|boolean',
            'notification_bill_overdue' => 'required|boolean',
            'notification_maintenance_assigned' => 'required|boolean',
            'notification_maintenance_completed' => 'required|boolean',
            'notification_new_tenant' => 'required|boolean',
            'notification_lease_expiring' => 'required|boolean',
        ]);

        foreach ($validated as $key => $value) {
            $this->saveSetting('notifications.' . $key, $value);
        }

        Cache::forget('settings.notifications');

        return redirect()->route('settings.index')
            ->with('success', 'Notification settings updated successfully.');
    }

    /**
     * Update maintenance settings.
     */
    public function updateMaintenance(Request $request)
    {
        $validated = $request->validate([
            'maintenance_sla_enabled' => 'required|boolean',
            'emergency_sla_hours' => 'required|integer|min:1|max:24',
            'high_priority_sla_hours' => 'required|integer|min:1|max:72',
            'medium_priority_sla_hours' => 'required|integer|min:1|max:168',
            'low_priority_sla_hours' => 'required|integer|min:1|max:336',
            
            'preventive_maintenance_enabled' => 'required|boolean',
            'pm_reminder_days' => 'required|integer|min:1|max:30',
            
            'vendor_rating_enabled' => 'required|boolean',
            'auto_assign_vendors' => 'required|boolean',
            
            'work_order_prefix' => 'required|string|max:10',
            'work_order_start_number' => 'required|integer|min:1',
        ]);

        foreach ($validated as $key => $value) {
            $this->saveSetting('maintenance.' . $key, $value);
        }

        Cache::forget('settings.maintenance');

        return redirect()->route('settings.index')
            ->with('success', 'Maintenance settings updated successfully.');
    }

    /**
     * Update system settings.
     */
    public function updateSystem(Request $request)
    {
        $validated = $request->validate([
            'backup_enabled' => 'required|boolean',
            'backup_frequency' => 'required|in:daily,weekly,monthly',
            'backup_retention_days' => 'required|integer|min:1|max:365',
            
            'log_retention_days' => 'required|integer|min:1|max:365',
            'activity_log_enabled' => 'required|boolean',
            
            'api_enabled' => 'required|boolean',
            'api_rate_limit' => 'required|integer|min:1|max:1000',
            
            'maintenance_mode' => 'required|boolean',
            'debug_mode' => 'required|boolean',
            
            'cache_driver' => 'required|in:file,redis,memcached',
            'queue_driver' => 'required|in:sync,database,redis',
        ]);

        foreach ($validated as $key => $value) {
            $this->saveSetting('system.' . $key, $value);
        }

        Cache::forget('settings.system');

        return redirect()->route('settings.index')
            ->with('success', 'System settings updated successfully.');
    }

    /**
     * Clear application cache.
     */
    public function clearCache(Request $request)
    {
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        \Artisan::call('view:clear');
        \Artisan::call('route:clear');

        return redirect()->back()->with('success', 'Application cache cleared successfully.');
    }

    /**
     * Run database migrations.
     */
    public function runMigrations(Request $request)
    {
        \Artisan::call('migrate', ['--force' => true]);

        return redirect()->back()->with('success', 'Database migrations completed successfully.');
    }

    /**
     * Generate application key.
     */
    public function generateKey(Request $request)
    {
        \Artisan::call('key:generate', ['--force' => true]);

        return redirect()->back()->with('success', 'Application key generated successfully.');
    }

    /**
     * Get general settings.
     */
    private function getGeneralSettings()
    {
        return Cache::remember('settings.general', 3600, function () {
            return [
                'company_name' => config('settings.company_name', 'Property Management Inc.'),
                'company_address' => config('settings.company_address', ''),
                'company_phone' => config('settings.company_phone', ''),
                'company_email' => config('settings.company_email', ''),
                'company_website' => config('settings.company_website', ''),
                'timezone' => config('settings.timezone', 'UTC'),
                'date_format' => config('settings.date_format', 'Y-m-d'),
                'time_format' => config('settings.time_format', 'H:i'),
                'currency' => config('settings.currency', 'USD'),
                'currency_symbol' => config('settings.currency_symbol', '$'),
                'logo_path' => config('settings.logo_path', ''),
                'favicon_path' => config('settings.favicon_path', ''),
            ];
        });
    }

    /**
     * Get billing settings.
     */
    private function getBillingSettings()
    {
        return Cache::remember('settings.billing', 3600, function () {
            return [
                'invoice_prefix' => config('settings.billing.invoice_prefix', 'INV-'),
                'invoice_start_number' => config('settings.billing.invoice_start_number', 1000),
                'payment_terms' => config('settings.billing.payment_terms', 30),
                'late_fee_percentage' => config('settings.billing.late_fee_percentage', 5),
                'late_fee_fixed' => config('settings.billing.late_fee_fixed', 25),
                'tax_enabled' => config('settings.billing.tax_enabled', true),
                'default_tax_rate' => config('settings.billing.default_tax_rate', 8.5),
                'auto_generate_bills' => config('settings.billing.auto_generate_bills', true),
                'bill_generation_day' => config('settings.billing.bill_generation_day', 1),
                'auto_send_bills' => config('settings.billing.auto_send_bills', true),
                'payment_methods' => config('settings.billing.payment_methods', ['credit_card', 'bank_transfer', 'cash']),
            ];
        });
    }

    /**
     * Get notification settings.
     */
    private function getNotificationSettings()
    {
        return Cache::remember('settings.notifications', 3600, function () {
            return [
                'email_enabled' => config('settings.notifications.email_enabled', true),
                'smtp_host' => config('settings.notifications.smtp_host', ''),
                'smtp_port' => config('settings.notifications.smtp_port', 587),
                'smtp_username' => config('settings.notifications.smtp_username', ''),
                'smtp_password' => config('settings.notifications.smtp_password', ''),
                'smtp_encryption' => config('settings.notifications.smtp_encryption', 'tls'),
                'from_email' => config('settings.notifications.from_email', ''),
                'from_name' => config('settings.notifications.from_name', ''),
                
                'sms_enabled' => config('settings.notifications.sms_enabled', false),
                'sms_provider' => config('settings.notifications.sms_provider', ''),
                'sms_api_key' => config('settings.notifications.sms_api_key', ''),
                'sms_sender_id' => config('settings.notifications.sms_sender_id', ''),
                
                'notification_bill_due' => config('settings.notifications.notification_bill_due', true),
                'notification_bill_overdue' => config('settings.notifications.notification_bill_overdue', true),
                'notification_maintenance_assigned' => config('settings.notifications.notification_maintenance_assigned', true),
                'notification_maintenance_completed' => config('settings.notifications.notification_maintenance_completed', true),
                'notification_new_tenant' => config('settings.notifications.notification_new_tenant', true),
                'notification_lease_expiring' => config('settings.notifications.notification_lease_expiring', true),
            ];
        });
    }

    /**
     * Get maintenance settings.
     */
    private function getMaintenanceSettings()
    {
        return Cache::remember('settings.maintenance', 3600, function () {
            return [
                'maintenance_sla_enabled' => config('settings.maintenance.maintenance_sla_enabled', true),
                'emergency_sla_hours' => config('settings.maintenance.emergency_sla_hours', 4),
                'high_priority_sla_hours' => config('settings.maintenance.high_priority_sla_hours', 24),
                'medium_priority_sla_hours' => config('settings.maintenance.medium_priority_sla_hours', 72),
                'low_priority_sla_hours' => config('settings.maintenance.low_priority_sla_hours', 168),
                
                'preventive_maintenance_enabled' => config('settings.maintenance.preventive_maintenance_enabled', true),
                'pm_reminder_days' => config('settings.maintenance.pm_reminder_days', 7),
                
                'vendor_rating_enabled' => config('settings.maintenance.vendor_rating_enabled', true),
                'auto_assign_vendors' => config('settings.maintenance.auto_assign_vendors', false),
                
                'work_order_prefix' => config('settings.maintenance.work_order_prefix', 'WO-'),
                'work_order_start_number' => config('settings.maintenance.work_order_start_number', 1000),
            ];
        });
    }

    /**
     * Get system settings.
     */
    private function getSystemSettings()
    {
        return Cache::remember('settings.system', 3600, function () {
            return [
                'backup_enabled' => config('settings.system.backup_enabled', false),
                'backup_frequency' => config('settings.system.backup_frequency', 'daily'),
                'backup_retention_days' => config('settings.system.backup_retention_days', 30),
                
                'log_retention_days' => config('settings.system.log_retention_days', 90),
                'activity_log_enabled' => config('settings.system.activity_log_enabled', true),
                
                'api_enabled' => config('settings.system.api_enabled', true),
                'api_rate_limit' => config('settings.system.api_rate_limit', 60),
                
                'maintenance_mode' => config('settings.system.maintenance_mode', false),
                'debug_mode' => config('settings.system.debug_mode', false),
                
                'cache_driver' => config('settings.system.cache_driver', 'file'),
                'queue_driver' => config('settings.system.queue_driver', 'sync'),
            ];
        });
    }

    /**
     * Save a setting to the database or config file.
     */
    private function saveSetting($key, $value)
    {
        // You can implement different storage methods:
        // 1. Database table 'settings'
        // 2. Config file
        // 3. Cache
        
        // Example using database (if you have a settings table):
        // \App\Models\Setting::updateOrCreate(
        //     ['key' => $key],
        //     ['value' => $value]
        // );
        
        // For now, we'll just store in cache for demo
        Cache::put('setting.' . $key, $value);
    }
}