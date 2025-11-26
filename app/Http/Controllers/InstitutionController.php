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
        $institution = Institution::orderBy('id')->first();
        $institution->load('media');
        
        return response()->json($institution);
    }

    public function index()
    {
        return Inertia::render('Admin/Institutions/Index');
    }

    public function create()
    {
        return Inertia::render('Admin/Institutions/Create');
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
            return to_route('admin.institutions.index')->with('success', 'Institution created successfully.');

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

        return to_route('admin.institutions.index')->with('success', 'Institution updated successfully.');
//        return response()->noContent();
    }

    public function uploadMedia(Request $request)
    {
        $validated = $request->validate([
            'institution_id' => 'required',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        $institution = Institution::findOrFail($validated['institution_id']);

        if($request->hasFile('logo')) {
            $institution->clearMediaCollection('logo');
            $institution->addMedia($validated['logo'])
                ->toMediaCollection('logo');
        }

        if($request->hasFile('favicon')) {
            $institution->clearMediaCollection('favicon');
            $institution->addMedia($validated['favicon'])
                ->toMediaCollection('favicon');
        }

        return to_route('admin.institutions.index')->with('success', 'Institution media uploaded successfully.');
    }
}
