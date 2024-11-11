<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Role
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, ...$roles)
    {
        // Define role groups
        $roleGroups = [
            'dekanat' => ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'],
            'prodi' => ['kaprodi', 'sekprodi', 'admin_prodi'],
            'dosen' => ['dosen'],
            'mahasiswa' => ['mahasiswa'],
            // Add more role groups here
        ];

        // Flatten roles into a single array and handle role groups
        $roles = array_map('trim', explode(',', implode(',', $roles)));
        $roles = array_reduce($roles, function($carry, $role) use ($roleGroups) {
            if (array_key_exists($role, $roleGroups)) {
                return array_merge($carry, $roleGroups[$role]);
            }
            return array_merge($carry, [$role]);
        }, []);

        // Remove duplicates and trim roles
        $roles = array_unique(array_map('trim', $roles));

        // Check if user has any of the roles
        if ($request->user() && $request->user()->roles()->whereIn('nama', $roles)->exists()) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}
