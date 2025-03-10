<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    //
    protected $guarded = [];
    protected $casts = [
        'settings' => 'array'
    ];

    public function messages(){
        return $this->hasMany(Message::class);
    }
}
