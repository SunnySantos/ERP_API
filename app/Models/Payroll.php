<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'employee_id',
        'total_hours',
        'start',
        'end',
        'gross',
        'deduction',
        'net'
    ];
}
