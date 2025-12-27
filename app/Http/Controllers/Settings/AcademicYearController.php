<?php

namespace App\Http\Controllers\Settings;

use Inertia\Inertia;
use App\Models\Settings\AcademicYear;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class AcademicYearController extends Controller
{
   /**
    * Display the academic years management page
    */
   public function index()
   {
      return Inertia::render('Admin/Settings/AcademicYears');
   }

   /**
    * Get academic years for datatable
    */
   public function dataTable()
   {
      \Log::info('AcademicYearController::dataTable called');
      // Ensure academic years exist, auto-generate if needed
      AcademicYear::ensureYearsExist();

      $academicyears = QueryBuilder::for(
         AcademicYear::orderBy('start_date', 'desc')
      )->allowedFilters([
         AllowedFilter::exact('id'),
         AllowedFilter::exact('is_active'),
         AllowedFilter::partial('name'),
      ])->jsonPaginate();

      \Log::info('AcademicYearController found years: ' . $academicyears->count());

      return Resource::collection($academicyears);
   }

   /**
    * Store a newly created academic year
    */
   public function store(Request $request)
   {
      $validated = $request->validate([
         'name' => ['required', 'string', 'max:255', 'unique:academic_years,name'],
         'start_date' => ['required', 'date'],
         'end_date' => ['required', 'date', 'after:start_date'],
         'is_active' => ['boolean'],
      ]);

      DB::beginTransaction();
      try {
         // If this year is being set as active, deactivate all others
         if ($validated['is_active'] ?? false) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
         }

         $academicYear = AcademicYear::create($validated);

         DB::commit();

         return redirect()->back()->with('success', 'Academic year created successfully');
      } catch (\Exception $e) {
         DB::rollBack();
         return redirect()->back()->with('error', 'Failed to create academic year: ' . $e->getMessage());
      }
   }

   /**
    * Update the specified academic year
    */
   public function update(Request $request, $id)
   {
      $academicYear = AcademicYear::findOrFail($id);

      $validated = $request->validate([
         'name' => ['required', 'string', 'max:255', Rule::unique('academic_years', 'name')->ignore($id)],
         'start_date' => ['required', 'date'],
         'end_date' => ['required', 'date', 'after:start_date'],
         'is_active' => ['boolean'],
      ]);

      DB::beginTransaction();
      try {
         // If this year is being set as active, deactivate all others
         if ($validated['is_active'] ?? false) {
            AcademicYear::where('id', '!=', $id)
               ->where('is_active', true)
               ->update(['is_active' => false]);
         }

         $academicYear->update($validated);

         DB::commit();

         return redirect()->back()->with('success', 'Academic year updated successfully');
      } catch (\Exception $e) {
         DB::rollBack();
         return redirect()->back()->with('error', 'Failed to update academic year: ' . $e->getMessage());
      }
   }

   /**
    * Remove the specified academic year
    */
   public function destroy($id)
   {
      try {
         $academicYear = AcademicYear::findOrFail($id);

         // Prevent deletion of active academic year
         if ($academicYear->is_active) {
            return redirect()->back()->with('error', 'Cannot delete the active academic year');
         }

         $academicYear->delete();

         return redirect()->back()->with('success', 'Academic year deleted successfully');
      } catch (\Exception $e) {
         return redirect()->back()->with('error', 'Failed to delete academic year: ' . $e->getMessage());
      }
   }

   /**
    * Activate the specified academic year
    */
   public function activate($id)
   {
      DB::beginTransaction();
      try {
         // Deactivate all academic years
         AcademicYear::where('is_active', true)->update(['is_active' => false]);

         // Activate the selected year
         $academicYear = AcademicYear::findOrFail($id);
         $academicYear->update(['is_active' => true]);

         DB::commit();

         return redirect()->back()->with('success', 'Academic year activated successfully');
      } catch (\Exception $e) {
         DB::rollBack();
         return redirect()->back()->with('error', 'Failed to activate academic year: ' . $e->getMessage());
      }
   }
}
