<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriptions extends Model
{
    use HasFactory;

    protected $fillable = [
        'marketplace_below_50',
        'marketplace_above_50',
        'marketplace_opt_out',
        'discovery_route',
        'blue_guide',
        'market_view',
        'insider',
        'announcements',
        'investment',
        'user_id',
    ];
}
