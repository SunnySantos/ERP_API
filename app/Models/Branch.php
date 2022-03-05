<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'address',
        'phone_number',
        'started_at'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}