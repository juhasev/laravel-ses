<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes;

use Closure;
use Illuminate\Contracts\Mail\Mailable as MailableContract;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Carbon;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Juhasev\LaravelSes\Contracts\SentEmailContract;
use Juhasev\LaravelSes\Exceptions\LaravelSesDailyQuotaExceededException;
use Juhasev\LaravelSes\Exceptions\LaravelSesInvalidSenderAddressException;
use Juhasev\LaravelSes\Exceptions\LaravelSesMaximumSendingRateExceeded;
use Juhasev\LaravelSes\Exceptions\LaravelSesSendFailedException;
use Juhasev\LaravelSes\Exceptions\LaravelSesTemporaryServiceFailureException;
use Juhasev\LaravelSes\Exceptions\LaravelSesTooManyRecipientsException;
use Juhasev\LaravelSes\Factories\Events\SesSentEvent;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\CurlException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Exceptions\StrictException;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\Headers;
use Throwable;

class SesMailer extends Mailer implements SesMailerInterface
{
    use TrackingTrait;

    /**
     * Init message (this is always called)
     * Creates database entry for the sent email
     *
     * @param Email $message
     * @return SentEmailContract
     * @throws LaravelSesTooManyRecipientsException
     */
    public function initMessage(Email $message): SentEmailContract
    {
        $this->checkNumberOfRecipients($message);

        return ModelResolver::get('SentEmail')::create([
            'message_id' => $message->generateMessageId(),
            'email' => $message->getTo()[0]->getAddress(),
            'batch_id' => $this->getBatchId(),
            'sent_at' => Carbon::now(),
            'delivery_tracking' => $this->deliveryTracking,
            'complaint_tracking' => $this->complaintTracking,
            'bounce_tracking' => $this->bounceTracking
        ]);
    }

    /**
     * Check message recipient for tracking
     * Open tracking etc won't work if emails are sent to more than one recipient at a time
     *
     * @param Email $message
     * @throws LaravelSesTooManyRecipientsException
     */
    protected function checkNumberOfRecipients(Email $message): void
    {
        if (sizeOf($message->getTo()) > 1) {
            throw new LaravelSesTooManyRecipientsException("Tried to send to too many emails only one email may be set");
        }
    }

    /**
     * Throw SampleNinja exceptions
     *
     * @param Throwable $e
     * @throws LaravelSesDailyQuotaExceededException
     * @throws LaravelSesInvalidSenderAddressException
     * @throws LaravelSesMaximumSendingRateExceeded
     * @throws LaravelSesTemporaryServiceFailureException|LaravelSesSendFailedException
     */
    protected function throwException(Throwable $e): void
    {
        $errorMessage = $this->parseErrorFromSymfonyTransportException($e->getMessage());
        $errorCode = $this->parseErrorCode($errorMessage);

        Log::error('SES Error: ' . $errorMessage);

        if (Str::contains($errorMessage, '454 Throttling failure: Maximum sending rate exceeded')) {
            throw new LaravelSesMaximumSendingRateExceeded($errorMessage, $errorCode);
        }

        if (Str::contains($errorMessage, '454 Throttling failure: Daily message quota exceeded')) {
            throw new LaravelSesDailyQuotaExceededException($errorMessage, $errorCode);
        }

        if (Str::contains($errorMessage, '554 Message rejected: Email address is not verified')) {
            throw new LaravelSesInvalidSenderAddressException($errorMessage, $errorCode);
        }

        if (Str::contains($errorMessage, '451 Temporary service failure')) {
            throw new LaravelSesTemporaryServiceFailureException($errorMessage, $errorCode);
        }

        throw new LaravelSesSendFailedException($errorMessage, $errorCode);
    }

    /**
     * Resolve error code
     *
     * @param string $message
     * @return string
     */
    protected function parseErrorFromSymfonyTransportException(string $message): string
    {
        $message = Str::after($message, ' with message "');

        return Str::beforeLast($message, '"');
    }

    /**
     * Parse error code
     *
     * @param string $smtpError
     * @return int
     */
    protected function parseErrorCode(string $smtpError): int
    {
        return (int) Str::before($smtpError, ' Message');
    }

    /**
     * Send a new message using a view.
     *
     * @param MailableContract|string|array $view
     * @param array $data
     * @param Closure|string|null $callback
     * @return SentMessage|null
     * @throws LaravelSesDailyQuotaExceededException
     * @throws LaravelSesInvalidSenderAddressException
     * @throws LaravelSesMaximumSendingRateExceeded
     * @throws LaravelSesSendFailedException
     * @throws LaravelSesTemporaryServiceFailureException
     */
    public function send($view, array $data = [], $callback = null): SentMessage|null
    {
        if ($view instanceof MailableContract) {
            return $this->sendMailable($view);
        }

        // First we need to parse the view, which could either be a string or an array
        // containing both an HTML and plain text versions of the view which should
        // be used when sending an e-mail. We will extract both of them out here.
        [$view, $plain, $raw] = $this->parseView($view);

        $data['message'] = $message = $this->createMessage();

        // Once we have retrieved the view content for the e-mail we will set the body
        // of this message using the HTML type, which will provide a simple wrapper
        // to creating view based emails that are able to receive arrays of data.
        if (! is_null($callback)) {
            $callback($message);
        }

        $this->addContent($message, $view, $plain, $raw, $data);

        // If a global "to" address has been set, we will set that address on the mail
        // message. This is primarily useful during local development in which each
        // message should be delivered into a single mail address for inspection.
        if (isset($this->to['address'])) {
            $this->setGlobalToAndRemoveCcAndBcc($message);
        }

        // Next we will determine if the message should be sent. We give the developer
        // one final chance to stop this message and then we will send it to all of
        // its recipients. We will then fire the sent event for the sent message.
        $symfonyMessage = $message->getSymfonyMessage();

        if ($this->shouldSendMessage($symfonyMessage, $data)) {
            try {
                $this->sendSymfonyMessage($symfonyMessage);
            } catch (Throwable $e) {
                $this->throwException($e);
            }
        }

        return null;
    }

    /**
     * Send symfony message
     *
     * @param Email $message
     * @return void
     *
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws LaravelSesTooManyRecipientsException
     * @throws NotLoadedException
     * @throws StrictException
     */
    protected function sendSymfonyMessage(Email $message): void
    {
        $sentEmail = $this->initMessage($message);

        $message->setHeaders($this->appendToHeaders($message->getHeaders(), $sentEmail));

        $message->html($this->setupTracking((string) $message->getHtmlBody(), $sentEmail));

        // Sending email first, in case sendEvent fails
        parent::sendSymfonyMessage($message);

        $this->sendEvent($sentEmail);
    }

    protected function appendToHeaders(Headers $headers, SentEmailContract $email): Headers
    {
        $headers->addIdHeader('Message-ID', $email->getMessageId());
        $headers->addTextHeader('X-SES-CONFIGURATION-SET', $this->getConfigurationSetName());

        return $headers;
    }

    /**
     * Returns AWS SES configuration set name like staging-ses-complaint-us-west-2
     */
    protected function getConfigurationSetName(): string
    {
        return App::environment() . '-ses-' . config('services.ses.region');
    }

    protected function sendEvent(SentEmailContract $sentEmail): void
    {
        event(new SesSentEvent($sentEmail));
    }
}
