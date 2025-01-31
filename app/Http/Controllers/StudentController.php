<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class StudentController extends Controller
{

    public function dataTable()
    {
        $students =  QueryBuilder::for(
            Student::with(['admission.rank'])->orderBy('id', 'desc')
        )->allowedFilters([
            AllowedFilter::partial('admission_number'),
            AllowedFilter::scope('classfilter'),
        ])->jsonPaginate();

        // dd($students);
        return Resource::collection($students);
    }
}
