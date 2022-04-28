<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class OvertimeResource extends JsonResource
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
            'attendance_id' => $this->attendance_id,
            'hours' => $this->hours,
            'rate' => $this->rate,
            'attend_date' => $this->attendance->attend_date,
            'attend_date_formatted' => Carbon::parse($this->attendance->attend_date)->format('M d, Y')
        ];
    }
}
