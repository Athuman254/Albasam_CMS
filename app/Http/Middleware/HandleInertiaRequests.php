<?php

namespace App\Http\Middleware;

use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
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
        return array_merge(parent::share($request), [
            'logoUrl' => function () {
                $institution = \App\Models\Institution::firstOrFail() ?? null;
                
                return $institution->hasMedia('logo') ? $institution->getMedia('logo')->sortByDesc('created_at')->first()->getUrl() : null;
            },
            'faviconUrl' => function () {
                $institution = \App\Models\Institution::firstOrFail() ?? null;
                
                return $institution->hasMedia('favicon') ? $institution->getMedia('favicon')->sortByDesc('created_at')->first()->getUrl() : null;
            },
            'institution' => function () {
                $institution = Institution::orderBy('id')->first() ?? new Institution();
                return $institution->exists() ? $institution : null;
            },
            'auth.user' => function () use ($request) {
                $user = $request->user();
                $loggedInAs = Session::get('logged_in_as');

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
                        'is_admin' => $user->is_admin,
                        'is_teacher' => $user->is_teacher,
                        'is_parent' => $user->is_parent,
                        'logged_in_as' => Session::get('logged_in_as'),
                    ]
                    : null;
            },
        ]);
    }
}
