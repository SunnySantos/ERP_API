<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateBasicRequest;
use App\Http\Requests\UpdateEmployeeJobRequest;
use App\Http\Resources\EmployeeResource;
use App\Import\EmployeeImport;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{

    public function importCSV(Request $request)
    {
        (new EmployeeImport)->queue($request->file('csv'), null, \Maatwebsite\Excel\Excel::CSV);
        return response('Successfully imported.', 201);
    }

    public function exportCSV()
    {
        $filename = 'employees.csv';
        $employees = EmployeeResource::collection(Employee::all());

        $columns = [
            'id',
            'department',
            'branch',
            'position',
            'firstname',
            'middlename',
            'lastname',
            'address',
            'sex',
            'marital_status',
            'birth',
            'phone_number',
            'hire'
        ];

        $file = fopen($filename, 'w');
        fputcsv($file, $columns);

        foreach ($employees as $employee) {
            $row = [];

            foreach ($columns as $column) {
                if ($column === "department") {
                    $row[$column] = $employee[$column]->name;
                } elseif ($column === "branch") {
                    $row[$column] = $employee[$column]->name;
                } elseif ($column === "position") {
                    $row[$column] = $employee[$column]->title;
                } else {
                    $row[$column] = $employee[$column];
                }
            }

            fputcsv($file, $row);
        }

        fclose($file);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($filename));
        header('Content-Transfer-Encoding: binary');
        header('Access-Control-Allow-Origin: *');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        ob_clean();
        flush();
        readfile($filename);
        unlink($filename);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $branch_id = auth()->user()->employee->branch_id;

        $employees = Employee::where('branch_id', $branch_id);

        if (!empty($search)) $employees->where('id', $search);

        return EmployeeResource::collection(
            $employees->orderBy('id', 'DESC')
                ->paginate(10)
        );
    }

    public function count()
    {
        return Employee::select('id')
            ->where('branch_id', auth()->user()->employee->branch_id)
            ->whereNull('deleted_at')
            ->count();
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEmployeeRequest $request)
    {
        $user = User::create([
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'role_id' => 1
        ]);
        if ($user) {
            $request['user_id'] = $user->id;

            $employee = Employee::create($request->all());

            if ($employee) return response('Successfully created.', 201);

            $user->forceDelete();
        }
        return response("Failed", 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return EmployeeResource::make(
            Employee::where('id', $id)
                ->first()
        );
    }

    public function account()
    {
        return EmployeeResource::make(
            Employee::where('user_id', auth()->id())
                ->first()
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateJob(UpdateEmployeeJobRequest $request, $id)
    {
        $employee = Employee::where('id', $id)
            ->update($request->only([
                'branch_id',  'department_id', 'position_id', 'hire'
            ]));

        return $employee ? response("Successfully updated.")
            : response("Failed!", 400);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateBasic(UpdateBasicRequest $request, $id)
    {
        $employee = Employee::where('id', $id)
            ->update($request->only([
                'firstname',
                'middlename',
                'lastname',
                'sex',
                'marital_status',
                'birth',
                'address',
                'phone_number'
            ]));

        return $employee ? response("Successfully updated.")
            : response("Failed.", 400);
    }

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
