<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'cake_project_id',
        'image',
        'file_extension',
        'name',
        'description',
        'category_id',
        'price',
        'cost'
    ];

    public function stock()
    {
        return $this->hasOne(Stock::class, 'product_id');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
