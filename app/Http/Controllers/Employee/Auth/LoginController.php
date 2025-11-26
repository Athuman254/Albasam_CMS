<?php

namespace App\Http\Controllers\Employee\Auth;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class LoginController extends Controller
{
    /**
     * Show the employee login form
     */
    public function showLoginForm()
    {
        return Inertia::render('Employee/Auth/Login');
    }
    
    /**
     * Handle employee login request
     */
    public function login(Request $request)
    {
        // Start login attempt logging
        Log::info('🎯 EMPLOYEE LOGIN ATTEMPT', [
            'identifier' => $request->identifier,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Validate input
        $request->validate([
            'identifier' => 'required|string|max:255',
            'password' => 'required|string|min:1',
        ]);
        
        // Clean and prepare identifier
        $cleanIdentifier = trim($request->identifier);
        
        // Find employee by multiple identifiers
        $employee = $this->findEmployeeByIdentifier($cleanIdentifier);
        
        if (!$employee) {
            Log::warning('❌ EMPLOYEE NOT FOUND', ['identifier' => $cleanIdentifier]);
            return $this->sendFailedLoginResponse($request, 'identifier');
        }
        
        // Check if employee has system access
        if (!$this->hasSystemAccess($employee)) {
            Log::warning('❌ EMPLOYEE ACCESS REVOKED', ['employee_id' => $employee->id]);
            return $this->sendFailedLoginResponse($request, 'identifier', 'Your system access has been revoked. Please contact administrator.');
        }
        
        // Check if password is set
        if (!$this->hasPasswordSet($employee)) {
            Log::warning('❌ EMPLOYEE NO PASSWORD SET', ['employee_id' => $employee->id]);
            return $this->sendFailedLoginResponse($request, 'identifier', 'No password set for your account. Please contact administrator.');
        }
        
        // Verify password
        if (!$this->verifyPassword($request->password, $employee)) {
            Log::warning('❌ PASSWORD VERIFICATION FAILED', [
                'employee_id' => $employee->id,
                'staff_number' => $employee->staff_number
            ]);
            return $this->sendFailedLoginResponse($request, 'password', 'Invalid password. Please try again.');
        }
        
        // Attempt authentication
        if (!$this->authenticateEmployee($employee, $request)) {
            Log::error('❌ AUTHENTICATION FAILED', ['employee_id' => $employee->id]);
            return $this->sendFailedLoginResponse($request, 'identifier', 'Authentication failed. Please try again.');
        }
        
        // Login successful
        Log::info('✅ EMPLOYEE LOGIN SUCCESS', [
            'employee_id' => $employee->id,
            'name' => $employee->full_name,
            'staff_number' => $employee->staff_number
        ]);
        
        return $this->sendLoginResponse($request);
    }
    
    /**
     * Find employee by identifier (staff_number, email, or phone)
     */
    private function findEmployeeByIdentifier(string $identifier): ?Employee
    {
        Log::debug('🔍 SEARCHING EMPLOYEE', ['identifier' => $identifier]);
        
        $employee = Employee::where('staff_number', $identifier)
            ->orWhere('email', $identifier)
            ->orWhere('primary_phone', $identifier)
            ->first();
        
        if ($employee) {
            Log::debug('✅ EMPLOYEE FOUND', [
                'id' => $employee->id,
                'staff_number' => $employee->staff_number,
                'email' => $employee->email,
                'has_system_access' => $employee->has_system_access
            ]);
        }
        
        return $employee;
    }
    
    /**
     * Check if employee has system access
     */
    private function hasSystemAccess(Employee $employee): bool
    {
        return $employee->has_system_access === true;
    }
    
    /**
     * Check if employee has password set
     */
    private function hasPasswordSet(Employee $employee): bool
    {
        return !empty($employee->password) && strlen($employee->password) > 0;
    }
    
    /**
     * Verify password against stored hash
     */
    private function verifyPassword(string $password, Employee $employee): bool
    {
        // Extensive password debugging in development
        if (app()->environment('local', 'development')) {
            $this->debugPasswordVerification($password, $employee);
        }
        
        // Standard password verification
        $isValid = Hash::check($password, $employee->password);
        
        // Additional check for common password variations (development only)
        if (!$isValid && app()->environment('local', 'development')) {
            $isValid = $this->checkCommonVariations($password, $employee->password);
        }
        
        return $isValid;
    }
    
    /**
     * Debug password verification process
     */
    private function debugPasswordVerification(string $password, Employee $employee): void
    {
        Log::debug('🔑 PASSWORD VERIFICATION DEBUG', [
            'input_length' => strlen($password),
            'stored_hash_prefix' => substr($employee->password, 0, 20),
            'stored_hash_length' => strlen($employee->password),
            'employee_id' => $employee->id
        ]);
        
        // Test hash functionality
        $testHash = Hash::make('test123');
        $testCheck = Hash::check('test123', $testHash);
        
        Log::debug('🧪 HASH FUNCTION TEST', [
            'test_result' => $testCheck ? '✅ WORKING' : '❌ BROKEN'
        ]);
    }
    
    /**
     * Check common password variations (development only)
     */
    private function checkCommonVariations(string $password, string $storedHash): bool
    {
        $commonVariations = [
            trim($password),
            rtrim($password),
            $password . ' ',
            ' ' . $password,
            $password . "\n",
            $password . "\t",
            strtolower($password),
            ucfirst($password),
        ];
        
        foreach ($commonVariations as $variation) {
            if (Hash::check($variation, $storedHash)) {
                Log::debug('🎯 FOUND MATCHING VARIATION', [
                    'original' => $password,
                    'variation' => $variation,
                    'variation_length' => strlen($variation)
                ]);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Authenticate employee using guard
     */
    private function authenticateEmployee(Employee $employee, Request $request): bool
    {
        // Method 1: Use Auth::guard()->attempt with credentials
        $credentials = [
            'staff_number' => $employee->staff_number,
            'password' => $request->password
        ];
        
        $attemptResult = Auth::guard('employee')->attempt($credentials, $request->filled('remember'));
        
        if ($attemptResult) {
            Log::debug('✅ AUTH ATTEMPT SUCCESS');
            return true;
        }
        
        // Method 2: Manual login if attempt fails
        Log::debug('🔄 FALLBACK TO MANUAL LOGIN');
        Auth::guard('employee')->login($employee, $request->filled('remember'));
        
        return Auth::guard('employee')->check();
    }
    
    /**
     * Send successful login response
     */
    private function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        
        return redirect()->intended(route('employee.dashboard'));
    }
    
    /**
     * Send failed login response
     */
    private function sendFailedLoginResponse(Request $request, string $field, string $message = null)
    {
        $defaultMessages = [
            'identifier' => 'No staff member found with those credentials',
            'password' => 'Invalid password. Please try again.'
        ];
        
        $errorMessage = $message ?? $defaultMessages[$field] ?? 'Login failed';
        
        return back()->withErrors([
            $field => $errorMessage
        ])->onlyInput($field);
    }
    
    /**
     * Handle employee logout
     */
    public function logout(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        
        Log::info('👋 EMPLOYEE LOGOUT', [
            'employee_id' => $employee?->id,
            'name' => $employee?->full_name
        ]);
        
        Auth::guard('employee')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('employee.login');
    }
    
    /**
     * Check authentication status (for debugging)
     */
    public function checkAuth(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        
        return response()->json([
            'authenticated' => !is_null($employee),
            'employee' => $employee ? [
                'id' => $employee->id,
                'staff_number' => $employee->staff_number,
                'name' => $employee->full_name,
                'has_system_access' => $employee->has_system_access
            ] : null,
            'session_id' => $request->session()->getId(),
            'guard' => 'employee'
        ]);
    }
    
    /**
     * Test employee authentication (for debugging)
     */
    public function testAuth(Request $request)
    {
        $request->validate([
            'staff_number' => 'required|string',
            'password' => 'required|string',
        ]);
        
        $credentials = $request->only('staff_number', 'password');
        $attempt = Auth::guard('employee')->attempt($credentials);
        
        return response()->json([
            'attempt_success' => $attempt,
            'authenticated' => Auth::guard('employee')->check(),
            'employee' => Auth::guard('employee')->user() ? [
                'id' => Auth::guard('employee')->user()->id,
                'staff_number' => Auth::guard('employee')->user()->staff_number,
                'name' => Auth::guard('employee')->user()->full_name
            ] : null
        ]);
    }
}