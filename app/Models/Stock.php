<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'branch_id',
        'product_id',
        'quantity',
        'minimum'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
