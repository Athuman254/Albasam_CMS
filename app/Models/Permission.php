<?php

namespace App\Models;

use Laratrust\Models\Permission as PermissionModel;

class Permission extends PermissionModel
{
    protected $table = 'permissions';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'code', 'display_name', 'description'];
}
