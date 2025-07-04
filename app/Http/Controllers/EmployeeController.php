<?php

namespace App\Http\Controllers;

use App\Http\Requests\Employees\EmployeeCredentialRequest;
use App\Http\Resources\Resource;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmployeeController extends Controller
{
    public function dataTable()
    {
        $employees = QueryBuilder::for(
            Employee::with(['employment_type', 'employment_status', 'honorific', 'marital_status', 'gender', 'religion', 'teacher'])
                ->orderBy('first_name')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::scope('search', 'Search'),
        ])->jsonPaginate();
        
        return Resource::collection($employees);
    }
    
    public function systemAccess(EmployeeCredentialRequest $request, Employee $employee): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validated();
        
        $update = ['has_system_access' => $validated['has_system_access']];
        
        if (!empty($validated['password'])) {
            $update['password'] = Hash::make($validated['password']);
        }
        
        $employee->update($update);
        
        return back(303)->with('success', 'Credentials captured.');
    }
    
    public function revokeSystemAccess(Employee $employee)
    {
        $employee->update([
            'has_system_access' => false,
        ]);
        
        return back(303)->with('success', 'Access Revoked');
    }
}
