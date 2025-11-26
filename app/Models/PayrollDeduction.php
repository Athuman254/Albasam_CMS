<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;

class PayrollDeduction extends Model
{
   use HasHashid, HashidRouting;
   protected $fillable = [
      "employee_id",
      "deduction_id",
      "amount",
      "reason",
      "deducted",
      "month",
      "year",
      "date"
   ];
   protected $appends = ['hashid'];
   public function employee()
   {
      return $this->belongsTo(Employee::class);
   }

   public function deduction()
   {
      return $this->belongsTo(Deduction::class);
   }
   public function scopeSearch($query, $search)
   {
      $query->whereHas("employee", function ($query) use ($search) {
         $query->where("first_name", "LIKE", "%" . $search . "%")
            ->orWhere("last_name", "LIKE", "%" . $search . "%")
            ->orWhere("staff_number", "LIKE", "%" . $search . "%");
      });
   }
}
