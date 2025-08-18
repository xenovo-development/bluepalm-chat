<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'link',
        'owner_id',
        'times_registered',
        'status',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    public function usersReferred()
    {
        return $this->belongsToMany(User::class, 'affiliate_link_user');
    }
    public function shareholdersReferred()
    {
        return $this->belongsToMany(User::class, 'affiliate_link_user')
            ->where('users.role', 'Shareholder');
    }
}
