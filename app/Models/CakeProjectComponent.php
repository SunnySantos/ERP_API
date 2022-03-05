<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CakeProjectComponent extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'cake_project_id',
        'cake_component_id',
        'uuid',
        'posX',
        'posY',
        'posZ'
    ];

    public function project()
    {
        return $this->belongsTo(CakeProject::class);
    }

    public function cake_component()
    {
        return $this->belongsTo(CakeComponent::class);
    }
}
