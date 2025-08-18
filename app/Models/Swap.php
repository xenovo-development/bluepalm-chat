<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Swap extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'user_id',
        'all_profit',
        'custom_profit',
        'amount',
        'notified',
        'conversation_id',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
