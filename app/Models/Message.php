<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;

class Message extends Model
{
    use HasFactory, Searchable;

    protected $fillable=[
        'body',
        'user_id',
        'conversation_id',
        'filename',
        'full_path',
        'sender_name',
        'is_read',
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $this->load('conversation');
        return [
            'body' => $this->body,
            'sender_name' => $this->sender_name,
            'created_at' => $this->created_at,
            'conversations.title'=>''
        ];
    }

    /**
     * Related conversation.
     *
     * @return BelongsTo
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Related user.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
