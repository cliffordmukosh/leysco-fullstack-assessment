<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // manage_inventory - Managers only
        Gate::define('manage_inventory', function (User $user) {
            return in_array('manage_inventory', $user->permissions ?? [], true);
        });

        // view_inventory - Reps + Managers
        Gate::define('view_inventory', function (User $user) {
            return in_array('view_inventory', $user->permissions ?? [], true)
                || in_array('manage_inventory', $user->permissions ?? [], true);
        });

        // view_customers - Reps + Managers
        Gate::define('view_customers', function (User $user) {
            return in_array('view_customers', $user->permissions ?? [], true)
                || $user->role === 'Sales Manager'
                || $user->role === 'Sales Representative';
        });

        // manage_customers - Managers only
        Gate::define('manage_customers', function (User $user) {
            return in_array('manage_customers', $user->permissions ?? [], true)
                || $user->role === 'Sales Manager';
        });

        // Orders-specific Gates
        Gate::define('view_orders', function (User $user) {
            return in_array('view_all_sales', $user->permissions ?? [])
                || in_array('view_own_sales', $user->permissions ?? []);
        });

        Gate::define('create_orders', function (User $user) {
            return in_array('create_sales', $user->permissions ?? []);
        });

        Gate::define('update_order_status', function (User $user) {
            return in_array('approve_sales', $user->permissions ?? [])
                || $user->role === 'Sales Manager';
        });
    }
}