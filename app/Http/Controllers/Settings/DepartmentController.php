<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\Department;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DepartmentController extends Controller
{
    public function dataTable()
    {
        $departments = QueryBuilder::for(
            Department::orderBy('name')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($departments);
    }
}
