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
    }
}
