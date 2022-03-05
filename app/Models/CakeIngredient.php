<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CakeIngredient extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'ingredient_id',
        'cake_component_id',
        'amount'
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredients::class);
    }
}
