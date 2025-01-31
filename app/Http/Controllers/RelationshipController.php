<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\Relationship;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class RelationshipController extends Controller
{
    public function dataTable()
    {
        $relationships = QueryBuilder::for(
            Relationship::orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($relationships);
    }
}
