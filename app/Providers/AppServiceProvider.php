<?php

namespace App\Providers;

use App\Models\Report;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\View\View as ViewInstance;

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
        Gate::before(function (User $user): ?bool {
            return $user->hasRole('Super Admin') ? true : null;
        });

        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(5)->by(
                Str::transliterate(Str::lower($request->string('login')).'|'.$request->ip()),
            );
        });

        View::composer('layouts.app', function (ViewInstance $view): void {
            $user = auth()->user();
            $view->with('unreadCount', $user ? $user->unreadNotifications()->count() : 0);
            $view->with('pendingVerificationCount', $user && $user->can('verify reports') ? Report::query()->where('status', 'pending')->count() : 0);
        });
    }
}
