<?php

namespace App\Models\Website;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;

class SectionCtaButton extends Model
{
    use HasHashid, HashidRouting;
    
    protected $table = 'section_cta_buttons';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $fillable = [
        'section_id', 'page_id', 'cta_button_text', 'cta_button_type'
    ];
    
    public function section(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }
    
    public function page(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id', 'id');
    }
}
