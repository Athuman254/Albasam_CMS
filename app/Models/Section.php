<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $table = 'sections';
    protected $primaryKey = 'id';
    protected $casts = ['is_active' => 'bool'];
    protected $fillable = [
        'page_id', 'title', 'sub_title', 'order', 'bg_style', 'bg_color', 'bg_image', 'type', 'type_image', 'content', 'is_active'
    ];

    public function page(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function subSections(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SubSection::class);
    }
}
