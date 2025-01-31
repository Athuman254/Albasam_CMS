<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\Honorific;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class HonorificController extends Controller
{
    public function dataTable()
    {
        $honorifics = QueryBuilder::for(
            Honorific::orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($honorifics);
    }
}
