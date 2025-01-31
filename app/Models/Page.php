<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $table = 'pages';
    protected $primaryKey = 'id';
    protected $casts = ['is_published' => 'bool'];
    protected $fillable = [
        'title', 'content', 'slug', 'is_published'
    ];

    public function sections(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Section::class)->orderBy('order');
    }
}
