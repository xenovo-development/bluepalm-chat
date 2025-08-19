<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class UnifiedConversationResource extends JsonResource
{
    public function toArray($request): array
    {
        $model  = $this->resource['model'];
        $source = $this->resource['source']; // 'local' or 'convolite'
        $meId   = $this->resource['me_id'];

        // Karşı taraf
        $peer = optional($model->users)->firstWhere('id', '!=', $meId)
            ?: optional($model->users)->first();

        $peerName = optional($peer)->name ?? 'Unknown';
        $title    = $peer->name . ' - '. $model->title ?? $peerName;

        // Last message & time
        $last = optional($model->messages)->sortByDesc('created_at')->first();

        // chat_type logic
        $chatType = 'active';
        if ($source === 'local') {
            $chatType = 'archive';
        } else {
            if (optional($model->updated_at)->lt(now()->subDays(30))) {
                $chatType = 'archive';
            }
        }

        $initials = collect(explode(' ', $peerName))
            ->filter()
            ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
            ->join('');

        return [
            'id'            => (int) $model->id,
            'name'          => $title,
            'initials'      => $initials,
            'source'        => $source,
            'avatar'        => optional($peer)->profile_photo_url
                ?? optional($peer)->avatar_url
                    ?? '',
            'lastMessage'   => $last ? ($last->text ?? $last->body ?? $last->content ?? null) : null,
            'lastMessageAt' => $last && $last->created_at ? $last->created_at->valueOf() : null,
            'unreadCount'   => 0,
            'typing'        => false,
            'archived'      => $chatType === 'archive',
            'chat_type'     => $chatType,
        ];
    }
}
