<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasHashid, HashidRouting;

    protected $table = 'pages';
    protected $primaryKey = 'id';
    protected $casts = ['is_published' => 'bool', 'is_home' => 'bool'];
    protected $appends = ['hashid'];
    protected $fillable = [
        'title', 'content', 'slug', 'is_published', 'is_home'
    ];

    public function sections(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Section::class)->orderBy('order');
    }
}
