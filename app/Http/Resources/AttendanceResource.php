<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
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
            'employee_id' => $this->employee_id,
            'attend_date' => $this->attend_date,
            'time_in' => $this->time_in,
            'time_in_formatted' => Carbon::createFromFormat('H:i:s', $this->time_in)->format('h:i A'),
            'time_out' => $this->time_out,
            'time_out_formatted' => Carbon::createFromFormat('H:i:s', $this->time_out)->format('h:i A'),
            'employee' => EmployeeResource::make($this->employee),
            'overtime' => OvertimeResource::make($this->overtime)
        ];
    }
}
