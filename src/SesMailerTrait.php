<?php

namespace Juhasev\LaravelSes;

use Closure;
use Illuminate\Support\Carbon;
use Juhasev\LaravelSes\Contracts\SentEmailContract;
use Juhasev\LaravelSes\Exceptions\LaravelSesTooManyRecipientsException;
use Juhasev\LaravelSes\Factories\Events\SesSentEvent;
use Symfony\Component\Mime\Email;

/**
 * @method getBatchId()
 * @mixin TrackingTrait
 * @psalm-suppress UndefinedMethod
 */
trait SesMailerTrait
{
    private ?Closure $initMessageCallback = null;

    /**
     * Init message (this is always called)
     * Creates database entry for the sent email
     *
     * @throws LaravelSesTooManyRecipientsException
     */
    public function initMessage(Email $message): SentEmailContract
    {
        $this->checkNumberOfRecipients($message);

        /** @psalm-suppress UndefinedMethod */
        $sentEmailModel = ModelResolver::get('SentEmail')::create([
            'message_id' => $message->generateMessageId(),
            'email' => $message->getTo()[0]->getAddress(),
            'batch_id' => $this->getBatchId(),
            'sent_at' => Carbon::now(),
            'delivery_tracking' => $this->deliveryTracking,
            'complaint_tracking' => $this->complaintTracking,
            'bounce_tracking' => $this->bounceTracking
        ]);

        if (($callback = $this->initMessageCallback) !== null) {
            $callback($sentEmailModel);
        }

        return $sentEmailModel;
    }

    /**
     * Check message recipient for tracking
     * Open tracking etc won't work if emails are sent to more than one recipient at a time
     *
     * @throws LaravelSesTooManyRecipientsException
     */
    protected function checkNumberOfRecipients(Email $message): void
    {
        if (sizeOf($message->getTo()) > 1) {
            throw new LaravelSesTooManyRecipientsException("Tried to send to too many emails only one email may be set");
        }
    }

    public function useInitMessageCallback(Closure $callback): self
    {
        $this->initMessageCallback = $callback;

        return $this;
    }

    protected function sendEvent(SentEmailContract $sentEmail): void
    {
        event(new SesSentEvent($sentEmail));
    }
}