<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'employee_id',
        'attend_date',
        'time_in',
        'time_out',
        'overtime'
    ];
}
