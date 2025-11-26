<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\Resource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    public function dataTable()
    {
        $users = QueryBuilder::for(
            User::with('branch', 'roles', 'permissions')->orderBy('name')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($users);
    }

    public function index(): \Inertia\Response
    {
        return Inertia::render('Admin/Users/Index');
    }

    public function store(UserRequest $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {

            $user = User::forceCreate([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'activated' => $validated['activated'] ?? false,
            ]);

            if (isset($validated['role_id'])) {

                $role = Role::with('permissions')->find($validated['role_id']);

                $user->syncRoles([$role]);

                if (!empty($role->permissions)) {
                    $user->syncPermissionsWithoutDetaching($role->permissions->toArray());
                }
            }

            return $user;

        });

        return back(303)->with('success', 'User created.');
    }

    public function show(User $user)
    {
        //
    }

    public function update(UserRequest $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($user, $validated) {

            $user->update([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'activated' => $validated['activated'] ?? false,
            ]);

            if (! $validated['password']) {

                unset($validated['password']);

            } else {

                $validated['password'] = Hash::make($validated['password']);
            }

            if (!$validated['role_id']) {
                
                unset($validated['role_id']);
                
            } else {

                $role = Role::with('permissions')->find($validated['role_id']);

                $user->syncRoles([$role]);

                if ($role->permissions) {
                    $user->syncPermissionsWithoutDetaching($role->permissions->toArray());
                }
            }
        });

        return back(303)->with('success', 'User updated.');
    }

    public function updatePermission(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $permissions = Permission::find($request->input('permissions'));

        $user->syncPermissions($permissions->pluck('id')->toArray());

        return back(303)->with('success', 'User permissions updated.');
    }
}
