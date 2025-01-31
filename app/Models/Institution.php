<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use SoftDeletes, HasHashid, HashidRouting;

    protected $table = 'institutions';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $fillable = [
        'name', 'code', 'email', 'phone', 'country', 'state', 'city', 'physical_address', 'postal_address', 'tax_identification_pin',
        'mission', 'vision', 'logo_size', 'x_profile', 'fb_profile', 'ig_profile', 'youtube_profile', 'tiktok_profile',
    ];
}
