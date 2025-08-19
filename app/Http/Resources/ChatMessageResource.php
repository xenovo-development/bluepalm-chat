<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    public function toArray($request): array
    {
        $meId      = data_get($this, 'me_id');
        $fromAlias = data_get($this, 'from_alias', 'peer');

        return [
            'id'          => (string)$this->id,
            'from'        => ((int)($this->user_id ?? $this->sender_id ?? 0) === (int)$meId) ? 'me' : $fromAlias,
            'text'        => $this->text ?? $this->body ?? $this->content ?? null,
            'at'          => optional($this->created_at)->valueOf(),
            'attachments' => [],
        ];
    }
}
