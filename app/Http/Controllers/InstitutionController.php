<?php

namespace App\Http\Controllers;

use App\Http\Requests\InstitutionRequest;
use App\Http\Resources\Resource;
use App\Models\Division;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Institution;
use Illuminate\Http\Request;

class InstitutionController extends Controller
{
    public function dataTable()
    {
        return Institution::orderBy('id')->first();

//        return response()->json($institution);
    }

    public function index()
    {
        $institution = Institution::first() ?: (object) [];
        $divisions = Division::activated()->orderBy('name')->get();

        return Inertia::render('admin/Institutions/Index', [
            'institution' => $institution,
            'divisions' => $divisions,
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/Institutions/Create');
    }

    public function store(InstitutionRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            Institution::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'country' => $validated['country'],
                'state' => $validated['state'],
                'city' => $validated['city'],
                'physical_address' => $validated['physical_address'],
                'postal_address' => $validated['postal_address'],
                'tax_identification_pin' => $validated['tax_identification_pin'],
                'mission' => $validated['mission'],
                'vision' => $validated['vision'],
                'x_profile' => $validated['x_profile'],
                'fb_profile' => $validated['fb_profile'],
                'ig_profile' => $validated['ig_profile'],
                'tiktok_profile' => $validated['tiktok_profile'],
                'youtube_profile' => $validated['youtube_profile'],
            ]);

            DB::commit();
            return to_route('institutions.index')->with('success', 'Institution created successfully.');

        } catch(\Exception $exception) {
            DB::rollBack();

            report($exception);

            abort(500, $exception->getMessage());
        }
    }

    public function update(Institution $institution, InstitutionRequest $request)
    {
        $validated = $request->validated();

        $institution->update($validated);

        return to_route('institutions.index')->with('success', 'Institution updated successfully.');
//        return response()->noContent();
    }
}
