<?php

namespace App\Http\Controllers\Settings;

use App\Models\Income;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class IncomeController extends Controller
{

   public function dataTable()
   {
      $incomes = QueryBuilder::for(
         Income::orderBy('id')
      )->allowedFilters([
         AllowedFilter::exact('id'),
         AllowedFilter::exact('activated'),
         AllowedFilter::partial('name'),
      ])->jsonPaginate();

      return Resource::collection($incomes);
   }
   public function store(Request $request)
   {
      $request->validate([
         'name' => 'required|string',
         'is_active' => 'required'
      ]);

      Income::create([
         'name' => $request->name,
         'is_active' => $request->is_active
      ]);
      return back(303)->with('success', 'Income  created.');
   }

   public function update(Request $request, Income $income)
   {
      $request->validate([
         'name' => 'required|string',
         'is_active' => 'required'
      ]);
      $income->update([
         'name' => $request->name,
         'is_active' => $request->is_active
      ]);

      return back(303)->with('success', 'Income details updated.');
   }
}
