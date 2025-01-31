<?php

namespace App\Http\Controllers;

use Exception;
use Inertia\Inertia;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use App\Models\BlogPostCategory;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManager;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class BlogController extends Controller
{
    public function datatable(){
        $classes = QueryBuilder::for(
            BlogPost::with('category')->orderBy('created_at')
        )->allowedFilters([
            AllowedFilter::partial('title'),
        ])->jsonPaginate();


        return Resource::collection($classes);
    }

    public function blog_post(){
            $posts = BlogPost::latest()->filter(request(['category','search']))->with('category')->Paginate(10);
            $latest_blog = BlogPost::orderBy('created_at','desc')
            ->limit(3)
            ->get();
            $categories = BlogCategory::all();
            return view('website.blog',[
                'posts' => $posts,
                'title' => 'blog',
                'latest_post' => $latest_blog,
                'categories' => $categories
            ]);
    }
    public function single_blog(BlogPost $post){
        $related_blogs = BlogPost::where('category_id',$post->category_id)
        ->whereNot('id',$post->id)
        ->orderBy('created_at','desc')
        ->limit(3)
        ->get();
        $latest_blog = BlogPost::whereNot('id',$post->id)
        ->orderBy('created_at','desc')
        ->limit(3)
        ->get();
        $categories = BlogCategory::all();
        // dd($related_blogs);
        return view('website.blog-single',['blog_post'=>$post,
    'related_posts' => $related_blogs,
    'title' =>$post->title,
    'latest_post' => $latest_blog,
    'categories' => $categories
    ]);
    }
    public function categories_datatable(){
        $categories = QueryBuilder::for(
            BlogCategory::orderBy('name')
        )->allowedFilters([
            AllowedFilter::partial('name'),
        ])->jsonPaginate();


        return Resource::collection($categories);
    }

    public function index(Request $request){
        return Inertia::render('Website/BlogPage');
    }

    public static function createSlug($text) {

        $text = strtolower($text);

        $text = str_replace(
            ['à', 'á', 'ä', 'â', 'ã', 'å', 'ā', 'ē', 'é', 'ě', 'ë', 'è', 'ê', 'í', 'ì', 'ï', 'î', 'ō', 'ó', 'ö', 'ò', 'ô', 'õ', 'ú', 'ù', 'ü', 'û', 'ý', 'ÿ', 'ñ', 'ç'],
            ['a', 'a', 'a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'n', 'c'],
            $text
        );


        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        return $text;
    }
    public function store_category(Request $request){
        $vali = $request->validate([
            'name'=> 'required|unique:blog_categories,name|max:20|string',
        ]);
        $category = BlogCategory::create([
            'name'=> $request->name,
            'slug'=> $request->name,
            'description'=> null,
        ]);

        return response()->json($category);
    }

    public function store(Request $request){
        $validated = $request->validate([
            'title'=> 'required|max:50',
            'content' => 'required',
            'featured_image'=>'',
            'category_id' =>'required',
            'status' => 'string',
            'comment_status' => 'required'
        ]);

        if ($request->hasFile('featured_image')) {
            $imgPath = $request->file('featured_image')->store('blog-pictures', 'public');
            $tempPath = $request->file('featured_image')->store('blog-pictures/t', 'public');

            // Create image instances
            $image = ImageManager::imagick()->read("storage/{$imgPath}");

            $image->resize(771, 423);
            $image->save("storage/{$imgPath}");

        }

        DB::beginTransaction();
        try {

            $post = BlogPost::create([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'slug' => $this->createSlug($validated['title']),
                'category_id' => $validated['category_id'],
                'featured_image' => $imgPath ?? null,
                'temp_image' => $temp ?? null,
                'author_id' => auth()->user()->id ?? null,
                'status' => $validated['status'],
                'comment_status' => $validated['comment_status'] ? 'open' : 'closed',
                'published_at' => $validated['status'] == 'published' ? now() : null,
            ]);
            if ($request->hasFile('featured_image')) {
                $temp = ImageManager::imagick()->read("storage/{$tempPath}");
                $temp->resize(369, 221);
                $temp->save("storage/{$tempPath}");
                $post->update([
                    'temp_image' => $tempPath,
                ]);
            }

            DB::commit();
        }catch(Exception $e){
            DB::rollBack();
            // return response()->json(['message'=> $e->getMessage()],500);
        }
    }

    public function update(Request $request, BlogPost $post){
        $validated = $request->validate([
            'title'=> 'required|max:50',
            'content' => 'required',
            'featured_image'=>'',
            'category_id' =>'required',
            'status' => 'string',
            'comment_status' => 'required'
        ]);
        // dd($request);
        if ($request->hasFile('featured_image')) {
            $img = $request->file('featured_image')->store('blog-pictures', 'public');
            $image = ImageManager::imagick()->read("storage/${img}");
            $image->resize(369, 221);
            $image->save("storage/${img}");
        }
        $post->update([
            'title'=> $validated['title'],
            'content'=> $validated['content'],
            'slug'=> $this->createSlug($validated['title']),
            'featured_image' => $img ?? null,
            'category_id'=> $validated['category_id'],
            'status' => $validated['status'],
            'comment_status' => $validated['comment_status'] ? 'open' : 'closed',
            'published_at' => $validated['status'] == 'published' ? now() : null,
        ]);
        // return response()->json(['message'=> 'updated'],200);
    }

    public function destroy(BlogPost $post){
        $post->delete();
    }
}
