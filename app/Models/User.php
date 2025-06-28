<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;

class User extends Authenticatable implements LaratrustUser
{
    use Notifiable, HasHashid, HashidRouting, HasRolesAndPermissions;

    protected $connection = 'mysql';

    protected $table = 'users';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'hashid'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'branch_id',
        'password',
        'email_verified_at',
        'activated',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activated' => 'boolean',
            'is_admin' => 'boolean',
            'is_teacher' => 'boolean',
            'is_parent' => 'boolean',
        ];
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
    
    public function teacher(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    public function scopeActivated($query): void
    {
        $query->where('activated', '=', true);
    }

    public function scopeAdmin($query): void
    {
        $query->where('is_admin', '=', true);
    }

    public function scopeTeacher($query): void
    {
        $query->where('is_teacher', '=', true);
    }

    public function scopeParent($query): void
    {
        $query->where('is_parent', '=', true);
    }
}
