<?php

namespace App\Http\Controllers\Settings;

use App\Models\Settings\AcademicYear;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class AcademicYearController extends Controller
{
   public function dataTable()
   {
      // Ensure academic years exist, auto-generate if needed
      AcademicYear::ensureYearsExist();

      $academicyears = QueryBuilder::for(
         AcademicYear::orderBy('id')
      )->allowedFilters([
         AllowedFilter::exact('id'),
         AllowedFilter::exact('is_active'),
         AllowedFilter::partial('name'),
      ])->jsonPaginate();

      return Resource::collection($academicyears);
   }
}
