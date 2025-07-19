<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $shared = [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? array_merge(
                    $user->only(['id', 'name', 'email']),
                    [
                        'roles' => $user->getRoleNames(),
                        'permissions' => $user->getAllPermissions()->pluck('name'),
                        'hasManagementPermissions' => $user->can('manage users') || $user->can('manage roles'),
                    ]
                ) : null,
            ],
        ];

        // Carica i dati di gestione permessi solo se l'utente ha i permessi
        // e solo per le pagine che ne hanno bisogno
        if ($user && ($user->can('manage users') || $user->can('manage roles'))) {
            // Carica i dati solo per la pagina home o admin
            $currentRoute = $request->route();
            $routeName = $currentRoute ? $currentRoute->getName() : '';
            
            if (in_array($routeName, ['home', 'admin.users']) || $request->is('home')) {
                $shared['managementData'] = [
                    'users' => \App\Models\User::with('roles', 'permissions')->get(),
                    'roles' => \Spatie\Permission\Models\Role::all(),
                    'permissions' => \Spatie\Permission\Models\Permission::all(),
                ];
            }
        }

        return $shared;
    }
}
