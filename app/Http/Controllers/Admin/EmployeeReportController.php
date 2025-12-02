<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PDF;

class EmployeeReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query()
            ->with(['user.roles'])
            ->when($request->role_id, function ($q) use ($request) {
                $q->whereHas('user.roles', function ($q2) use ($request) {
                    $q2->where('id', $request->role_id);
                });
            });

        $perPage = $request->input('per_page', 10);
        $employees = $query->paginate($perPage)->withQueryString();
        $roles = \App\Models\Role::all();

        return inertia('Admin/Reports/AllStaff', [
            'employees' => $employees,
            'roles' => $roles,
            'filters' => $request->only(['role_id']),
        ]);
    }

    public function export(Request $request)
    {
        $query = Employee::query()
            ->with(['user.roles'])
            ->when($request->role_id, function ($q) use ($request) {
                $q->whereHas('user.roles', function ($q2) use ($request) {
                    $q2->where('id', $request->role_id);
                });
            });

        $employees = $query->get();
        $roleName = $request->role_id ? \App\Models\Role::find($request->role_id)->display_name : 'All Roles';

        $pdf = PDF::loadView('reports.all-staff', [
            'employees' => $employees,
            'roleName' => $roleName,
            'generated_at' => now()->format('d/m/Y H:i'),
        ]);

        return $pdf->stream('all_staff_report.pdf');
    }
}
