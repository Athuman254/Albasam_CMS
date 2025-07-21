<?php

namespace App\Http\Controllers\Settings;

use App\Models\Allowance;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class AllowanceController extends Controller
{
     public function dataTable()
    {
        $statuses = QueryBuilder::for(
            Allowance::orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($statuses);
    }

    public function store(Request $request){
      $request->validate([
         'name'=> 'required|string',
         'is_ahl_exempted' => 'required',
         'is_active' => 'required'
      ]);

      Allowance::create([
         'name' => $request->name,
         'is_ahl_exempted' => $request->is_ahl_exempted,
         'is_active' => $request->is_active
      ]);
      return back(303)->with('success', 'Allowance  created.');
    }

    public function update(Request $request, Allowance $allowance){
       $request->validate([
         'name'=> 'required|string',
         'is_ahl_exempted' => 'required',
         'is_active' => 'required'
      ]);
      $allowance->update([
         'name'=> $request->name,
         'is_ahl_exempted' => $request->is_ahl_exempted,
         'is_active' => $request->is_active
      ]);

       return back(303)->with('success', 'Employment status details updated.');
    }
}
