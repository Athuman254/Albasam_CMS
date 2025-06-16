<?php

namespace App\Models\Website;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasHashid, HashidRouting;
    
    protected $table = 'menus';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $casts = ['has_children' => 'boolean'];
    protected $fillable = [
        'page_id', 'title', 'type', 'url', 'has_children', 'parent_id', 'order'
    ];
    
    const TYPE_PAGE = 'page';
    const TYPE_CUSTOM = 'custom';
    
    const TYPES = [
        'page', 'custom'
    ];
    
    public function page(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id', 'id');
    }
}
