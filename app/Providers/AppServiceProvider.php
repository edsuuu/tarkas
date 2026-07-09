<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::define('viewLogViewer', fn (?User $user = null) => $user?->can('log-viewer.view') ?? false);
        Gate::define('downloadLogFile', fn (?User $user = null) => $user?->can('log-viewer.view') ?? false);
        Gate::define('downloadLogFolder', fn (?User $user = null) => $user?->can('log-viewer.view') ?? false);
        Gate::define('deleteLogFile', fn () => false);
        Gate::define('deleteLogFolder', fn () => false);
    }
}
