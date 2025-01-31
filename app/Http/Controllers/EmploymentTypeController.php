<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\EmploymentType;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmploymentTypeController extends Controller
{
    public function dataTable()
    {
        $types = QueryBuilder::for(
            EmploymentType::orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($types);
    }
}
