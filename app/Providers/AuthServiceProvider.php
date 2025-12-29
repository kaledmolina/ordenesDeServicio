<?php
namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User; // <-- Añade este
use App\Policies\UserPolicy; // <-- Añade este

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Orden::class => OrdenPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {

            if ($user->email === 'kaledmoly@gmail.com') {
                return true;
            }
            if ($user->hasRole('administrador')) {
                return true;
            }

            return null; 
        });
    }
}
