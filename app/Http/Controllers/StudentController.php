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
            Student::with(['rank', 'gender', 'religion', 'guardians', 'siblings'])->orderBy('first_name')
        )->allowedFilters([
            AllowedFilter::exact('rank_id'),
            AllowedFilter::partial('admission_number'),
        ])->jsonPaginate();

        return Resource::collection($students);
    }
}
