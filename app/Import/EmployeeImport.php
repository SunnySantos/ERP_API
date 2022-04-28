<?php

namespace App\Import;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class EmployeeImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, ShouldQueue
{

    use Importable;

    public function model(array $row)
    {
        $request = new Request();

        foreach ($row as $key => $value) {
            $request[$key] = $value;
        }

        $request->validate([
            'branch' => 'required|string|exists:branches,name',
            'firstname' => 'required|string|regex:/[A-Za-z]/',
            'middlename' => 'nullable|string|regex:/[A-Za-z]/',
            'lastname' => 'required|string|regex:/[A-Za-z]/',
            'phone_number' => 'required|numeric|unique:employees|regex:/^9\d{9}$/',
            'address' => 'required',
            'sex' => 'required|in:Male,Female',
            'position' => 'required|string|exists:positions,title',
            'department' => 'required|string|exists:departments,name',
            'hire' => 'required|date',
            'birth' => 'required|date',
            'username' => 'required|string|min:8|unique:users',
            'password' => 'required|string|min:8',
            'marital_status' => 'required|string|in:Single,Married,Others'
        ]);


        $branch = Branch::where('name', $request->branch)->first();
        $position = Position::where('title', $request->position)->first();

        $department = Department::where('name', $request->department)->first();

        $user = User::create([
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'role_id' => 1
        ]);

        if ($user) {
            $request['branch_id'] = $branch->id;
            $request['position_id'] = $position->id;
            $request['department_id'] = $department->id;
            $request['user_id'] = $user->id;
            $request['birth'] = date('Y-m-d', strtotime($request->birth));
            $request['hire'] = date('Y-m-d', strtotime($request->hire));
            $employee = Employee::create($request->all());

            if (!$employee) $user->forceDelete();
        }
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
