<?php

namespace App\Models\Website;

use App\Models\EmploymentType;
use App\Models\Language;
use App\Models\User;
use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use App\Traits\HasSeoMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Career extends Model
{
    use HasHashid, HashidRouting, InteractsWithMedia, HasSeoMeta;
    
    protected $table = 'careers';
    protected $primaryKey = 'id';
    protected $casts = ['active' => 'boolean'];
    protected $appends = ['hashid'];
    protected $fillable = [
        'user_id', 'employment_type_id', 'language_id', 'title', 'slug', 'location',
        'start_date', 'deadline_date', 'job_description', 'active'
    ];
    
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function contract_type(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(EmploymentType::class, 'employment_type_id', 'id');
    }
    
    public function language(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }
    
    protected static function booted(): void
    {
        static::saved(function () {
            Artisan::call('app:generate-sitemap');
        });
        
        static::updated(function () {
            Artisan::call('app:generate-sitemap');
        });
        
        static::deleted(function () {
            Artisan::call('app:generate-sitemap');
        });
    }
}
