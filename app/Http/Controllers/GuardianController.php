<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\Guardian;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class GuardianController extends Controller
{
    public function dataTable()
    {
        $guardians = QueryBuilder::for(
            Guardian::with('relationship')
        )->allowedFilters([
            AllowedFilter::exact('student_id'),
        ])->jsonPaginate();

        return Resource::collection($guardians);
    }

    public function index()
    {
        return Inertia::render('Admin/Guardians/Index', []);
    }
}
