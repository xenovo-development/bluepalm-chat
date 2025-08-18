<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

class MarketplaceDeal extends Model
{
    use HasFactory;
    protected $fillable  =[
        'share_owner_name',
        'share_dealer_name',
        'product',
        'share',
        'per_amount',
        'total_amount',
        'number'
    ];


    /**
     * Related marketplace entities
     * @return BelongsTo
     */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(MarketplaceEntity::class,'marketplace_entity_id');
    }

    /**
     * Related users
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Related conversation
     * @return BelongsTo
     */
    public function conversation(): BelongsTo
    {
      return  $this->belongsTo(Conversation::class,'conversation_id');
    }
}
