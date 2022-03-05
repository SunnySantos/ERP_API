<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function registerCustomer(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string|regex:/^[a-zA-ZñÑ\s]*$/',
            'middlename' => 'nullable|string|regex:/^[a-zA-ZñÑ\s]*$/',
            'lastname' => 'required|string|regex:/^[a-zA-ZñÑ\s]*$/',
            'address' => 'required|string',
            'phone_number' => 'required|string|unique:customers|regex:/^9\d{9}$/',
            'email' => 'required|email|unique:customers,email',
            'username' => 'required|min:8|string|unique:users',
            'password' => 'required|min:8|string|confirmed',
        ]);

        $user = [
            'role_id' => 2,
            'username' => $request->username,
            'password' => bcrypt($request->password),
        ];

        $user = User::create($user);

        if ($user !== null) {
            $customer = Customer::create([
                'user_id' => $user->id,
                'firstname' => $request->firstname,
                'middlename' => $request->middlename,
                'lastname' => $request->lastname,
                'address' => $request->address,
                'email' => $request->email,
                'phone_number' => $request->phone_number
            ]);
            if ($customer) {
                return response("Successfully created.", 201);
            } else {
                $user->forceDelete();
            }
        }
        return response("Invalid.", 400);
    }

    public function loginCustomer(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('username', $request->username)
            ->where('role_id', 2)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'Bad Credentials'
            ], 401);
        }

        $token = $user->createToken('myToken')->plainTextToken;

        $customer = Customer::select('id')
            ->where('user_id', $user->id)
            ->get()
            ->first();

        $user['customer_id'] = $customer->id;

        return response([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function logoutCustomer()
    {
        auth()->user()->tokens()->delete();

        return response("Logged out", 200);
    }

    public function loginSupplier(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('username', $request->username)
            ->where('role_id', 3)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'Incorrect username or password.'
            ], 401);
        }

        $token = $user->createToken('myToken')->plainTextToken;

        $supplier = Supplier::select('id')
            ->where('user_id', $user->id)
            ->get()
            ->first();

        $user['supplier_id'] = $supplier->id;

        return response([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function logoutSupplier()
    {
        auth()->user()->tokens()->delete();

        return response("Logged out", 200);
    }

    public function loginAdmin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('username', $request->username)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'Incorrect username or password.'
            ], 401);
        }

        $token = $user->createToken('myToken')->plainTextToken;

        $employee = Employee::select('branch_id', 'id', 'firstname', 'department_id')
            ->where('user_id', $user->id)
            ->get()
            ->first();

        $user['name'] = $employee->firstname;
        $user['employee_id'] = $employee->id;
        $user['branch_id'] = $employee->branch_id;
        $user['department_id'] = $employee->department_id;

        return response([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function logoutAdmin()
    {
        auth()->user()->tokens()->delete();

        return response("Logged out", 200);
    }
}
