<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\WorkHistory;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class WorkHistoryController extends Controller
{
    public function dataTable()
    {
        $histories = QueryBuilder::for(
            WorkHistory::orderBy('id')
        )->allowedFilters([
            AllowedFilter::partial('institution_name'),
            AllowedFilter::exact('employee_id'),
        ])->jsonPaginate();

        return Resource::collection($histories);
    }
}
