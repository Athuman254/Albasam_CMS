<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubSection extends Model
{
    protected $table = 'sub_sections';
    protected $primaryKey = 'id';
    protected $casts = ['is_active' => 'bool'];
    protected $fillable = [
        'section_id', 'title', 'sub_title', 'order', 'type', 'type_image', 'content', 'is_active'
    ];

    public function section(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
