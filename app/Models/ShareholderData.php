<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShareholderData extends Model
{
    use HasFactory;

    protected $fillable=[
        'invest',
        'invest_from',
        'investbonus',
        'jahr',
        'lastyear',
        'neingesetzt',
        'number',
        'registerdate',
        'rendite',
        'rendite_bestaetigt',
        'rendite_ly',
        'rendite_prozent',
        'showinfolay',
        'sperre',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
