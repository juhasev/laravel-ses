<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes;

use Exception;
use Illuminate\Mail\Message;
use Closure;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Testing\Fakes\MailFake;
use Symfony\Component\Mime\Email;

class SesMailFake extends MailFake implements SesMailerInterface
{
    use SesMailerTrait;
    use TrackingTrait;

    /**
     * Send a new message using a view.
     *
     * @param Mailable|string|array $view
     * @param array $data
     * @param Closure|string|null $callback
     * @return void
     * @throws Exception
     * @psalm-suppress UndefinedInterfaceMethod
     * @psalm-suppress NoInterfaceProperties
     * @psalm-suppress InvalidArgument
     */
    public function send($view, array $data = [], $callback = null): void
    {
        if (! $view instanceof Mailable) {
            return;
        }

        $message = new Message(new Email());
        $message->from('sender@example.com', 'John Doe');
        $message->to(collect($view->to)->pluck('address')->all(), null, true);
        $message->html(' ');

        $symfonyMessage = $message->getSymfonyMessage();

        $sentEmail = $this->initMessage($symfonyMessage);

        $emailBody = $this->setupTracking((string) $message->getHtmlBody(), $sentEmail);

        $view->sesBody = $emailBody;

        $view->mailer($this->currentMailer);
        $this->currentMailer = null;

        if ($view instanceof ShouldQueue) {
            /** @var Mailable $view */
            $this->queue($view, $data);
        }

        $this->mailables[] = $view;

        $this->sendEvent($sentEmail);
    }

    /**
     * Get the array of failed recipients.
     */
    public function failures(): array
    {
        return [];
    }
}
