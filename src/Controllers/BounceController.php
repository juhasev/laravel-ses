<?php

namespace Juhasev\LaravelSes\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Juhasev\LaravelSes\Models\EmailBounce;
use Juhasev\LaravelSes\Models\SentEmail;
use Psr\Http\Message\ServerRequestInterface;

class BounceController extends BaseController
{
    /**
     * Bounce controller
     *
     * @param ServerRequestInterface $request
     * @return JsonResponse
     */

    public function bounce(ServerRequestInterface $request)
    {
        $this->validateSns($request);

        $result = json_decode(request()->getContent());

        $this->logResult($request);

        if ($this->isSubscriptionConfirmation($result)) {

            $this->confirmSubscription($result);

            return response()->json([
                'success' => true,
                'message' => 'Delivery subscription confirmed'
            ]);
        }

        $message = json_decode($result->Message);

        $this->persistBounce($message);

        $this->logMessage("Bounce processed for: " . $message->mail->destination[0]);

        return response()->json([
            'success' => true,
            'message' => 'Delivery subscription confirmed'
        ]);
    }

    /**
     * Persis bounce
     *
     * @param $message
     */

    protected function persistBounce($message): void
    {
        if ($this->debug()) return;

        $messageId = $this->parseMessageId($message);

        try {
            $sentEmail = SentEmail::whereMessageId($messageId)
                ->whereBounceTracking(true)
                ->firstOrFail();

            EmailBounce::create([
                'message_id' => $messageId,
                'sent_email_id' => $sentEmail->id,
                'type' => $message->bounce->bounceType,
                'email' => $message->mail->destination[0],
                'bounced_at' => Carbon::parse($message->mail->timestamp)
            ]);

        } catch (ModelNotFoundException $e) {

            Log::error('Could not find laravel_ses_email_bounces table. Did you run migrations?');
        }
    }
}
