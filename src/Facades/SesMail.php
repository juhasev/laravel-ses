<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes\Facades;

use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Facade;
use Juhasev\LaravelSes\SesMailFake;

/**
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
