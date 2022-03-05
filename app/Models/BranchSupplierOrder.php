<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BranchSupplierOrder extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'supplier_id',
        'branch_id',
        'checked_out',
        'processed',
        'delivered',
        'amount_tendered'
    ];
}
