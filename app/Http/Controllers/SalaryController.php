<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Salary;
use App\Models\Employee;

class SalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Salary::where('deleted_at', null)->get();
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
            'employee_id' => 'required|numeric',
            'basic' => 'required|numeric',
            'da' => 'required|numeric',
            'hra' => 'required|numeric',
            'ca' => 'required|numeric',
            'ea' => 'required|numeric',
            'mi' => 'required|numeric',
            'bonus' => 'required|numeric',
            'ot' => 'required|numeric',
            'it' => 'required|numeric',
            'pf' => 'required|numeric',
            'month' => 'required|string|regex:/^[A-Za-z]+\s\d{4}$/'
        ]);


        $employee = Employee::where('deleted_at', null)
            ->get()
            ->first();

        $salary = Salary::where('deleted_at', null)
            ->where('month', $request->month)
            ->get()
            ->first();

        if ($employee !== null && $salary === null) {
            Salary::create($request->all());
            return response('Success', 201);
        }
        return response(["message" => "The given data was invalid."], 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
