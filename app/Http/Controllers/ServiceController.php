<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Service;
use App\Models\BlogCategory;
use App\Http\Resources\Resource;
use Intervention\Image\ImageManager;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;

class ServiceController extends Controller
{


    public function services(){
        // $services = Service::all();
        $categories = BlogCategory::all();
        $services = Service::latest()->filter(request(['category','search']))->with('category')->Paginate(10);
        return view('website.services',[
            'services'=> $services,
            'categories'=> $categories,
            'title' => 'Services'
        ]);
    }

    public function single_service($slug){
        $service = Service::whereSlug($slug)->first();
        $related_services = Service::where('category_id',$service->category_id)
        ->whereNot('id',$service->id)
        ->orderBy('created_at','desc')
        ->limit(3)
        ->get();
        if(!$service){
            abort(404);
        }
        return view('website.service',[
            'service'=>$service,
            'title' =>$service->title,
            'related_services' => $related_services
        ]);
    }

    public function datatable(){
        $services = QueryBuilder::for(
            Service::with('category')->orderBy('created_at')
        )->allowedFilters([
            AllowedFilter::partial('title'),
        ])->jsonPaginate();


        return Resource::collection($services);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Website/ServicesPage');
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
    public function store(StoreServiceRequest $request)
    {
        // dd($request->validated());
        $validated = $request->validated();

        if($request->hasFile('featured_image')){
            $imgPath = $request->file('featured_image')->store('services', 'public');
            $tempPath = $request->file('featured_image')->store('services/t', 'public');

            // Create image instances
            $image = ImageManager::imagick()->read("storage/{$imgPath}");

            $image->resize(771, 423);
            $image->save("storage/{$imgPath}");
        }
        $slug = BlogController::createSlug($validated["title"]);

        $service = Service::create([
            'title'=> $validated['title'],
            'discription'=> $validated['discription'],
            'slug' => $slug,
            'content' => $validated['content'],
            'featured_image' => $imgPath,
            'temp_image'=> null,
            'service_includes'=> $validated['service_includes'],
            'price'=> $validated['price'],
            'vedio_src'=> $validated['vedio_src'],
            'category_id'=> $validated['category_id'],
            'status'=> $validated['status'],
            'author_id' => auth()->user()->id,
            'has_started'=> $validated['has_started'],
            'starts_at'=> $validated['starts_at'],
        ]);
        if ($request->hasFile('featured_image')) {
            $temp = ImageManager::imagick()->read("storage/{$tempPath}");
            $temp->resize(370, 450);
            $temp->save("storage/{$tempPath}");
            $service->update([
                'temp_image' => $tempPath,
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServiceRequest $request, Service $service)
    {
        $service->update($request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        $service->delete();
    }
}
