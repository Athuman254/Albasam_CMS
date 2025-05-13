<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $table = 'sections';
    protected $primaryKey = 'id';
    protected $casts = ['active' => 'bool', 'order' => 'int'];
    protected $fillable = [
        'page_id', 'type', 'title', 'sub_title', 'description', 'order', 'active'
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
