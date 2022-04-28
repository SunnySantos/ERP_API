<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'department_id' => $this->department_id,
            'user_id' => $this->user_id,
            'branch_id' => $this->branch_id,
            'position_id' => $this->position_id,
            'firstname' => $this->firstname,
            'middlename' => $this->middlename,
            'lastname' => $this->lastname,
            'address' => $this->address,
            'sex' => $this->sex,
            'marital_status' => $this->marital_status,
            'birth' => $this->birth,
            'phone_number' => $this->phone_number,
            'hire' => $this->hire,
            'photo' => $this->photo,
            'department' => $this->department,
            'user' => $this->user,
            'branch' => $this->branch,
            'position' => $this->position
        ];
    }
}
