<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Channel extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name'
    ];

    public function subscribers()
    {
        return $this->hasMany(Subscriber::class, 'channel_id', 'id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'channel_id');
    }

    public function message()
    {
        return $this->hasOne(Message::class, 'channel_id');
    }
}
