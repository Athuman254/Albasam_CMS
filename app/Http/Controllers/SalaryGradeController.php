<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\SalaryGrade;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SalaryGradeController extends Controller
{
    public function dataTable()
    {
        $salaryGrades = QueryBuilder::for(
            SalaryGrade::orderBy('id')
        )->allowedFilters([
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($salaryGrades);
    }
}
