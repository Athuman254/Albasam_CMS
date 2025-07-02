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
            Employee::with(['employment_type', 'employment_status', 'honorific', 'marital_status', 'gender', 'religion', 'teacher'])
                ->orderBy('first_name')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::scope('search', 'Search'),
        ])->jsonPaginate();

        return Resource::collection($employees);
    }
}
