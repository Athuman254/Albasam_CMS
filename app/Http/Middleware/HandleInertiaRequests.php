<?php

namespace App\Http\Middleware;

use App\Models\Institution;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'institution' => function () {
                $institution = Institution::orderBy('id')->first() ?? new Institution();
                return $institution->exists() ? $institution : null;
            },
            'auth.user' => function () use ($request) {
                $user = $request->user();

                return $user
                    ? [
                        'id' => $user->hashid,
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'username' => $user->username,
                        'roles' => $user->roles->pluck('name'),
                        'permissions' => $user->permissions->pluck('name'),
                    ]
                    : null;
            },
        ]);
    }
}
