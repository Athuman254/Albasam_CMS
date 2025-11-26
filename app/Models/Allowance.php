<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Allowance extends Model
{
   use HasHashid, HashidRouting;
   protected $fillable = [
      'name',
      'is_ahl_exempted',
      'is_active'
   ];

    protected $appends = ['hashid'];
}
