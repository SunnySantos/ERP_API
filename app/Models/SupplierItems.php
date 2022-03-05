<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierItems extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'supplier_id',
        'image',
        'name',
        'description',
        'category',
        'price',
        'stock',
        'unit'
    ];
}
