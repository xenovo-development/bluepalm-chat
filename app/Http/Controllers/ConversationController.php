<?php

namespace App\Http\Controllers;

use App\Http\Requests\Conversation\StoreConversationRequest;
use App\Http\Requests\Conversation\UpdateConversationRequest;
use App\Http\Resources\UnifiedConversationResource;
use App\Http\Resources\ChatMessageResource;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;

class ConversationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $me      = $request->user();
        $perPage = 12;
        $page    = (int) $request->query('page', 1);

        $convolite = \Emincmg\ConvoLite\Models\Conversation::query()
            ->whereHas('users', fn ($q) => $q->where('id', $me->id))
            ->with([
                'users:id,username as name',
                'messages' => fn ($q) => $q->latest()->limit(1),
            ])
            ->get();

        $convoliteIds = $convolite->pluck('id')->all();

        $local = \App\Models\Conversation::query()
            ->whereHas('users', fn ($q) => $q->where('id', $me->id))
            ->when(count($convoliteIds) > 0, fn ($q) => $q->whereNotIn('id', $convoliteIds))
            ->with([
                'users:id,username as name',
                'messages' => fn ($q) => $q->latest()->limit(1),
            ])
            ->get();

        $threads = collect()
            ->concat($convolite->map(fn ($c) => (new UnifiedConversationResource([
                'model'  => $c,
                'source' => 'convolite',
                'me_id'  => $me->id,
            ]))->toArray($request)))
            ->concat($local->map(fn ($c) => (new UnifiedConversationResource([
                'model'  => $c,
                'source' => 'local',
                'me_id'  => $me->id,
            ]))->toArray($request)))
            ->unique('id')
            ->sortByDesc(fn ($t) => $t['lastMessageAt'] ?? 0)
            ->values();

        $total     = $threads->count();
        $pageItems = $threads->forPage($page, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $pageItems,
            $total,
            $perPage,
            $page,
            [
                'path'  => url()->current(),
                'query' => $request->query(),
            ]
        );

        return Inertia::render('Chat', [
            'conversations' => $paginator,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConversationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConversationRequest $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function messages(Request $request, string $source, int $id)
    {
        $request->validate([
            'page'     => ['sometimes','integer','min:1'],
            'per_page' => ['sometimes','integer','between:1,100'],
        ]);

        $me      = $request->user();
        $perPage = (int)($request->input('per_page', 30));

        // Resolve model by source
        $modelClass = $source === 'convolite'
            ? \Emincmg\ConvoLite\Models\Conversation::class
            : \App\Models\Conversation::class;

        // Ensure user belongs to the conversation
        $conversation = $modelClass::query()
            ->whereKey($id)
            ->whereHas('users', fn($q) => $q->where('id', $me->id))
            ->with(['users:id,username']) // for resolving peer
            ->firstOrFail();

        // Identify peer for "from" mapping
        $peer = $conversation->users->firstWhere('id', '!=', $me->id)
            ?: $conversation->users->first();

        // Load paginated messages (newest first)
        $messages = $conversation->messages()
            ->with([]) // add relations like 'attachments' if you have them per-message
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->appends($request->query());

        // Pass context to each message for 'from' + optional alias
        $messages->getCollection()->transform(function ($m) use ($me, $peer) {
            $m->setAttribute('me_id', $me->id);
            $m->setAttribute('from_alias', optional($peer)->username ?? optional($peer)->name ?? 'peer');
            return $m;
        });

        // (Optional) mark as read at conversation level if you track it there
        // if (method_exists($conversation, 'readBy')) {
        //     $conversation->readBy()->syncWithoutDetaching([$me->id => ['read_at' => now()]]);
        // }

        return ChatMessageResource::collection($messages)
            ->additional([
                'meta' => [
                    'conversation_id' => (int)$conversation->id,
                    'source'          => $source,
                ],
            ]);
    }
}
