<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CakeProject extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'name',
        'description'
    ];

    public function cake_project_components()
    {
        return $this->hasMany(CakeProjectComponent::class);
    }

    public function cake_component()
    {
        return $this->belongsTo(CakeComponent::class);
    }
}
