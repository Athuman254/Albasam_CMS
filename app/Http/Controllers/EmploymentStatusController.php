<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\EmploymentStatus;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmploymentStatusController extends Controller
{
    public function dataTable()
    {
        $statuses = QueryBuilder::for(
            EmploymentStatus::orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($statuses);
    }
}
