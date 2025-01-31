<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\Qualification;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class QualificationController extends Controller
{
    public function dataTable()
    {
        $qualifications = QueryBuilder::for(
            Qualification::orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('employee_id'),
        ])->jsonPaginate();

        return Resource::collection($qualifications);
    }
}
