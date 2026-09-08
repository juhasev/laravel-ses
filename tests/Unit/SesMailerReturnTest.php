<?php

namespace Juhasev\LaravelSes\Tests\Unit;

use Illuminate\Mail\SentMessage;
use Illuminate\Mail\Transport\ArrayTransport;
use Juhasev\LaravelSes\Mocking\TestMailable;
use Juhasev\LaravelSes\SesMailer;
use Juhasev\LaravelSes\Tests\UnitTestCase;

class SesMailerReturnTest extends UnitTestCase
{
    private function mailer(): SesMailer
    {
        // The array transport keeps everything in memory, so this exercises the
        // real mailer rather than the fake without sending anything.
        return new SesMailer(
            'ses-mailer',
            $this->app['view'],
            new ArrayTransport(),
            $this->app['events'],
        );
    }

    public function testSendReturnsTheSentMessage(): void
    {
        $sentMessage = $this->mailer()
            ->enableAllTracking()
            ->to('john.doe@example.com')
            ->send(new TestMailable());

        $this->assertInstanceOf(SentMessage::class, $sentMessage);
        $this->assertNotEmpty($sentMessage->getMessageId());
    }

    public function testTheReturnedMessageCarriesTheTrackingHeaders(): void
    {
        $sentMessage = $this->mailer()
            ->enableAllTracking()
            ->to('john.doe@example.com')
            ->send(new TestMailable());

        $headers = $sentMessage->getSymfonySentMessage()->getOriginalMessage()->getHeaders();

        $this->assertTrue($headers->has('X-SES-CONFIGURATION-SET'));
        $this->assertTrue($headers->has('Message-ID'));
    }
}
