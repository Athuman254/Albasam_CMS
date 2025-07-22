<?php

namespace App\Models;

use App\Models\Employee;
use App\Models\Allowance;
use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;

class PayrollAllowance extends Model
{
   use HasHashid, HashidRouting;
    protected $fillable = [
      "employee_id",
      "allowance_id",
      "amount",
      "reason",
      "is_included",
      "month",
      "year",
      "date"
    ];
   protected $appends = ['hashid'];
    public function employee(){
      return $this->belongsTo(Employee::class);
    }

    public function allowance(){
      return $this->belongsTo(Allowance::class);
    }

    public function scopeSearch($query, $search){
      $query->whereHas("employee", function ($query) use ($search) {
         $query->where("first_name","LIKE","%".$search."%")
         ->orWhere("last_name","LIKE","%".$search."%")
         ->orWhere("staff_number","LIKE","%".$search."%");
      });

    }
}
