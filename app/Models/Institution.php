<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Institution extends Model implements HasMedia
{
    use SoftDeletes, HasHashid, HashidRouting, InteractsWithMedia;

    protected $table = 'institutions';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $fillable = [
        'name', 'code', 'email', 'phone', 'country', 'state', 'city', 'physical_address', 'postal_address', 'tax_identification_pin',
        'mission', 'vision', 'logo_size', 'x_profile', 'fb_profile', 'ig_profile', 'youtube_profile', 'tiktok_profile',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->useDisk('public')
            ->useFallbackPath(public_path('/logo.png'))
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

        $this->addMediaCollection('favicon')
            ->useDisk('public')
            ->useFallbackPath(public_path('/favicon.png'))
            ->acceptsFile(function (File $file) {
                return in_array($file->mimeType, [
                    'image/jpg', 'image/jpeg', 'image/png',
                ]);
            })
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('thumb')
                    ->width(291)
                    ->height(301);
            });
    }
}
