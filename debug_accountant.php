<?php

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Find an employee who is an accountant
$employee = Employee::whereHas('user', function ($q) {
    $q->whereHas('roles', function ($r) {
        $r->where('name', 'accountant');
    });
})->with('user.roles')->first();

if (!$employee) {
    echo "No employee found with Accountant role linked to a User.\n";

    // Try to find just by job title or something if role check fails
    $employee = Employee::first();
    echo "Checking first employee found: " . ($employee ? $employee->full_name : 'None') . "\n";
    if ($employee && $employee->user) {
        echo "Linked User Roles: " . implode(', ', $employee->user->roles->pluck('name')->toArray()) . "\n";
    }
} else {
    echo "Found Accountant Employee: " . $employee->full_name . "\n";
    echo "Staff Number: " . $employee->staff_number . "\n";
    echo "User ID: " . $employee->user_id . "\n";
    echo "User Roles: " . implode(', ', $employee->user->roles->pluck('name')->toArray()) . "\n";
}
