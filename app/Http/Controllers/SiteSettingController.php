<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSiteSettingRequest;
use App\Http\Requests\UpdateSiteSettingRequest;
use App\Models\SiteSetting;
use Inertia\Inertia;

class SiteSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $siteSetting = SiteSetting::first() ?? new SiteSetting();
        return Inertia::render("Website/SiteSettings",[
            "site_settings"=> $siteSetting
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSiteSettingRequest $request)
    {
        $validated =$request->validated();
        // dd($validated);
        $settings = SiteSetting::updateOrCreate(['id'=>$validated['id']],$validated);
        $settings->fill($request->all());
        $settings->save();

    }

    /**
     * Display the specified resource.
     */
    public function show(SiteSetting $siteSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SiteSetting $siteSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSiteSettingRequest $request, SiteSetting $siteSetting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SiteSetting $siteSetting)
    {
        //
    }
}
