<?php

namespace App\Models\Website;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use App\Traits\HasSeoMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

class Page extends Model
{
    use HasHashid, HashidRouting, HasSeoMeta;

    protected $table = 'pages';
    protected $primaryKey = 'id';
    protected $casts = ['published' => 'bool', 'is_home' => 'bool'];
    protected $appends = ['hashid'];
    protected $fillable = [
        'title', 'description', 'slug', 'published', 'is_home'
    ];

    public function sections(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Section::class)->orderBy('order');
    }
    
    protected static function booted(): void
    {
        static::saved(function () {
            Artisan::call('sitemap:generate');
        });
        
        static::deleted(function () {
            Artisan::call('sitemap:generate');
        });
    }
}
