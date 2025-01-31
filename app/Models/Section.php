<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $table = 'sections';
    protected $primaryKey = 'id';
    protected $casts = ['is_active' => 'bool'];
    protected $fillable = [
        'page_id', 'parent_id', 'title', 'content', 'type', 'image', 'order', 'is_active'
    ];

    public function page(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Section::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Section::class, 'parent_id')->orderBy('order');
    }
}
