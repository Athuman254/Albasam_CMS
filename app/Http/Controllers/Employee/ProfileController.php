<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ProfileController extends Controller
{

    public function index(){
      $user = Auth::guard("employee")->user();
      $user->load('employment_type');
      $user->load('employment_status');
      $user->load('honorific');
      $user->load('teacher.job');
      $user->load('marital_status');
      $user->load('religion');
      $user->load('gender');
      $user->load('contacts');
      $user->load('qualifications');
      $user->load('histories');
      return Inertia::render('Employee/Profile/Index', [
          'employee' => $user,
      ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'currentPassword' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);
        $employee = Auth::guard('employee')->user();
        if (!password_verify($request->currentPassword, $employee->password)) {
            return back()->withErrors(['currentPassword' => 'Current password is incorrect.']);
        }

        $employee->password = Hash::make($request->password);
        $employee->save();
    }
}
