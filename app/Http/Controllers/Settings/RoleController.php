<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Http\Resources\Resource;
use App\Models\Role;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class RoleController extends Controller
{
    public function dataTable()
    {
        $roles = QueryBuilder::for(
            Role::with('permissions')->orderBy('display_name')
        )->allowedFilters(
            AllowedFilter::partial('display_name'),
        )->jsonPaginate();

        return Resource::collection($roles);
    }

    public function index()
    {
        return Inertia::render('Admin/Roles/Index', []);
    }

    public function store(RoleRequest $request)
    {
        $validated = $request->validated();

        $name = strtolower(str_replace(' ', '-', $validated['name']));

        $role = Role::create([
            'name' => $name,
            'display_name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        $permissionIds = collect($validated['permissions']);

        $role->syncPermissions($permissionIds);

        return to_route('roles.index')->with('success', 'Role created.');
    }

    public function update(RoleRequest $request, Role $role)
    {
        $validated = $request->validated();

        $name = strtolower(str_replace(' ', '-', $validated['name']));

        $role->update([
            'name' => $name,
            'display_name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        $permissionIds = collect($validated['permissions']);

        $role->syncPermissions($permissionIds);

        return to_route('roles.index')->with('success', 'Role created.');
    }
}
