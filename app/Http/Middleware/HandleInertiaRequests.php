<?php

namespace App\Http\Middleware;

use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                $user = Auth::guard('web')->user();

                return $user
                    ? [
                        'id' => $user->hashid,
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'username' => $user->username,
                        'roles' => $user->roles->pluck('name')->toArray(),
                        'permissions' => $user->allPermissions()->pluck('name')->toArray(),
                    ]
                    : null;
            },

            'auth.employee' => function () use ($request) {
                $employee = Auth::guard('employee')->user();

                return $employee
                    ? [
                        'id' => $employee->hashid,
                        'employee_id' => $employee->id,
                        'staff_number' => $employee->staff_number ?? '',
                        'name' => $employee->first_name . ' ' . $employee->last_name,
                        'email' => $employee->email,
                        'phone' => $employee->primary_phone,
                        'teacher' => $employee->teacher ?? null,
                    ]
                    : null;
            },

            'auth.student' => function () use ($request) {
                $student = Auth::guard('student')->user();

                return $student
                    ? [
                        'id' => $student->hashid,
                        'student_id' => $student->id,
                        'admission_number' => $student->admission_number,
                        'name' => $student->first_name . ' ' . $student->last_name,
                        'email' => $student->email, // If student has email
                        'photo_url' => $student->photo_url,
                    ]
                    : null;
            },
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
                'warning' => fn() => $request->session()->get('warning'),
                'info' => fn() => $request->session()->get('info'),
            ],
        ]);
    }
}
