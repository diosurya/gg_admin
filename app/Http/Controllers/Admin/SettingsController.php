<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        // Get all settings from database
        $settings = DB::table('settings')->get()->keyBy('key');
        
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'site_name' => 'nullable|string|max:100',
            'site_description' => 'nullable|string|max:255',
            'site_logo' => 'nullable|image|max:2048',
            'site_favicon' => 'nullable|image|max:1024',
            'admin_email' => 'nullable|email|max:100',
            'default_currency' => 'nullable|string|max:3',
            'default_language' => 'nullable|string|max:5',
            'default_timezone' => 'nullable|string|max:50',
            'maintenance_mode' => 'nullable|boolean',
            'registration_enabled' => 'nullable|boolean',
            'email_verification' => 'nullable|boolean',
            'two_factor_auth' => 'nullable|boolean',
            'max_upload_size' => 'nullable|integer|min:1|max:100',
            'allowed_file_types' => 'nullable|string',
            'products_per_page' => 'nullable|integer|min:5|max:100',
            'blogs_per_page' => 'nullable|integer|min:5|max:100',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|in:tls,ssl',
            'google_analytics_id' => 'nullable|string|max:50',
            'facebook_pixel_id' => 'nullable|string|max:50',
            'backup_frequency' => 'nullable|in:daily,weekly,monthly',
            'backup_retention_days' => 'nullable|integer|min:1|max:365'
        ]);

        try {
            $settingsToUpdate = [];
            
            // Handle file uploads
            if ($request->hasFile('site_logo')) {
                $logo = $request->file('site_logo');
                $logoPath = $logo->store('settings', 'public');
                $settingsToUpdate['site_logo'] = $logoPath;
            }

            if ($request->hasFile('site_favicon')) {
                $favicon = $request->file('site_favicon');
                $faviconPath = $favicon->store('settings', 'public');
                $settingsToUpdate['site_favicon'] = $faviconPath;
            }

            // Add other settings
            $regularSettings = [
                'site_name', 'site_description', 'admin_email', 'default_currency',
                'default_language', 'default_timezone', 'max_upload_size', 'allowed_file_types',
                'products_per_page', 'blogs_per_page', 'smtp_host', 'smtp_port',
                'smtp_username', 'smtp_password', 'smtp_encryption', 'google_analytics_id',
                'facebook_pixel_id', 'backup_frequency', 'backup_retention_days'
            ];

            foreach ($regularSettings as $setting) {
                if ($request->filled($setting)) {
                    $settingsToUpdate[$setting] = $request->$setting;
                }
            }

            // Handle boolean settings
            $booleanSettings = [
                'maintenance_mode', 'registration_enabled', 'email_verification', 'two_factor_auth'
            ];

            foreach ($booleanSettings as $setting) {
                $settingsToUpdate[$setting] = $request->has($setting) ? '1' : '0';
            }

            // Update or insert settings
            foreach ($settingsToUpdate as $key => $value) {
                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    [
                        'value' => $value,
                        'updated_at' => now()
                    ]
                );
            }

            // Clear cache after updating settings
            Cache::tags(['settings'])->flush();

            return redirect()->back()->with('success', 'Settings updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating settings: ' . $e->getMessage());
        }
    }

    /**
     * Display cache management page
     */
    public function cache()
    {
        // Get cache statistics
        $cacheStats = [
            'application_cache' => Cache::many(['products', 'categories', 'blogs']),
            'database_queries' => DB::table('cache')->count(),
            'storage_files' => count(Storage::allFiles('cache')),
            'total_size' => $this->getCacheSize()
        ];

        return view('admin.settings.cache', compact('cacheStats'));
    }

    /**
     * Clear cache
     */
    public function clearCache(Request $request)
    {
        $request->validate([
            'cache_type' => 'required|in:all,application,database,views,routes,config'
        ]);

        try {
            switch ($request->cache_type) {
                case 'all':
                    Cache::flush();
                    $this->clearFileCache();
                    $message = 'All caches cleared successfully';
                    break;

                case 'application':
                    Cache::tags(['products', 'categories', 'blogs', 'users', 'stores'])->flush();
                    $message = 'Application cache cleared successfully';
                    break;

                case 'database':
                    DB::table('cache')->truncate();
                    $message = 'Database cache cleared successfully';
                    break;

                case 'views':
                    $this->clearViewCache();
                    $message = 'View cache cleared successfully';
                    break;

                case 'routes':
                    $this->clearRouteCache();
                    $message = 'Route cache cleared successfully';
                    break;

                case 'config':
                    $this->clearConfigCache();
                    $message = 'Config cache cleared successfully';
                    break;

                default:
                    $message = 'Invalid cache type';
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error clearing cache: ' . $e->getMessage());
        }
    }

    /**
     * Get total cache size
     */
    private function getCacheSize()
    {
        $totalSize = 0;
        
        try {
            // Get file cache size
            $files = Storage::allFiles('cache');
            foreach ($files as $file) {
                $totalSize += Storage::size($file);
            }
            
            // Get database cache size (approximate)
            $dbCacheCount = DB::table('cache')->count();
            $totalSize += $dbCacheCount * 1024; // Rough estimate
            
        } catch (\Exception $e) {
            // If error, return 0
        }

        return $this->formatBytes($totalSize);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Clear file cache
     */
    private function clearFileCache()
    {
        try {
            Storage::deleteDirectory('cache');
            Storage::makeDirectory('cache');
        } catch (\Exception $e) {
            // Handle silently
        }
    }

    /**
     * Clear view cache
     */
    private function clearViewCache()
    {
        try {
            Storage::deleteDirectory('views');
            Storage::makeDirectory('views');
        } catch (\Exception $e) {
            // Handle silently
        }
    }

    /**
     * Clear route cache
     */
    private function clearRouteCache()
    {
        try {
            if (file_exists(storage_path('app/routes.php'))) {
                unlink(storage_path('app/routes.php'));
            }
        } catch (\Exception $e) {
            // Handle silently
        }
    }

    /**
     * Clear config cache
     */
    private function clearConfigCache()
    {
        try {
            if (file_exists(storage_path('app/config.php'))) {
                unlink(storage_path('app/config.php'));
            }
        } catch (\Exception $e) {
            // Handle silently
        }
    }

    /**
     * Create settings table if not exists
     */
    private function createSettingsTable()
    {
        if (!DB::getSchemaBuilder()->hasTable('settings')) {
            DB::statement("
                CREATE TABLE settings (
                    id CHAR(36) NOT NULL PRIMARY KEY,
                    `key` VARCHAR(100) NOT NULL,
                    `value` TEXT,
                    `description` TEXT,
                    `type` ENUM('string', 'number', 'boolean', 'json', 'file') DEFAULT 'string',
                    `group` VARCHAR(50) DEFAULT 'general',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_settings_key (`key`)
                )
            ");
        }
    }
}