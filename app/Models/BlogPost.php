<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    //
    protected $table = "blog_posts";
    protected $guarded = [];

    public function blog_categories() {
        return $this->hasMany(BlogPostCategory::class, );
    }
    public function category() {
        return $this->belongsTo(BlogCategory::class,"category_id");
    }
    public function scopeFilter($query, array $filters){
      if($filters['category'] ?? false){
        return $query->whereHas('category', function($query) {
            $query->where('name', 'like', '%' . request('category') . '%');
        });
      }
      if($filters['search'] ?? false){
        return $query->where('title','like', '%'.request('search').'%')->orWhere('content','like', '%'.request('search').'%');
      }
    }

}
