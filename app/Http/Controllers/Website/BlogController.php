<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\BlogRequest;
use App\Http\Resources\Resource;
use App\Models\Website\Blog;
use App\Services\HtmlPurifierService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BlogController extends Controller
{
    public function datatable()
    {
        $blogs = QueryBuilder::for(
            Blog::with('user', 'category', 'media')->orderBy('title')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('active'),
            AllowedFilter::partial('title'),
        ])->jsonPaginate();
        
        return Resource::collection($blogs);
    }
    
    public function store(BlogRequest $request, HtmlPurifierService $purifier)
    {
        $user = auth()->user();
        $validated = $request->validated();
        
        $validated['slug'] = Str::slug($validated['title']);
        $validated['details'] = $purifier->purify($validated['details']);
        
        if($validated['blog_category_id'] === '') {
            unset($validated['blog_category_id']);
        }
        
        $blog = Blog::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'blog_category_id' => $validated['blog_category_id'] ?? null,
            'details' => $validated['details'],
            'active' => $validated['active'],
        ]);
        
        if($request->hasFile('media')) {
            $blog->addMedia($validated['media'])
                ->toMediaCollection('blog-image');
        }
        
        return back(303);
    }
    
    public function update(BlogRequest $request, Blog $blog)
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['slug']);
        
        $blog->update([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'details' => $validated['details'],
            'active' => $validated['active'],
        ]);
        
        return back(303);
    }
    
    public function destroy($blogId)
    {
        $blog = Blog::findOrFail($blogId);
        $blog->delete();
        
        return redirect()->route('admin.components.index');
    }
    
    public function show($blog)
    {
        $blog = Blog::where('slug', '=', $blog)->firstOrFail();
        $blog->load('user', 'category', 'media');
        $otherBlogs = Blog::where('id', '!=', $blog->id)->orderByDesc('created_at')->get();
        $previousBlog = Blog::where('created_at', '<', $blog->created_at)
            ->orderBy('created_at', 'desc')
            ->first();
        $nextBlog = Blog::where('created_at', '>', $blog->created_at)
            ->orderBy('created_at', 'asc')
            ->first();
        
        $seo = $blog->seoMeta;
        
        if($seo) {
            \App\Models\SeoMeta::applyMeta($seo);
        }
        
        return view('website.template-1.blog-details', [
            'blog' => $blog,
            'otherBlogs' => $otherBlogs,
            'previousBlog' => $previousBlog,
            'nextBlog' => $nextBlog,
        ]);
    }
}
