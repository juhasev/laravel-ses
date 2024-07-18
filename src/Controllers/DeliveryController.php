<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes\Controllers;

use Aws\Sns\Exception\InvalidSnsMessageException;
use Aws\Sns\Message;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use JsonException;
use Juhasev\LaravelSes\Contracts\SentEmailContract;
use Juhasev\LaravelSes\DataTransferObjects\MessageContent;
use Juhasev\LaravelSes\Factories\Events\SesDeliveryEvent;
use Juhasev\LaravelSes\ModelResolver;
use Psr\Http\Message\ServerRequestInterface;

class DeliveryController extends BaseController
{
    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public function delivery(ServerRequestInterface $request): JsonResponse
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
                'message' => 'Delivery subscription confirmed'
            ]);
        }

        try {
            $message = MessageContent::fromJson($response['Message']);
        } catch (JsonException $e) {
            Log::error('Failed to decode Message from AWS SES Delivery request ' . $e->getMessage() . ' ' . $e->getTraceAsString());

            return response()->json(['success' => false]);
        }

        $this->persistDelivery($message);

        $this->logMessage("Delivery processed for: " . $message->destination);

        return response()->json([
            'success' => true,
            'message' => 'Delivery notification processed'
        ]);
    }

    /**
     * @throws Exception
     */
    protected function persistDelivery(MessageContent $message): void
    {
        try {
            /** @psalm-suppress UndefinedMethod */
            $sentEmail = ModelResolver::get('SentEmail')::whereMessageId($message->id)
                ->whereDeliveryTracking(true)
                ->firstOrFail();

        } catch (ModelNotFoundException $e) {
            $this->logMessage('Message ID (' . $message->id . ') not found in the SentEmail, this email is likely sent without Laravel SES. Skipping delivery processing...');
            return;
        }

        try {
            $sentEmail->setDeliveredAt($message->deliveryTimestamp);

            $this->sendEvent($sentEmail);

        } catch (Exception $e) {
            Log::error("Failed updating delivered timestamp, got error: " . $e->getMessage() . ' ' . $e->getTraceAsString());
        }
    }

    protected function sendEvent(SentEmailContract $sentEmail): void
    {
        event(new SesDeliveryEvent($sentEmail));
    }
}
