<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SystemMedia extends Model implements HasMedia
{
    use HasHashid, HashidRouting, InteractsWithMedia;

    protected $table = 'system_media';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $fillable = [
        'morph_type', 'morph_id', 'user_id', 'type', 'date'
    ];

    public function system_media(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}
