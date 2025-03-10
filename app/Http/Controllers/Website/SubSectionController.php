<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\SubSection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SubSectionController extends Controller
{
    public function dataTable()
    {
        $pages = QueryBuilder::for(
            SubSection::with('section')->orderBy('order')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('section_id'),
            AllowedFilter::partial('title'),
        ])->jsonPaginate();

        return Resource::collection($pages);
    }
}
