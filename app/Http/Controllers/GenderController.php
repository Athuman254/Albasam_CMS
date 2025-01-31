<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\Gender;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class GenderController extends Controller
{
    public function dataTable()
    {
        $genders = QueryBuilder::for(
            Gender::orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($genders);
    }
}
