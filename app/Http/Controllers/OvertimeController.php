<?php

namespace App\Http\Controllers;

use App\Models\Overtime;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Overtime::whereNull('deleted_at')
            ->paginate(10);
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
            'employee_id' => 'required|numeric|exists:employees,id',
            'overtime_date' => 'required|date',
            'hours' => 'required|numeric',
            'rate' => 'required|numeric'
        ]);

        $overtime = Overtime::create($request->all());

        if ($overtime) {
            return response('Successfully created.', 201);
        }

        return response('Failed.', 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Overtime::where('id', $id)
            ->whereNull('deleted_at')
            ->first();
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
        $request->validate([
            'employee_id' => 'required|numeric|exists:employees,id',
            'overtime_date' => 'required|date',
            'hours' => 'required|numeric',
            'rate' => 'required|numeric'
        ]);

        $overtime = Overtime::where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'employee_id' => $request->employee_id,
                'overtime_date' => $request->overtime_date,
                'hours' => $request->hours,
                'rate' => $request->rate
            ]);

        if ($overtime) {
            return response('Successfully updated.', 200);
        }
        return response('Failed.', 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $overtime = Overtime::find($id);

        if ($overtime) {
            $overtime->delete();
            return response('Successfully deleted.', 200);
        }
        return response('Failed.', 400);
    }
}
