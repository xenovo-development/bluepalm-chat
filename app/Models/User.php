<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable;

    protected $guarded = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'username',
        'last_name',
        'email',
        'password',
        'role',
        'phone',
        'passport',
        'approved_at',
        'investment'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function marketplaceEntities(): BelongsToMany
    {
        return $this->belongsToMany(MarketplaceEntity::class);
    }

    public function marketplaceDeals(): BelongsToMany
    {
        return $this->belongsToMany(MarketplaceDeal::class);
    }


    public function masterdata(): HasOne
    {
        return $this->hasOne(Masterdata::class);
    }

    public function subscriptions(): HasOne
    {
        return $this->hasOne(Subscriptions::class);
    }

    public function files()
    {
        return $this->hasMany(UserFile::class);
    }
    public function loyaltybonus()
    {
        return $this->hasOne(LoyaltyBonus::class);
    }

    public function shareholderData()
    {
        return $this->hasOne(ShareholderData::class);
    }

    public function swaps()
    {
        return $this->hasMany(Swap::class);
    }

    public function affiliateLink()
    {
        return $this->hasOne(AffiliateLink::class,'owner_id');
    }

    public function affiliateLinksUsed()
    {
        return $this->belongsToMany(AffiliateLink::class, 'affiliate_link_user');
    }
}
