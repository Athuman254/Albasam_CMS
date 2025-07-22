<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;

class Deduction extends Model
{
    //
     use HasHashid, HashidRouting;
   protected $fillable = [
      'name',
      'is_active'
   ];

    protected $appends = ['hashid'];
}
