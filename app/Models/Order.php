<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'customer_id',
        'employee_id',
        'branch_id',
        'location',
        'shipping_fee',
        'amount_tendered',
        'status'
    ];


    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
