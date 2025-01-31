<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use App\Models\About;
use App\Models\Quote;
use App\Models\Slide;
use App\Models\BlogPost;
use App\Models\QuickLink;
use App\Models\Institution;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Models\WhyChooseUs;
use Illuminate\Http\Request;
use App\Models\UpcomingEvent;
use Intervention\Image\ImageManager;

class WebsiteController extends Controller
{
    public function index(): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application
    {
        //select template chose from the db
        $institution = Institution::first();
        // dd($institution);
        $home_sliders = Slide::all();
        $quotes = Quote::all();
        $about = About::find(1);
        $testimonials = Testimonial::all();
        $why_choose_us = WhyChooseUs::all();
        $events = UpcomingEvent::all();
        $services = Service::limit(6)->get();
        $quick_links = QuickLink::orderBy('created_at', 'desc')->get();
        $blog_posts = BlogPost::with('category')->orderBy('created_at','desc')
        ->limit(10)
        ->get();

        $web_setting = SiteSetting::first();

        if($web_setting->template_name == '0') {
            $template = 'website.template1.index';
        }elseif($web_setting->template_name == '1'){
            $template = 'website.template2.index';
        }elseif($web_setting->template_name == '2'){
            $template = 'website.template3.index';
        }else{
            $template = 'website.template1.index';
        }
        // dd($web_setting);
        return view($template,[
            'home_sliders' => $home_sliders,
            'quotes' => $quotes,
            'about' => $about,
            'testimonials' => $testimonials,
            'why_choose_us' => $why_choose_us,
            'institution' => $institution,
            'events' =>$events,
            'services' => $services,
            'blog_posts' => $blog_posts,
            'web_setting' =>$web_setting,
            'quick_link' => $quick_links[0] ?? null
        ]);
    }
     public function about(): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application
    {
        /**
         * requiments
         * no of students
         * no of teacher
         */

        $about = About::find(1);

        return view('website.about',[
            'about' => $about,
            'title' => 'About Us'
        ]);
    }


    //Dashboard website routes
    public function pages(): \Inertia\Response
    {
        return Inertia::render('admin/Website/Pages');
    }

    public function homepage()
    {
        $slides = Slide::all();
        $quotes = Quote::all();
        $why_choose_us = WhyChooseUs::all();
        $testimonials = Testimonial::all();
        $events = UpcomingEvent::all();
        $quick_links = QuickLink::all();
        $about = About::find(1);
        // dd($about);
        return Inertia::render('admin/Website/Homepage',[
            'home_slides' => $slides,
            'quotes' => $quotes,
            'why_choose_us' => $why_choose_us,
            'testimonials' => $testimonials,
            'events' => $events,
            'quick_links' => $quick_links,
            'about' => $about ?? []
        ]);
    }

    public function store_slide(Request $request)
    {

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);
        // 1920 x 786
        // Handle the uploaded image
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
            $image = ImageManager::imagick()->read("storage/${imagePath}");
            $image->resize(1920, 786);
            $image->save("storage/${imagePath}");
        }
        // $image = Image::make($request->file('image'));
         // Store in the database (example logic)
        //  DB::beginTransaction();
        $slide = Slide::create([
            'caption_title' => $request->title,
            'caption' => $request->description,
            'image_src' => $imagePath ?? null,
            'vedio_url' => null,
        ]);
        return response()->json($slide);

    }
    public function delete_slide(Slide $slide){
        $slide->delete();
    }
    public function store_quotes(Request $request){
        $request->validate([
            'title' => 'required|string|max:255',
            'link' => 'nullable|string|max:255',
            'image' => 'image|mimes:jpg,png,jpeg|max:2048',
            'description' => 'required|string',
            // 'icon' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);
          if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');

        }
        // dd("storage/${imagePath}");
        $image = ImageManager::imagick()->read("storage/${imagePath}");
        $image->resize(74, 62);
        $image->save("storage/${imagePath}");
        $quote = Quote::create([
            'image_src' => $imagePath,
            'button_url' => $request->link,
            'title' => $request->title,
            'description' => $request->description
        ]);
        return response()->json($quote);
    }
    public function delete_quotes(Quote $quote){
        $quote->delete();
    }


    public function store_about(Request $request){

        $request->validate([
            'videoId' => 'required|string|max:255',
            'videoUrl' => 'nullable|string|max:255',
            'thumbnailImage' => 'nullable|string|max:255',
            'decorationImage' => 'nullable|string',
            'subtitle' => 'required|string',
            'title' => 'required|string',
            'content1' => 'required|string',
            'content2' => 'nullable|string',
            'listItems' => 'nullable|array',
        ]);
        $about = About::create([
            'image_src' => $request->thumbnailImage,
            'video_src' => $request->videoId,
            'title_section' => $request->subtitle,
            'title' => $request->title,
            'description' => $request->content1 . '|'. $request->content2,
            'stmt1'=> $request->listItems[0] ?? null,
            'stmt2'=> $request->listItems[1] ?? null,
            'stmt3'=> $request->listItems[2] ?? null,
            'stmt4'=> $request->listItems[3] ?? null,
        ]);

        // return response()->noContent();

        // if ($request->hasFile('image')) {
        //     $imagePath = $request->file('image')->store('images', 'public');
        // }
    }
    public function update_about(About $about, Request $request){
        $request->validate([
            'videoId' => 'required|string|max:255',
            'videoUrl' => 'nullable|string|max:255',
            'thumbnailImage' => 'nullable|string|max:255',
            'decorationImage' => 'nullable|string',
            'subtitle' => 'required|string',
            'title' => 'required|string',
            'content1' => 'required|string',
            'content2' => 'nullable|string',
            'listItems' => 'nullable|array',
        ]);

        $about->update([
            'image_src' => $request->thumbnailImage,
            'video_src' => $request->videoId,
            'title_section' => $request->subtitle,
            'title' => $request->title,
            'description' => $request->content1 . '|'. $request->content2,
            'stmt1'=> $request->listItems[0] ?? null,
            'stmt2'=> $request->listItems[1] ?? null,
            'stmt3'=> $request->listItems[2] ?? null,
            'stmt4'=> $request->listItems[3] ?? null,
        ]);

    }
    public function store_whyus(Request $request){
        $request->validate([
            'description' => 'required|string|max:255',
            'icon' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'title' => 'required|string',
        ]);
        if ($request->hasFile('icon')) {
            $icon = $request->file('icon')->store('icons', 'public');
        }
        $reason = WhyChooseUs::create([
            'image_src' => $icon,
            'title' => $request->title,
            'description' => $request->description
        ]);

        return response()->json($reason, 201);
    }

    public function delete_whyus(WhyChooseUs $reason){
        $reason->delete();
    }
    public function store_testimonials(Request $request){

        $image_src = null;
        $request->validate([
            'name' => 'required|string|max:255',
            'authorPhoto	' => 'nullable|string',
            'message' => 'required|string',
            'actualPhoto' => 'nullable|image|mimes:jpg,png,jpeg|max:2048'
        ]);
        // 369 x 221
        if ($request->hasFile('image')) {
            $image_src = $request->file('image')->store('testimonial', 'public');
            $image = ImageManager::imagick()->read("storage/${image_src}");
            $image->resize(262, 262);
            $image->save("storage/${image_src}");
        }else{
            $image_src = $request->authorPhoto;
        }


        $testimonial = Testimonial::create([
            'name' => $request->name,
            'image_src' => $image_src,
            'message' => $request->message,
        ]);
        return response()->json($testimonial, 201);
    }

    public function delete_testimonial(Testimonial $testimonial){
        $testimonial->delete();
    }

    public function store_events(Request $request){
        $validated = $request->validate([
            'date' => 'required|date',
            'end_time' => 'required|string',
            'start_time' => 'required|string',
            'venue' => 'required|string',
            'name' => 'required|string'
        ]);
        $event = UpcomingEvent::create($validated);

        return response()->json($event, 201);
    }
    public function delete_events(UpcomingEvent $event){
        $event->delete();
    }

    public function store_quicklinks(Request $request){

        $validated = $request->validate([
            'welcome_text' => 'nullable|string|max:100',
            'title' => 'required|string|max:100',
            'date' => 'nullable|date',
            'description' => 'nullable|string',
            'button_label' => 'nullable|string|max:100',
            'button_url' => 'nullable|string|max:100',
            'link1_text' => 'nullable|string|max:20',
            'link1_href' => 'nullable|string|max:255',
            'link2_text' => 'nullable|string|max:20',
            'link2_href' => 'nullable|string|max:255',
            'link3_text' => 'nullable|string|max:20',
            'link3_href' => 'nullable|string|max:255',
            'link4_text' => 'nullable|string|max:20',
            'link4_href' => 'nullable|string|max:255',
        ]);
        $validated['date'] = now();
        $quicklink = QuickLink::create($validated);

        return response()->json($quicklink, 201);
    }
    public function delete_quicklinks(QuickLink $link){
        $link->delete();
    }


    // services website

}
