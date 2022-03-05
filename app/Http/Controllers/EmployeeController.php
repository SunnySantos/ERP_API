<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $branch_id = auth()->user()->employee->branch_id;

        if (is_null($search) || $search === "null") {
            return Employee::with([
                'department',
                'position',
                'schedule',
                'branch',
                'user'
            ])
                ->where('branch_id', $branch_id)
                ->paginate(10);
        }

        return Employee::with([
            'department',
            'position',
            'schedule',
            'branch',
            'user'
        ])
            ->where('branch_id', $branch_id)
            ->where('id', $search)
            ->paginate(10);
    }

    public function count(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|numeric|exists:branches,id'
        ]);

        $count = Employee::select('id')
            ->where('branch_id', $request->branch_id)
            ->whereNull('deleted_at')
            ->count();
        return $count;
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|numeric|exists:branches,id',
            'firstname' => 'required|string|regex:/[A-Za-z]/',
            'middlename' => 'nullable|string|regex:/[A-Za-z]/',
            'lastname' => 'required|string|regex:/[A-Za-z]/',
            'phone_number' => 'required|numeric|unique:employees|regex:/^9\d{9}$/',
            'address' => 'required',
            'sex' => 'required|in:Male,Female',
            'position_id' => 'required|numeric|exists:positions,id',
            'schedule_id' => 'required|numeric|exists:schedules,id',
            'department_id' => 'required|numeric|exists:departments,id',
            'hire' => 'required',
            'birth' => 'required',
            'username' => 'required|string|min:8|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'marital_status' => 'required|string|in:Single,Married,Others'
        ]);

        $user = User::create([
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'role_id' => 1
        ]);
        if ($user) {
            $request['user_id'] = $user->id;
            $employee = Employee::create($request->all());
            if ($employee) {
                return response('Successfully created.', 201);
            }
            $user->forceDelete();
            return response('Creation failed.', 400);
        }
        return response("Invalid", 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Employee::with([
            'department',
            'position',
            'schedule',
            'branch',
            'user'
        ])
            ->where('id', $id)
            ->first();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateJob(Request $request, $id)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|numeric|exists:positions,id',
            'schedule_id' => 'required|numeric|exists:schedules,id',
            'hire' => 'required|date'
        ]);

        $employee = Employee::find($id);

        if ($employee !== null) {
            if ($employee->update($request->all())) {
                return response("Successfully updated!", 200);
            }
        }

        return response("Employee does not exist!", 400);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateBasic(Request $request, $id)
    {
        $request->validate([
            'firstname' => 'required|min:2|string',
            'middlename' => 'nullable|min:2|string',
            'lastname' => 'required|min:2|string',
            'sex' => 'required|string',
            'marital_status' => 'required|string',
            'birth' => 'required|date',
            'address' => 'required|string',
            'phone_number' => ['required', 'string', 'regex:/^9\d{9}$/', Rule::unique("employees", "phone_number")->ignore($id)]
        ]);

        $employee = Employee::find($id);

        if ($employee !== null) {
            if ($employee->update($request->all())) {
                return response("Successfully updated!", 200);
            }
        }

        return response("Employee does not exist!", 400);
    }

    // /**
    //  * Update the specified resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  int  $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function update(Request $request, $id)
    // {
    //     // PASSWORD AND PHOTO_DIR is not yet included
    //     $employee = Employee::find($id);
    //     $request->validate([
    //         'firstname' => 'required|string|regex:/[A-Za-z]/',
    //         'middlename' => 'nullable|string|regex:/[A-Za-z]/',
    //         'lastname' => 'required|string|regex:/[A-Za-z]/',
    //         'suffix' => 'nullable|string|regex:/[A-Za-z]/',
    //         'email' => 'required|email|unique:employees,email,' . $employee->id,
    //         'phone_number' => 'required|numeric|regex:/^9\d{9}$/',
    //         'home_address' => 'required',
    //         'sex' => 'required',
    //         'job_title' => 'required|string|regex:/[A-Za-z]/',
    //         'department_id' => 'required',
    //         'hire_date' => 'required',
    //         'birth_date' => 'required',
    //         'photo_dir' => 'nullable|string',
    //         'active' => 'required'
    //     ]);

    //     $department = Department::find($request['department_id']);

    //     if ($employee !== null && $department !== null) {
    //         if ($employee->update($request->all())) {
    //             return response('Success', 200);
    //         }
    //     }
    //     return response('Invalid', 400);
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
