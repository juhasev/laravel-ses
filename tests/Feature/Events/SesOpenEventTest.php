<?php

namespace Juhasev\LaravelSes\Tests\Feature\Events;

use Juhasev\LaravelSes\Factories\Events\SesOpenEvent;
use Juhasev\LaravelSes\ModelResolver;
use Juhasev\LaravelSes\Tests\FeatureTestCase;
use PHPUnit\Framework\Attributes\Test;

class SesOpenEventTest extends FeatureTestCase
{
    #[Test]
    public function it_ensures_the_right_data_structure_for_open_event(): void
    {
        $batch = ModelResolver::get('Batch')::create([
            'name' => 'Test Batch',
        ]);

        $sentEmail = ModelResolver::get('SentEmail')::create([
            'batch_id' => $batch->id,
            'message_id' => 'abc123',
            'email' => 'example@sampleninja.io',
            'sent_at' => now(),
        ]);

        $open = ModelResolver::get('EmailOpen')::create([
            'sent_email_id' => $sentEmail->id,
            'beacon_identifier' => 'beaconId',
            'opened_at' => now(),
        ]);

        $event = new SesOpenEvent($open);

        $this->assertArrayHasKey('id', $event->data);
        $this->assertArrayHasKey('sent_email_id', $event->data);
        $this->assertArrayHasKey('beacon_identifier', $event->data);
        $this->assertArrayHasKey('opened_at', $event->data);
        $this->assertArrayHasKey('sent_email', $event->data);

        $this->assertArrayHasKey('id', $event->data['sent_email']);
        $this->assertArrayHasKey('message_id', $event->data['sent_email']);
        $this->assertArrayHasKey('email', $event->data['sent_email']);
        $this->assertArrayHasKey('batch_id', $event->data['sent_email']);
        $this->assertArrayHasKey('sent_at', $event->data['sent_email']);
        $this->assertArrayHasKey('delivered_at', $event->data['sent_email']);
        $this->assertArrayHasKey('batch', $event->data['sent_email']);

        $this->assertArrayHasKey('id', $event->data['sent_email']['batch']);
        $this->assertArrayHasKey('created_at', $event->data['sent_email']['batch']);
        $this->assertArrayHasKey('updated_at', $event->data['sent_email']['batch']);
        $this->assertArrayHasKey('name', $event->data['sent_email']['batch']);
    }
}