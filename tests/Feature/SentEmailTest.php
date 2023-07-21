<?php

namespace Juhasev\LaravelSes\Tests\Feature;

use Juhasev\LaravelSes\Facades\SesMail;
use Juhasev\LaravelSes\Mocking\TestMailable;
use Juhasev\LaravelSes\ModelResolver;
use Juhasev\LaravelSes\Tests\FeatureTestCase;

class SentEmailTest extends FeatureTestCase
{
    public function testSentEmailRecordContainsTheRightMessageId(): void
    {
        SesMail::fake();

        $emailAddress = 'john.doe@example.com';

        SesMail::enableAllTracking()->to($emailAddress)->send(new TestMailable());

        $record = ModelResolver::get('SentEmail')::whereEmail($emailAddress)->first();

        $this->assertMatchesRegularExpression('/^[a-z0-9]+@\w+\.\w+$/', $record->message_id);
        $this->assertStringNotContainsString('Message-ID:', $record->message_id);
        $this->assertStringNotContainsString('<', $record->message_id);
        $this->assertStringNotContainsString('>', $record->message_id);
    }
}