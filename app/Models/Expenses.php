<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expenses extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'branch_id',
        'name',
        'amount',
        'approve'
    ];
}
