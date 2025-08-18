<?php

namespace App\Services;


use App\Jobs\MarketplaceResponse;
use App\Models\Conversation;
use App\Models\MarketplaceEntity;
use App\Models\User;
use Illuminate\Http\Request;

class MarketplaceMessagesService
{
    private ConversationService $conversationService;
    public function __construct()
    {
        $this->conversationService =  resolve(ConversationService::class);
    }

    /**
     * Start the marketplace conversations and send a delayed response from admin account. Return the conversation as a response.
     * @param MarketplaceEntity $entity
     * @param User $user
     * @return mixed
     */
    public function startMpCreateConversation(MarketplaceEntity $entity, User $user): mixed
    {
        $conversationRequest = new Request();
        $conversationRequest->merge([
            'title' => 'Marketplace Transaction ID=' . $entity['number'],
            'user_id' => $user['id'],
            'receiver_id'=>1,
            'is_read' => 1,
            'body' => "Dear Blue Palm Group,\n\n" .
                "I have confirmed that preference shares " . $entity['product'] . "%" .
                " will be available for trading on the Marketplace for " . $entity['days'] . " days, with " .
                $entity['share_num'] . " shares at €" . number_format($entity['share_price'], 0, '.', ',') . " per share and a total transaction of " .
                "€" . number_format($entity['total_price'], 0, '.', ',') . "." . "\n\n" . "Best regards.",
        ]);

        $conversation = $this->conversationService->store($conversationRequest);
        $conversation = Conversation::with('messages')->find($conversation);
        $messageDetails = [
            'body' => "Dear investor,\n\n" .
                "We have received your share transaction you made on the Marketplace. Your transaction is currently in the evaluation process. You will be informed as soon as the procedure is finalized.\n",
            'sender_name' => 'Blue Palm',
            'user_id' => 0,
        ];
        $conversation->marketplaceEntity()->save($entity);
        MarketplaceResponse::dispatch($conversation, $messageDetails)->delay(now()->addSeconds(75));
        return $conversation;
    }

    /**
     * Start the marketplace conversations and send a delayed response from admin account.
     *
     * @param MarketplaceEntity $entity
     * @param User $user
     * @return mixed
     */
    public function startMpInterestConversation(MarketplaceEntity $entity, User $user): mixed
    {
        $conversationRequest = new Request();
        $userIntent = $entity->intent === 'Sell' ? 'purchasing' : 'selling';
        $conversationRequest->merge([
            'title' => 'Marketplace Transaction Interest ID=' . $entity['number'],
            'user_id' => $user['id'],
            'receiver_id'=>1,
            'body' => "Dear Blue Palm Group,\n\n" .
                "I would like to inform you that I am interested in" . " " . $userIntent . " " . " the share, the details of which are given below. I request to be informed of the necessary information and procedures for further proceedings.\n\n" .
                "Preference share:  " . $entity['product'] . "%\n" . "Share amount: " . $entity['share_num'] . "\n" . "Price per share: €" . number_format($entity['share_price'], 0, '.', ',') . "\n" .
                "Total transaction value: €" . number_format($entity['total_price'], 0, '.', ',') . ".\n\n" .
                "Best regards.",
            'is_read' => 0
        ]);

        $conversationId = $this->conversationService->store($conversationRequest);
        return Conversation::with('messages')->find($conversationId);
    }

    /**
     * Send the user deal message.
     *
     * @param Conversation $conversation
     * @param string $username
     * @return void
     */
    public function sendDealMessage(Conversation $conversation ,string $username): void
    {
        $request = new Request();
        $request->merge(
            ['conversation_id' => $conversation->id,
                'body' => 'Dear ' .' '. $username.',

Your purchase request for the share offered for sale on Blue Palm Marketplace has been evaluated. You will be notified of the outcome of the evaluation process as soon as possible.

We thank you for this new step of cooperation that will further strengthen your place in our success.

Best regards,
Blue Palm Group',
                'user_id' => auth()->user()['id'], 'sender_name' => auth()->user()['username']]
        );
        $this->conversationService->update($request);
    }

    /**
     * Stars a conversation between user and Admin account regarding swap request.
     *
     * @param float|string $amount
     * @param $userId
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
     */
    public function startSwapConversation(float|string $amount,$userId): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
    {
        $conversationRequest = new Request();

        $title = 'Swap request, Package = ' . $amount;
        $message = "I would like to enroll in the Blue Palm Swap Membership and select the " . $amount ;

        $conversationRequest->merge([
            'title' => $title,
            'user_id' => $userId,
            'receiver_id'=>1,
            'body' => "Dear Blue Palm Group,\n\n" .$message. "\n\n". "Best Regards.",
            'is_read' => 0
        ]);

        $conversationResponse = $this->conversationService->storeAPIRequest($conversationRequest);
        $conversation = $conversationResponse->getData()->conversation;
        return Conversation::with('messages')->find($conversation->id);
    }


    /**
     * Sends a message regarding 2024 Annual Report
     *
     * @param $userId
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
     */
    public function sendSwapMessage($userId): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
    {
        $conversationRequest = new Request();
        $link = '<a href="/financial-statements-2024" target="_blank">2024 Annual Report Now Available.</a>';
        $title = ' 2024 Annual Report Now Available';
        $message = "Dear Shareholders,

We are pleased to share with you the Blue Palm 2024 Annual Report. This comprehensive overview highlights the key milestones, challenges, and strategic developments that have shaped our year.

As we look ahead, we remain focused on unlocking new opportunities, delivering consistent returns, and enhancing the quality of the products and experiences we offer. None of this would be possible without the continued confidence of our shareholders and the loyalty of our customers.

Thank you for being a vital part of our journey. We are excited about what lies ahead and look forward to achieving even more - together.

You can view the 2024 Annual Report via the link below.

$link
" ;

        $conversationRequest->merge([
            'title' => $title,
            'user_id' =>1 ,
            'receiver_id'=>$userId,
            'body' => $message,
            'is_read' => 0
        ]);

        $conversationId = $this->conversationService->store($conversationRequest);
        return Conversation::with('messages')->find($conversationId);
    }

    /**
     * Sends an automated message to a swap conversation
     *
     * @param $conversationId
     * @param $userName
     * @param $receiverId
     * @return void
     */
    public function sendAutomatedSwapMessage($conversationId,$userName,$receiverId):void
    {
        $request = new Request();
        $conversation = Conversation::with('messages')->find($conversationId);

        $request->merge(
            ['conversation_id' => $conversation->id,
                'body' => 'Dear ' .' '. $userName.',

Thank you for reaching out and expressing your interest in the Blue Palm Swap Membership Program. We have received your message and appreciate your engagement.

We will follow up with you shortly to provide further details regarding the next steps of the process.',
                'user_id' => 1,
                'sender_name' =>'Blue Palm',
                'receiver_id'=>$receiverId,
                'is_read' => 0
            ]
        );
        $this->conversationService->update($request);
    }
}
