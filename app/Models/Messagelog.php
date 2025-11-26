<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Messagelog extends Model
{
    protected $table = "message_logs";
    protected $guarded = [];

    protected $fillable = ["message_id"];

}
