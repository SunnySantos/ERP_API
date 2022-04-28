<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOvertimeRequest;
use App\Http\Requests\UpdateOvertimeRequest;
use App\Http\Resources\OvertimeResource;
use App\Models\Overtime;

class OvertimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return OvertimeResource::collection(
            Overtime::whereNull('deleted_at')
                ->paginate(10)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOvertimeRequest $request)
    {
        $overtime = Overtime::create($request->all());

        return $overtime ? response('Successfully created.', 201)
            : response('Failed.', 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return OvertimeResource::make(
            Overtime::where('id', $id)
                ->whereNull('deleted_at')
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
    public function update(UpdateOvertimeRequest $request, $id)
    {
        $overtime = Overtime::where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'hours' => $request->hours,
                'rate' => $request->rate
            ]);

        return $overtime ? response('Successfully updated.')
            : response('Failed.', 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Overtime::find($id)->delete();

        return response('Successfully deleted.');
    }
}
