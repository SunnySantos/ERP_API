<?php

namespace App\Http\Controllers;

use App\Models\Career;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CareerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Career::where('deleted_at', null)->paginate(10);
    }

    public function list($count)
    {
        $careers = Career::where('deleted_at', null)->get()->chunk($count, function ($career) {
            foreach ($career as $value) {
                return $value;
            }
        });

        return sizeof($careers) > 0 ? $careers[0] : [];
    }

    public function count()
    {
        $count = Career::select('id')
            ->where('deleted_at', null)
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
            'title' => 'required|min:2|string',
            'location' => 'required|min:2|string',
            'description' => 'required|min:2|string',
            'posted' => 'required|date'
        ]);

        $career = Career::create($request->all());
        if ($career !== null) {
            return response("Successfully created!", 201);
        }
        return response("Invalid data!", 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $career = Career::where('id', $id)
            ->where('deleted_at', null)
            ->get()
            ->first();

        if ($career === null) {
            return response("Not Exist", 400);
        }
        return response($career, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $search
     * @return \Illuminate\Http\Response
     */
    public function search($search)
    {
        if (!empty($search)) {
            return DB::table('careers')
                ->where('location', 'like', '%' . $search . '%')
                ->orWhere('title', 'like', '%' . $search . '%')
                ->whereNull('deleted_at')
                ->get();
        }
        return response([], 200);
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
            'title' => 'required|min:2|string',
            'location' => 'required|min:2|string',
            'description' => 'required|min:2|string'
        ]);

        $career = Career::find($id);
        if ($career !== null) {
            if ($career->update($request->all())) {
                return response("Successfully updated!", 200);
            }
        }
        return response("Career does not exist!", 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $career = Career::find($id);
        if ($career !== null && $career->deleted_at === null) {
            $career->delete();
            return response("Successfully deleted!", 200);
        }
        return response("Invalid", 400);
    }
}
