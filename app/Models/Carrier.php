<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Carrier extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'address',
        'phone_number'
    ];
}
