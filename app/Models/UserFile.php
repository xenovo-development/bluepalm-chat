<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class UserFile extends Model
{
    use HasFactory;

    protected $fillable=[
        'full_path',
        'name',
        'type',
        ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
