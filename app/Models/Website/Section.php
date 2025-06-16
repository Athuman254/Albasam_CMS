<?php

namespace App\Models\Website;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Section extends Model implements HasMedia
{
    use HasHashid, HashidRouting, InteractsWithMedia;
    
    protected $table = 'sections';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $casts = [
        'include_contact_cards' => 'boolean',
        'section_has_image' => 'boolean',
        'section_image_first' => 'boolean',
        'has_cta_buttons' => 'boolean',
        'order' => 'integer',
    ];
    protected $fillable = [
        'page_id', 'type', 'title', 'sub_title', 'component_type', 'details',
        'include_contact_cards', 'section_image_first', 'has_cta_buttons', 'section_has_image', 'order', 'active', 'map_link'
    ];
    
    public function page(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
    
    public function cta_buttons(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SectionCtaButton::class);
    }
    
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('section_image')
            ->useDisk('media')
            ->useFallbackPath(public_path('/dummy-image.jpg'))
            ->acceptsFile(function (File $file) {
                return in_array($file->mimeType, [
                    'image/jpg', 'image/jpeg', 'image/png',
                ]);
            })
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('thumb')
                    ->width(831)
                    ->height(301);
            });
    }
}
