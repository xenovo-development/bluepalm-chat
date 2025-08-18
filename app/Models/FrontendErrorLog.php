<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrontendErrorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip',
        'username',
        'page_url',
        'json'
    ];
}
