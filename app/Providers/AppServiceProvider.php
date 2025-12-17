<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Student;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share bookings count with all views
        View::composer('layouts.app', function ($view) {
            if (auth()->check() && auth()->user()->role === 'owner') {
                $newBookingsCount = Student::where('status', 'booked')->count();
                $view->with('newBookingsCount', $newBookingsCount);
            }
        });
        
        // Helper function for cPanel storage paths
        if (!function_exists('storage_asset')) {
            function storage_asset($path) {
                // Check if path starts with storage/
                if (strpos($path, 'storage/') === 0) {
                    $path = substr($path, 8); // Remove 'storage/' prefix
                }
                
                // For cPanel, use absolute path
                $storagePath = '/storage/' . ltrim($path, '/');
                
                // Use asset() helper which handles APP_URL automatically
                return asset($storagePath);
            }
        }
    }
}
