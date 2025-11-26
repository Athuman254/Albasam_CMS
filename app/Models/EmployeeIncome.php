<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;

class EmployeeIncome extends Model
{
   use HasHashid, HashidRouting;
    protected $fillable = [
      "employee_id",
      "income_id",
      "amount",
      "freeze",
    ];

    protected $appends = ["hashid"];

    public function employee(){
      return $this->belongsTo(Employee::class);
    }
    public function income(){
      return $this->belongsTo(Income::class);
    }
}
