<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PermissionController extends Controller
{
    public function dataTable()
    {
//        $permissions = QueryBuilder::for(
//            Permission::orderBy('display_name')
//        )->allowedFilters(
//            AllowedFilter::partial('display_name')
//        )->jsonPaginate();
        
        $permissions = Permission::all();

        return Resource::collection($permissions);
    }

    public function index()
    {
        return Inertia::render('Admin/Permissions/Index', []);
    }

    public function store(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => ['required'],
            'code' => ['required'],
            'description' => ['nullable'],
        ]);

        $name = strtolower(str_replace(' ', '-', $validated['name']));

        Permission::create([
            'name' => $name,
            'code' => $validated['code'],
            'display_name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => ['required'],
            'code' => ['required'],
            'description' => ['nullable'],
        ]);

        $name = strtolower(str_replace(' ', '-', $validated['name']));

        $permission->update([
            'name' => $name,
            'code' => $validated['code'],
            'display_name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        return redirect()->back();
    }
}
