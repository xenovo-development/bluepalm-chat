<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Conversation extends Model
{
    use HasFactory;
    protected $fillable=
        [
            'title',
            'status'
        ];

    /**
     * Related users.
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Related messages
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Related marketplace entities
     * @return HasOne
     */
    public function marketplaceEntity():HasOne
    {
        return $this->hasOne(MarketplaceEntity::class);
    }

    /**
     * Related marketplace entities
     * @return HasOne
     */
    public function marketplaceDeal():HasOne
    {
      return  $this->hasOne(MarketplaceDeal::class);
    }
}
