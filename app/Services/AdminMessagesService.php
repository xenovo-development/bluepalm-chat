<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class AdminMessagesService
{
    /**
     * Returns the stock offer message creation request.
     * @param User $user
     * @param int $diffInDays
     * @return Request
     */
    public function returnStockOfferReminderMessageRequest(User $user, int $diffInDays): Request
    {
        $currentConversation = $user->conversations()->where('title', '=', $diffInDays . ' days to Stock Offer expiration',)->first();
        if (!$currentConversation) {
            $conversationRequest = new Request();
            $stockOfferLink = '<a href="/stock-offer" target="_blank">Please click this link to go to the stock offer page.</a>';
            $messageBody = "Dear " . $user->first_name . ' ' . $user->last_name . ",\n\n" .

                "We wanted to remind you that you have " . $diffInDays . " days left to evaluate our stock offer. Your evaluation and decision is critical to our mutual progress, and we want to thank you in advance for taking the time to review it.

We would like to inform you that at the end of the " . $diffInDays . " days, while our stock offer remains valid, if you have not made a decision, your participation  information that allows you to access the system will expire.

Should you require further information or assistance, please do not hesitate to contact us.

$stockOfferLink

Thank you for your continued partnership and confidence in our organization.

Best regards,
Blue Palm Investor Support Team
";
            $conversationRequest->merge([
                'title' => $diffInDays . ' days to Stock Offer expiration',
                'user_id' => 1,
                'receiver_id' => $user->id,
                'body' => $messageBody,
            ]);
            return $conversationRequest;
        }
    }

    /**
     * Return greeting message request.
     * @param User $user
     * @return Request
     */
    public function returnGreetingMessageRequest(User $user): Request
    {
        $conversationRequest = new Request();

        if ($user->role === 'Shareholder') {
            $messageBody = "Dear " . $user->first_name . ' ' . $user->last_name . ",\n\n" .

                "We are very happy to share with you this informative message with some important developments about Blue Palm Group.

Blue Palm has gained a brand new look with its renewed, modern interface and rich content including many current Blue Palm and sector news.Here is all the news from a journey full of successes we have achieved together.

Best regards,
Blue Palm Support Team
";
        } else {
            $stockOfferLink = '<a href="/stock-offer" target="_blank">Stock Offer</a>';
            $messageBody = "Dear " . $user->first_name . ' ' . $user->last_name . ",\n\n" .

                "Welcome to Blue Palm,

We are very happy to see you among us and we look forward to working with you in building the future of Blue Palm.

Our website, where cultural city news from Vienna, Istanbul, Leipzig and Northern Cyprus, the four important pillars of Blue Palm's business network, Blue Palm's success stories, important developments and future plans are shared, has gained a new and useful interface to keep you informed about the Blue Palm world. This website, with its different categories and up-to-date news content, serves as a guide to understand Blue Palm's vision and participate in its investments.

    " . $stockOfferLink . " section will direct you to Blue Palm's three-year development plan from 2024 to 2026. You will have 40 days to review all the details of this development announcement and to make a decision on the stock offer we have presented to you. If you accept our offer and decide to become a Blue Palm shareholder, you must sign the form at the end of the announcement and upload the signed form to the relevant section in the same place. We want you to know that if you do not accept our offer, you will lose your access to the site and all your data will be deleted. However, we believe in our possibility of cooperation much more than this possibility.

Now, we continue our journey with the power of knowing that you have a share in our vision of the future.


Best regards,
Blue Palm Investor Support Team
";
        }

        $conversationRequest->merge([
            'title' => 'Welcome to Blue Palm',
            'user_id' => 1,
            'receiver_id' => $user->id,
            'body' => $messageBody,
            'is_read' => 0
        ]);
        return $conversationRequest;
    }

    /**
     * Return greeting message request.
     * @param User $user
     * @return Request
     */
    public function newWebsiteMessage(User $user): Request
    {
        $conversationRequest = new Request();
        $messageBody = "Dear " . $user->first_name . ' ' . $user->last_name . ",\n\n" .

            "We are pleased to announce that we have made some improvements to the Blue Palm website.

Blue Palm has a brand new look with a refreshed, modern interface. We have made significant improvements to both the back-end and front-end, with a robust software infrastructure. Ensuring the highest possible standards of site speed and security has been at the core of our development approach. In addition, to keep your communication channel with Blue Palm always open, we have implemented a messaging system where you can send your messages at any time. Starting in April, we are also making the Blue Palm Exclusive category one of the most important sections of our new website, offering a carefully curated selection of insider news, in-depth reports and vision plans on Blue Palm's past, present and future journey.

Best regards,
Blue Palm Support Team
";
        $conversationRequest->merge([
            'title' => 'Re-launch of the Blue Palm website',
            'user_id' => 1,
            'receiver_id' => $user->id,
            'body' => $messageBody,
            'is_read' => 0
        ]);
        return $conversationRequest;
    }

    /**
     * Returns the shareholder greeting message creation request.
     *
     * @param User $user
     * @return Request
     */
    public function returnShareholderGreetingMessageRequest(User $user): Request
    {
        $conversationRequest = new Request();
        $shareholderLink = '<a href="/shareholder" target="_blank">Shareholder</a>';
        $marketplaceLink = '<a href="/marketplace/market" target="_blank">Marketplace</a>';
        $exclusiveLink = '<a href="/exclusive" target="_blank">Exclusive</a>';
        $subscriptionsLink = '<a href="/shareholder/subscriptions" target="_blank">https://bluepalm.group/shareholder/subscriptions</a>';
        $messageBody = "Dear " . $user->first_name . ' ' . $user->last_name . ",\n\n" .

            "We are pleased to announce the new developments regarding your official achievement of Shareholder status at Blue Palm.

Over the next 14 days, all your account information will be seamlessly integrated into our system, providing you with a consolidated view of your investments.

In the " . $shareholderLink . " category, you can view investment details, including accumulated return, investment and account balance, as well as related reports, data and documents.

Through the " . $marketplaceLink . ", you can now manage your investment transactions, such as buying and selling shares or tracking trades. Additionally, " . $exclusiveLink . " news categories are available, providing more comprehensive reports and customized information tailored specifically to your interests.

To further personalize your shareholder experience and ensure you receive the most relevant updates and information, we kindly ask you to customize your preferences by clicking on the link below:

" . $subscriptionsLink . "

If you have any questions or need assistance at any point, please feel free to reach out to us via message.

Congratulations once again on the threshold of the beginning of a journey full of opportunities and collective success.

Best regards,
Blue Palm Shareholder Relations

            ";
        $conversationRequest->merge([
            'title' => "Your new Blue Palm Shareholder status: Information about your exclusive benefits",
            'user_id' => 1,
            'receiver_id' => $user->id,
            'body' => $messageBody,
        ]);
        return $conversationRequest;
    }


    /**
     * Return marketplace publish conversation creation request.
     *
     * @param User $user
     * @return Request
     */
    public function returnPublishConversationRequest(User $user): Request
    {

       $marketplaceLink = '<a href="https://bluepalm.group/marketplace/market" target="_blank">Marketplace</a>';
        $conversationRequest = new Request();
        $messageBody = "Dear " . $user->first_name . ' ' . $user->last_name . ",\n\n" .

            "We are pleased to announce a significant opportunity for our valued shareholder that has recently arisen on Marketplace. Please click the button below to learn the details of our offer now. \n\n".$marketplaceLink . "

Best regards,
Blue Palm Shareholder Assistance Team
";
        $conversationRequest->merge([
            'title' => 'Marketplace significant share offer',
            'user_id' => 1,
            'receiver_id' => $user->id,
            'body' => $messageBody,
            'is_read' => 0
        ]);
        return $conversationRequest;
    }

}

