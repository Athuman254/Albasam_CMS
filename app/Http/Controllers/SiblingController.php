<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\Sibling;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SiblingController extends Controller
{
    public function dataTable()
    {
        $siblings = QueryBuilder::for(
            Sibling::with('gender')
        )->allowedFilters([
            AllowedFilter::exact('student_id'),
        ])->jsonPaginate();

        return Resource::collection($siblings);
    }
}
