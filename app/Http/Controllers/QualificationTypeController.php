<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\QualificationType;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class QualificationTypeController extends Controller
{
    public function dataTable()
    {
        $types = QueryBuilder::for(
            QualificationType::orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($types);
    }
}
