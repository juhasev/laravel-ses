<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes\Controllers;

use Aws\Sns\Exception\InvalidSnsMessageException;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;

class BaseController extends Controller
{
    /**
     * @throws InvalidSnsMessageException
     */
    protected function validateSns(Message $message): void
    {
        $validator = new MessageValidator();

        $validator->validate($message);
    }

    /**
     * @throws GuzzleException
     * @throws RuntimeException
     */
    protected function confirmSubscription(Message $message): void
    {
        if (! isset($message['SubscribeURL'])) {
            throw new RuntimeException('Failed to confirm subscription because of missing SubscribeURL param: '. json_encode($message));
        }

        $response = Http::get($message['SubscribeURL']);

        if ($response->status() !== 200) {
            throw new RuntimeException('Subscription confirmation failed: '. json_encode($message));
        }

        $this->logMessage("Subscribed to (".$message['TopicArn'].") using GET Request " . $message['SubscribeURL']);
    }

    protected function isSubscriptionConfirmation(Message $message): bool
    {
        if ($message['Type'] === 'SubscriptionConfirmation') {
            $this->logMessage("Received subscription confirmation: ". $message['TopicArn']);

            return true;
        }

        return false;
    }

    protected function isTopicConfirmation(Message $message): bool
    {
        if (
            $message['Type'] === 'Notification' &&
            Str::contains($message['Message'], "Successfully validated SNS topic")
        ) {
            $this->logMessage('SNS Topic Validated: ' . $message['TopicArn']);

            return true;
        }

        return false;
    }

    protected function logMessage(string $message): void
    {
        if ($this->debug()) {
            Log::debug(config('laravelses.log_prefix') . ": " . $message);
        }
    }

    /**
     * @throws JsonException
     */
    protected function logResult(string|object|array|null $content): void
    {
        if ($this->debug()) {
            if (is_array($content) || is_object($content)) {
                $content = json_encode($content, JSON_THROW_ON_ERROR);
            }

            Log::debug("RAW SES REQUEST BODY: " . $content);
        }
    }

    protected function debug(): bool
    {
        return config('laravelses.debug') === true;
    }

    protected function shouldValidateRequest(): bool
    {
        return config('laravelses.aws_sns_validator') === true;
    }
}
