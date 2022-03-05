<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransfer extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'branch_sender_id',
        'branch_receiver_id',
        'stock_id',
        'quantity',
        'received'
    ];

    public function stock()
    {
        return $this->hasOne(Stock::class, 'branch_id', 'branch_receiver_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch_sender()
    {
        return $this->belongsTo(Branch::class, 'branch_sender_id');
    }

    public function branch_receiver()
    {
        return $this->belongsTo(Branch::class, 'branch_receiver_id');
    }
}
