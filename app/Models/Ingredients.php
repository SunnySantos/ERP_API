<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredients extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'branch_id',
        'image',
        'name',
        'unit',
        'stock',
        'low_level',
        'cost',
        'category'
    ];
}
