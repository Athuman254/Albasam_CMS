<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\SalaryScale;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SalaryScaleController extends Controller
{
    public function dataTable()
    {
        $scales = QueryBuilder::for(
            SalaryScale::orderBy('id')
        )->allowedFilters([
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($scales);
    }
}
