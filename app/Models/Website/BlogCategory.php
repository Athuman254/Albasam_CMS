<?php

namespace App\Models\Website;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogCategory extends Model
{
    use SoftDeletes, HasHashid, HashidRouting;
    
    protected $table = 'blog_categories';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $casts = ['activated' => 'bool'];
    protected $fillable = ['name', 'activated'];
    
    public function scopeActivated($query)
    {
        $query->where('activated', '=', true);
    }
}
