<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SpecializationController extends Controller
{
    public function dataTable()
    {
        $specializations = QueryBuilder::for(
            Specialization::orderBy('id', 'desc')
        )->allowedFilters([
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($specializations);
    }
}
