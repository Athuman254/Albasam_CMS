<?php

namespace App\Models\Website;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;

class Customisation extends Model
{
    use HasHashid, HashidRouting;
    
    protected $table = 'customisations';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $fillable = [
        'primary_color', 'primary_color_rgb', 'primary_color_light', 'primary_color_light_rgb',
        'secondary_color', 'secondary_color_rgb', 'secondary_color_light', 'secondary_color_light_rgb',
        'button_style'
    ];
    
    public static function hexToRgb(string $hex): string
    {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] .
                $hex[1] . $hex[1] .
                $hex[2] . $hex[2];
        }
        
        $int = hexdec($hex);
        return sprintf("rgb(%d, %d, %d)", ($int >> 16) & 255, ($int >> 8) & 255, $int & 255);
    }
}
