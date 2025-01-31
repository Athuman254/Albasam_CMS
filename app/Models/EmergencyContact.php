<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyContact extends Model
{
    use HasHashid, HashidRouting;

    protected $table = 'emergency_contacts';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $fillable = [
        'employee_id', 'relationship_id', 'name', 'email', 'phone'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function relationship(): BelongsTo
    {
        return $this->belongsTo(Relationship::class, 'relationship_id', 'id');
    }
}
