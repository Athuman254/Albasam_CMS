<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\MaritalStatus;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class MaritalStatusController extends Controller
{
    public function dataTable()
    {
        $maritalStatuses = QueryBuilder::for(
            MaritalStatus::orderBy('name')
        )->allowedFilters([
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($maritalStatuses);
    }
}
