<?php

namespace App\Providers;

use App\Domains\Auth\Models\User;
use App\Domains\Product\Models\Product;
use App\Domains\Product\Models\ProductRelease;
use App\Domains\Product\Observers\ProductObserver;
use App\Domains\Product\Observers\ProductReleaseObserver;
use App\Domains\Product\Policies\ProductPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Product::class => ProductPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
        $this->registerObservers();
        $this->registerGates();
    }

    private function registerObservers(): void
    {
        Product::observe(ProductObserver::class);
        ProductRelease::observe(ProductReleaseObserver::class);
    }

    private function registerGates(): void
    {
        // Super admin bypasses ALL gates
        Gate::before(fn (User $user, string $ability) =>
            $user->hasPermission('*') ? true : null
        );

        Gate::define('manage_products',         fn (User $u) => $u->hasPermission('manage_products'));
        Gate::define('manage_documentation',    fn (User $u) => $u->hasPermission('manage_documentation'));
        Gate::define('manage_roadmap',          fn (User $u) => $u->hasPermission('manage_roadmap'));
        Gate::define('manage_updates',          fn (User $u) => $u->hasPermission('manage_updates'));
        Gate::define('manage_careers',          fn (User $u) => $u->hasPermission('manage_careers'));
        Gate::define('manage_contact_requests', fn (User $u) => $u->hasPermission('manage_contact_requests'));
        Gate::define('manage_media',            fn (User $u) => $u->hasPermission('manage_media'));
        Gate::define('manage_users',            fn (User $u) => $u->hasPermission('manage_users'));
        Gate::define('manage_settings',         fn (User $u) => $u->hasPermission('manage_settings'));
        Gate::define('view_audit_logs',         fn (User $u) => $u->hasPermission('view_audit_logs'));
        Gate::define('view_products',           fn (User $u) => $u->hasAnyPermission(['manage_products', 'view_products']));
    }
}
