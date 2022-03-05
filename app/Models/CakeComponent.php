<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CakeComponent extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'cake_model_id',
        'name',
        'size',
        'shape',
        'category',
        'cost'
    ];

    public function cake_model()
    {
        return $this->belongsTo(CakeModel::class, 'cake_model_id');
    }

    public function cake_ingredients() {
        return $this->hasMany(CakeIngredient::class);
    }
}
