<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\Department;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DepartmentController extends Controller
{
    public function dataTable()
    {
        $departments = QueryBuilder::for(
            Department::orderBy('name')
        )->allowedFilters([
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($departments);
    }
}
