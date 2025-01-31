<?php

namespace App\Http\Controllers;

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
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($users);
    }

    public function index()
    {
        return Inertia::render('admin/Users/Index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|unique:users,phone',
            'role_id' => 'nullable|exists:roles,id',
            'password' => 'required',
            'activated' => 'boolean',
        ]);

        $user = DB::transaction(function () use ($validated) {

            $user = User::forceCreate([
                'name'                => $validated['name'],
                'username'            => $validated['username'],
                'email'               => $validated['email'],
                'phone'               => $validated['phone'],
//                'branch_id'           => $validated['branch_id'],
                'password'            => Hash::make($validated['password']),
                'activated'           => $validated['activated'] ?? false,
            ]);

            if ($validated['role_id']) {

                $role = Role::with('permissions')->find($validated['role_id']);

                $user->syncRoles([$role]);

                if (!empty($role->permissions)) {
                    $user->syncPermissionsWithoutDetaching($role->permissions->toArray());
                }
            }

            return $user;

        });

        return to_route('users.index')->with('success', 'User created.');
    }

    public function show(User $user)
    {
        //
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required',
            'username' => 'required',
            'email' => 'required|email',
            'phone' => 'nullable',
            'role_id' => 'nullable|exists:roles,id',
            'password' => 'nullable',
            'activated' => 'boolean',
        ]);

        DB::transaction(function () use ($user, $validated) {

            if (! $validated['password']) {

                unset($validated['password']);

            } else {

                $validated['password'] = Hash::make($validated['password']);
            }

            if (array_key_exists('role_id', $validated) && $validated['role_id'] !== null) {

                $role = Role::with('permissions')->find($validated['role_id']);

                $user->syncRoles([$role]);

                if ($role->permissions) {
                    $user->syncPermissionsWithoutDetaching($role->permissions->toArray());
                }

                unset($validated['role_id']);
            }

            unset($validated['role_id']);

            $user->forceFill($validated)->save();
        });

        return to_route('users.index')->with('success', 'User updated.');
    }

    public function updatePermission(Request $request, User $user)
    {
        $permissions = Permission::find($request->input('permissions'));

        $user->syncPermissions($permissions->pluck('id')->toArray());

        return to_route('users.index')->with('success', 'User permissions updated.');
    }
}
