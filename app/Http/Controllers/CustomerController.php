<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Customer::whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->paginate(10);
    }

    public function exportCSV()
    {
        $filename = 'customers.csv';
        $customers = Customer::all();

        $columns = array_map('current', DB::select('DESCRIBE customers'));

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

    public function verifyName(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string|regex:/^[a-zA-ZñÑ\s]+$/',
            'middlename' => 'nullable|string|regex:/^[a-zA-ZñÑ\s]+$/',
            'lastname' => 'required|string|regex:/^[a-zA-ZñÑ\s]+$/'
            // 'address' => 'required|string',
            // 'phone_number' => 'required|string|unique:customers|regex:/^9\d{9}$/',
            // 'username' => 'required|min:8|string|unique:users',
            // 'password' => 'required|min:8|string|confirmed',
        ]);
        return response('', 200);
    }

    public function verifyPhone(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|unique:customers,phone_number|regex:/^9\d{9}$/',
            'email' => 'required|email|unique:customers,email'
        ]);
        return response('', 200);
    }

    public function verifyAccount(Request $request)
    {
        $request->validate([
            'username' => 'required|min:8|string|unique:users',
            'password' => 'required|min:8|string|confirmed',
        ]);
        return response('', 200);
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Customer::select(
            'id',
            'user_id',
            'firstname',
            'middlename',
            'lastname',
            'address',
            'phone_number',
            'email',
            'profile'
        )
            ->where('deleted_at', null)
            ->where('user_id', $id)
            ->get()
            ->first();
    }

    public function showBasicInformation($id)
    {
        return Customer::select(
            'firstname',
            'middlename',
            'lastname',
            'email',
            'phone_number',
            'address',
            'profile'
        )
            ->whereNull('deleted_at')
            ->where('user_id', $id)
            ->get()
            ->first();
    }

    public function updateBasicInformation(Request $request, $id)
    {
        $request->validate([
            'firstname' => 'required|string|regex:/^[a-zA-Z\s]*$/',
            'middlename' => 'nullable|string|regex:/^[a-zA-Z\s]*$/',
            'lastname' => 'required|string|regex:/^[a-zA-Z\s]*$/',
            'email' => ['required', 'email', Rule::unique("customers", "email")->ignore($id, 'user_id')],
            'phone_number' => ['required', 'string', 'regex:/^9\d{9}$/', Rule::unique("customers", "phone_number")->ignore($id, 'user_id')],
            'address' => 'required|string'
        ]);

        $customer = Customer::where('user_id', $id)
            ->get()
            ->first();

        if ($customer !== null) {
            if ($customer->update($request->all())) {
                return response('Successfully updated.', 200);
            }
        }

        return response('Customer does not exist.', 400);
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'old_password' => 'required|string|min:8',
            'password' => 'required|min:8|string|confirmed'
        ]);

        $customer = User::where('id', $id)->first();

        if ($request->password === $request->old_password) {
            return response(["errors" => ["password" => "Old and new password are same."]], 422);
        }


        if ($customer !== null && Hash::check($request->old_password, $customer->password)) {
            $customer->password = bcrypt($request->password);
            $customer->save();
            return response('Successfully updated.', 200);
        }
        return response(["errors" => ["old_password" => "Wrong old password."]], 422);
    }

    public function updateAvatar(Request $request, $id)
    {
        $request->validate([
            'profile' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $customer = Customer::where('user_id', $id)->first();

        if ($customer !== null) {
            $profile = $request->file('profile');
            $extension = $profile->extension();
            $name = pathinfo($profile->getClientOriginalName(), PATHINFO_FILENAME);
            $profile_name = time() . '_' . $name . '.' . $extension;
            Storage::disk('public')->putFileAs('customer_img', $profile, $profile_name);
            $customer->profile = $profile_name;
            $customer->save();
            return response("Successfully updated.", 200);
        }
        return response("Customer does not exist.", 400);
    }

    public function removeAvatar($id)
    {
        $customer = Customer::where('user_id', $id)->first();
        if ($customer !== null) {
            $customer->profile = 'default.jpg';
            $customer->save();
            return response('Successfully updated.', 200);
        }
        return response('Customer does not exist.', 400);
    }

    public function count()
    {
        $count = Customer::select('id')
            ->whereNull('deleted_at')
            ->count();
        return $count;
    }

    public function search($key)
    {
        return Customer::where('id', $key)
            ->orWhere('firstname', 'like', '%' . $key . '%')
            ->orWhere('middlename', 'like', '%' . $key . '%')
            ->orWhere('lastname', 'like', '%' . $key . '%')
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->paginate(10);
    }
}
