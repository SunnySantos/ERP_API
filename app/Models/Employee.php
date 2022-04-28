<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'department_id',
        'user_id',
        'branch_id',
        'firstname',
        'middlename',
        'lastname',
        'address',
        'sex',
        'birth',
        'marital_status',
        'phone_number',
        'position_id',
        'hire',
        'photo'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // public function branch()
    // {
    //     return $this->hasOne(Branch::class);
    // }
}
