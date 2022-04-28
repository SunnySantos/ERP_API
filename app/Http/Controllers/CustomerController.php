<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAvatarRequest;
use App\Http\Requests\UpdateBasicInformationRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\VerifyAccountRequest;
use App\Http\Requests\VerifyNameRequest;
use App\Http\Requests\VerifyPhoneRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $customers = Customer::whereNull('deleted_at');

        if ($search !== "null") {
            $customers->where('id', $search)
                ->orWhere('firstname', 'like', '%' . $search . '%')
                ->orWhere('middlename', 'like', '%' . $search . '%')
                ->orWhere('lastname', 'like', '%' . $search . '%')
                ->orWhere('address', 'like', '%' . $search . '%');
        }

        return CustomerResource::collection(
            $customers->orderBy('id', 'DESC')
                ->paginate(10)
        );
    }

    public function exportCSV()
    {
        $filename = 'customers.csv';
        $customers = Customer::all();

        $columns = [
            'id',
            'firstname',
            'middlename',
            'lastname',
            'address',
            'phone_number',
            'email'
        ];

        $file = fopen($filename, 'w');
        fputcsv($file, $columns);

        foreach ($customers as $customer) {
            $row = [];

            foreach ($columns as $column) {
                $row[$column] = $customer[$column];
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

    public function verifyName(VerifyNameRequest $request)
    {
        return response('');
    }

    public function verifyPhone(VerifyPhoneRequest $request)
    {
        return response('');
    }

    public function verifyAccount(VerifyAccountRequest $request)
    {
        return response('');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return CustomerResource::make(
            Customer::whereNull('deleted_at')
                ->find($id)
        );
    }

    public function showBasicInformation($id)
    {
        return CustomerResource::make(
            Customer::whereNull('deleted_at')
                ->where('user_id', $id)
                ->first()
        );
    }

    public function updateBasicInformation(UpdateBasicInformationRequest $request, $id)
    {
        $customer = Customer::where('user_id', $id)
            ->update($request->only([
                'firstname', 'middlename', 'lastname',  'email', 'phone_number', 'address'
            ]));

        if ($customer) return response('Successfully updated.');

        return response('Failed', 400);
    }

    public function updatePassword(UpdatePasswordRequest $request, $id)
    {
        $user = User::where('id', auth()->id())->first();

        if ($request->password === $request->old_password) {
            return response(["errors" => ["password" => "Old and new password are same."]], 422);
        }


        if ($user && Hash::check($request->old_password, $user->password)) {
            $user->password = bcrypt($request->password);
            $user->save();
            return response('Successfully updated.');
        }
        return response(["errors" => ["old_password" => "Wrong old password."]], 422);
    }

    public function updateAvatar(UpdateAvatarRequest $request, $id)
    {
        $customer = auth()->user()->customer;
        // Customer::where('user_id', $id)->first();


        if ($customer) {
            $profile = $request->file('profile');
            $profile_name = pathinfo($profile->hashName(), PATHINFO_FILENAME) . '.' . $profile->extension();
            Storage::disk('public')->putFileAs('customer_img', $profile, $profile_name);
            $customer->profile = $profile_name;
            $customer->save();
            return response("Successfully updated.");
        }
        return response("Failed.", 400);
    }

    public function removeAvatar($id)
    {
        // $customer = Customer::where('user_id', $id)->first();
        $customer = auth()->user()->customer->update([
            'profile' => 'default.jpg'
        ]);
        if ($customer) {
            //     $customer->profile = 'default.jpg';
            //     $customer->save();
            return response('Successfully updated.');
        }
        return response('Failed.', 400);
    }

    public function count()
    {
        return Customer::select('id')
            ->whereNull('deleted_at')
            ->count();
    }
}
