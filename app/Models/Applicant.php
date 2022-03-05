<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Applicant extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'career_id',
        'firstname',
        'middlename',
        'lastname',
        'email',
        'phone_number',
        'hire',
        'resume'
    ];
}
