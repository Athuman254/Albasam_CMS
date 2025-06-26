<?php

namespace App\Traits;

use App\Models\SeoMeta;

trait HasSeoMeta
{
    public function seoMeta()
    {
        return $this->morphOne(SeoMeta::class, 'seo_able');
    }
}
