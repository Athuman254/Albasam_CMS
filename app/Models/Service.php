<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
    use HasFactory;

    protected $guarded = [];

    public function category(){
        return $this->belongsTo(BlogCategory::class);
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
