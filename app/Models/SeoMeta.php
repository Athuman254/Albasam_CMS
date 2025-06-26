<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\OpenGraph;
use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{
    use HasHashid, HashidRouting;
    
    protected $table = 'seo_metas';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $fillable = [
        'seo_able_type', 'seo_able_id', 'title', 'description', 'keywords'
    ];
    
    public function meta(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
    
    public static function applyMeta($seo): void
    {
        $currentUrl = url()->current();
        
        /** Meta SEO */
        \Artesaos\SEOTools\Facades\SEOMeta::setTitle($seo->title);
        \Artesaos\SEOTools\Facades\SEOMeta::setDescription($seo->description);
        \Artesaos\SEOTools\Facades\SEOMeta::setKeywords($seo->keywords);
        \Artesaos\SEOTools\Facades\SEOMeta::setCanonical($currentUrl);
        
        OpenGraph::setTitle($seo->title);
        OpenGraph::setDescription($seo->description);
        OpenGraph::setUrl($currentUrl);
        OpenGraph::addProperty('type', 'WebPage');
        OpenGraph::addProperty('locale', 'en-us');
        
        JsonLd::setTitle($seo->title);
        JsonLd::setDescription($seo->description);
        JsonLd::setType('WebPage');
    }
}
