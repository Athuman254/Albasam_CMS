<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\Employee;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmployeeController extends Controller
{
    public function dataTable()
    {
        $employees = QueryBuilder::for(
            Employee::orderBy('id')
        )->allowedFilters([
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($employees);
    }
}
