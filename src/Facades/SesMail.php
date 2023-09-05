<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes\Facades;

use Closure;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Facade;
use Juhasev\LaravelSes\Contracts\BatchContract;
use Juhasev\LaravelSes\Contracts\SentEmailContract;
use Juhasev\LaravelSes\SesMailFake;
use Symfony\Component\Mime\Email;

/**
 * @method SentEmailContract initMessage(Email $email)
 * @method string setupTracking(string $setupTracking, SentEmailContract $sentEmail)
 * @method array trackingSettings()
 * @method int|null getBatchId()
 * @method BatchContract|null getBatch()
 * @method static setBatch(string $batch)
 * @method static useInitMessageCallback(Closure $callback)
 * @method static enableAllTracking()
 * @method static enableOpenTracking()
 * @method static enableLinkTracking()
 * @method static enableBounceTracking()
 * @method static enableComplaintTracking()
 * @method static enableDeliveryTracking()
 * @method static enableRejectTracking()
 * @method static disableAllTracking()
 * @method static disableOpenTracking()
 * @method static disableLinkTracking()
 * @method static disableBounceTracking()
 * @method static disableComplaintTracking()
 * @method static disableDeliveryTracking()
 * @method static disableRejectTracking()
 *
 * @see \Illuminate\Mail\Mailer
 */
class SesMail extends Facade
{
    public static function fake(): void
    {
        static::swap(new SesMailFake(new MailManager(app())));
    }

    protected static function getFacadeAccessor(): string
    {
        return 'SesMailer';
    }
}
