<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\JobTitle;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class JobTitleController extends Controller
{
    public function dataTable()
    {
        $titles = QueryBuilder::for(
            JobTitle::orderBy('name')
        )->allowedFilters([
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($titles);
    }
}
