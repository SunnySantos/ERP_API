<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Career;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApplicantController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'career_id' => 'required|exists:careers,id',
            'firstname' => 'required|min:2|string',
            'middlename' => 'nullable|min:2|string',
            'lastname' => 'required|min:2|string',
            'phone_number' => 'required|string|regex:/^9\d{9}$/',
            'email' => 'required|email',
            'resume' => 'required|mimes:zip,pdf|max:10000'
        ]);

        $resume = $request->file('resume');
        $extension = $resume->extension();
        $name = pathinfo($resume->getClientOriginalName(), PATHINFO_FILENAME);
        $resumeName = time() . '_' . $name . '.' . $extension;
        Storage::disk('public')->putFileAs('applicant_file', $resume, $resumeName);
        $newRequest = [
            'career_id' => $request->career_id,
            'firstname' => $request->firstname,
            'middlename' => $request->middlename,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'resume' => $resumeName
        ];
        $applicant = Applicant::create($newRequest);

        if ($applicant !== null) {
            $career = Career::find($request->career_id);
            $career->applied++;
            $career->save();
            return response("Successfully submitted.", 201);
        }
        return response("Invalid data.", 400);
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
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function pending()
    {
        return DB::table('applicants as a')
            ->join('careers as c', 'a.career_id', '=', 'c.id')
            ->select(
                'a.id',
                'a.firstname',
                'a.middlename',
                'a.lastname',
                'a.phone_number',
                'a.email',
                'c.title as position',
                'a.resume'
            )
            ->where('a.hire', '=', 0)
            ->where('a.deleted_at', '=', null)
            ->orderBy('a.id', 'desc')
            ->paginate(10);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function pendingCount()
    {
        $count = Applicant::select('id')
            ->where('hire', 0)
            ->where('deleted_at', null)
            ->count();
        return $count;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatePending(Request $request, $id)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'branch_id' => 'required|exists:branches,id'
        ]);

        $applicant = DB::table('applicants as a')
            ->join('careers as c', 'c.id', '=', 'a.career_id')
            ->select(
                'a.id',
                'a.firstname',
                'a.middlename',
                'a.lastname',
                'a.email',
                'a.phone_number',
                'c.title'
            )
            ->where('a.id', '=', $id)
            ->get()
            ->first();

        $validateEmail = Employee::where('email', $applicant->email)
            ->get()
            ->first();

        if ($applicant !== null) {
            if ($validateEmail === null) {
                $updateApplicant = DB::table('applicants')
                    ->where('id', '=', $id)
                    ->where('deleted_at', '=', null)
                    ->update(['hire' => 1]);

                if ($updateApplicant) {

                    $user = User::create([
                        "username" => strtolower($applicant->lastname) . date('Ymd') . $applicant->id,
                        "password" => bcrypt(strtolower($applicant->lastname)),
                        "role_id" => 3,
                    ]);

                    if ($user) {
                        $employeeData = [
                            "department_id" => $request->department_id,
                            "branch_id" => $request->branch_id,
                            "user_id" => $user->id,
                            "firstname" => $applicant->firstname,
                            "middlename" => $applicant->middlename,
                            "lastname" => $applicant->lastname,
                            "email" => $applicant->email,
                            "phone_number" => $applicant->phone_number,
                            "address" => "",
                            "job" => $applicant->title
                        ];

                        $employee = Employee::create($employeeData);

                        if ($employee !== null) {
                            return response("Successfully updated!", 200);
                        }
                    }
                }
            } else {
                return response("Email is used.", 400);
            }
        }
        return response("Failed to update!", 400);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function hired()
    {
        return DB::table('applicants as a')
            ->join('careers as c', 'a.career_id', '=', 'c.id')
            ->select(
                'a.id',
                'a.firstname',
                'a.middlename',
                'a.lastname',
                'a.phone_number',
                'a.email',
                'c.title as position',
                'a.resume'
            )
            ->where('a.hire', '=', 1)
            ->where('a.deleted_at', '=', null)
            ->orderBy('a.id', 'desc')
            ->paginate(10);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function hiredCount()
    {
        $count = Applicant::select('id')
            ->where('hire', 1)
            ->where('deleted_at', null)
            ->count();
        return $count;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $applicant =  Applicant::find($id);
        if ($applicant !== null) {
            $applicant->delete();
            return response("Successfully deleted!", 200);
        }
        return response("Applicant does not exist!", 400);
    }
}
