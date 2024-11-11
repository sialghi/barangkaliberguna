<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
        Gate::define('show-exclusive-menu-admin', function (User $user) {
            $allowedRoles = ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'wadek_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi'];
            return $user->roles()->whereIn('nama', $allowedRoles)->exists();
        });

        Gate::define('show-exclusive-menu-dosen', function (User $user) {
            $allowedRoles = ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'wadek_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi', 'dosen'];
            return $user->roles()->whereIn('nama', $allowedRoles)->exists();
        });

        Gate::define('show-exclusive-menu-mahasiswa', function (User $user) {
            $allowedRoles = ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'wadek_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi', 'mahasiswa'];
            return $user->roles()->whereIn('nama', $allowedRoles)->exists();
        });
    }
}
