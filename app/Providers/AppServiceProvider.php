<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;

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
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });
        Paginator::useBootstrapFive();

        View::composer('participants.*', function ($view) {
            $user = auth()->user();
            if (!$user) return;

            $view->with('canCreate', $user->can('create participants') || 
                $user->can('manage participants') || 
                $user->can('manage own participants'));

            // For single participant context (edit, show)
            $participant = $view->participant ?? null;
            if ($participant) {
                $view->with('hasEditPermission', $participant->canBeEditedBy($user));
                $view->with('hasDeletePermission', $participant->canBeDeletedBy($user));
            }
        });
    }
}
