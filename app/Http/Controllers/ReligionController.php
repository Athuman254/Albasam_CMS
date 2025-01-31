<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\Religion;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ReligionController extends Controller
{
    public function dataTable()
    {
        $religions = QueryBuilder::for(
            Religion::orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($religions);
    }
}
