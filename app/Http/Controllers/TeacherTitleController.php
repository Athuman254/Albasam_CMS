<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\TeacherTitle;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TeacherTitleController extends Controller
{
    public function dataTable()
    {
        $titles = QueryBuilder::for(
            TeacherTitle::orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($titles);
    }
}
