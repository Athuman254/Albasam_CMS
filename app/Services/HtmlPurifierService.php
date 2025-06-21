<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlPurifierService
{
    protected $purifier;
    
    public function __construct()
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www.youtube.com/embed/|player.vimeo.com/video/)%');
        $config->set('HTML.Allowed', 'p,b,strong,i,em,u,ul,ol,li,a[href|title],h1,h2,h3,h4,h5,h6,blockquote,pre,code,br,hr,img[src|alt|width|height],span,div,table,thead,tbody,tr,td,th'); // customize allowed tags and attributes
        
        $this->purifier = new HTMLPurifier($config);
    }
    
    public function purify(string $dirtyHtml): string
    {
        return $this->purifier->purify($dirtyHtml);
    }
}
