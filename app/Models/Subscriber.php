<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscriber extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'channel_id',
        'user_id'
    ];

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function subscribable()
    {
        return $this->morphTo();
    }
}
