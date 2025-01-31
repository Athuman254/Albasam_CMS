<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPostCategory extends Model
{
    //
    protected $table = "blog_post_categories";

    protected $fillable = ['post_id', 'category_id', 'created_at'] ;


    // public function category(){
    //     return $this->belongsTo(BlogCategory::class,"category_id");
    // }
    public function post(){
        return $this->belongsTo( BlogPost::class,"post_id");
    }
}
