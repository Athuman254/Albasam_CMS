<?php

namespace App\Http\Controllers\Settings;

use App\Models\Deduction;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class DeductionController extends Controller
{
    public function dataTable()
    {
        $statuses = QueryBuilder::for(
            Deduction::orderBy('id')
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
         'is_active' => 'required'
      ]);

      Deduction::create([
         'name' => $request->name,
         'is_active' => $request->is_active
      ]);
      return back(303)->with('success', 'Deduction  created.');
    }

    public function update(Request $request, Deduction $deduction){
       $request->validate([
         'name'=> 'required|string',
         'is_active' => 'required'
      ]);
      $deduction->update([
         'name'=> $request->name,
         'is_active' => $request->is_active
      ]);

       return back(303)->with('success', 'Deduction details updated.');
    }
}
