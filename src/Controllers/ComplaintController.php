<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes\Controllers;

use Aws\Sns\Exception\InvalidSnsMessageException;
use Aws\Sns\Message;
use GuzzleHttp\Exception\GuzzleException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use JsonException;
use Juhasev\LaravelSes\Contracts\EmailComplaintContract;
use Juhasev\LaravelSes\DataTransferObjects\MessageContent;
use Juhasev\LaravelSes\Factories\Events\SesComplaintEvent;
use Juhasev\LaravelSes\ModelResolver;
use Psr\Http\Message\ServerRequestInterface;

class ComplaintController extends BaseController
{
    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public function complaint(ServerRequestInterface $request): JsonResponse
    {
        $response = Message::fromPsrRequest($request);

        $this->logResult($response);

        if ($this->shouldValidateRequest()) {
            try {
                $this->validateSns($response);
            } catch (InvalidSnsMessageException $e) {
                Log::error('Failed to parse AWS SES Delivery request ' . $e->getMessage() . ' ' . $e->getTraceAsString());

                return response()->json(['success' => false]);
            }
        }

        if ($this->isTopicConfirmation($response)) {
            return response()->json(['success' => true]);
        }

        if ($this->isSubscriptionConfirmation($response)) {
            $this->confirmSubscription($response);

            return response()->json([
                'success' => true,
                'message' => 'Complaint subscription confirmed'
            ]);
        }

        try {
            $message = MessageContent::fromJson($response['Message']);
        } catch (JsonException $e) {
            Log::error('Failed to decode Message from AWS SES Complaint request ' . $e->getMessage() . ' ' . $e->getTraceAsString());

            return response()->json(['success' => false]);
        }

        $this->persistComplaint($message);

        $this->logMessage("Complaint processed for: " . $message->destination);

        return response()->json([
            'success' => true,
            'message' => 'Complaint processed'
        ]);
    }

    /**
     * @throws Exception
     */
    protected function persistComplaint(MessageContent $message): void
    {
        try {
            /** @psalm-suppress UndefinedMethod */
            $sentEmail = ModelResolver::get('SentEmail')::whereMessageId($message->id)
                ->whereComplaintTracking(true)
                ->firstOrFail();

        } catch (ModelNotFoundException $e) {
            $this->logMessage('Message ID (' . $message->id . ') not found in the SentEmail, this email is likely sent without Laravel SES. Skipping delivery processing...');
            return;
        }

        try {
            /** @psalm-suppress UndefinedMethod */
            $emailComplaint = ModelResolver::get('EmailComplaint')::create([
                'sent_email_id' => $sentEmail->id,
                'type' => $message->complaintFeedbackType,
                'complained_at' => $message->mailTimestamp
            ]);

            $this->sendEvent($emailComplaint);

        } catch (Exception $e) {
            Log::error("Failed inserting EmailComplaint, got error: " . $e->getMessage() . ' ' . $e->getTraceAsString());
        }
    }

    protected function sendEvent(EmailComplaintContract $complaint): void
    {
        event(new SesComplaintEvent($complaint));
    }
}
