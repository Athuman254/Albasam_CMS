<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;

class EmployeeDeduction extends Model
{
   use HasHashid, HashidRouting;
    protected $fillable = [
      "employee_id",
      "deduction_id",
      "amount",
      "freeze",
    ];

    protected $appends = ["hashid"];

    public function employee(){
      return $this->belongsTo(Employee::class);
    }
    public function deduction(){
      return $this->belongsTo(Deduction::class);
    }
}
