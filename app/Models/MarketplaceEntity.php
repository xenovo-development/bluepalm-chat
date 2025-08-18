<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class MarketplaceEntity extends Model
{
    use HasFactory;
    protected $fillable=
        [
            'first_name',
            'last_name',
            'number',
            'intent',
            'product',
            'share_num',
            'share_price',
            'total_price',
            'bank_type',
            'account_num',
            'account_type',
            'routing_num',
            'iban_num',
            'bic_swift_num',
            'status',
            'approved_at',
            'user_id',
            'days'
        ];

    /**
     * Get related user.
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get related conversations
     * @return BelongsTo
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Related deals
     * @return HasMany
     */
    public function deals(): HasMany
    {
        return $this->hasMany(MarketplaceDeal::class);
    }

}
