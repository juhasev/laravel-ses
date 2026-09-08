<?php

namespace Juhasev\LaravelSes\Tests\Feature\Events;

use Juhasev\LaravelSes\Factories\Events\SesLinkEvent;
use Juhasev\LaravelSes\ModelResolver;
use Juhasev\LaravelSes\Tests\FeatureTestCase;
use PHPUnit\Framework\Attributes\Test;

class SesLinkEventTest extends FeatureTestCase
{
    #[Test]
    public function it_ensures_the_right_data_structure_for_link_event(): void
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

        $link = ModelResolver::get('EmailLink')::create([
            'sent_email_id' => $sentEmail->id,
            'link_identifier' => 'linkId',
            'original_url' => 'origUrl',
            'clicked' => true,
            'click_count' => 2,
        ]);

        $event = new SesLinkEvent($link);

        $this->assertArrayHasKey('id', $event->data);
        $this->assertArrayHasKey('link_identifier', $event->data);
        $this->assertArrayHasKey('sent_email_id', $event->data);
        $this->assertArrayHasKey('original_url', $event->data);
        $this->assertArrayHasKey('clicked', $event->data);
        $this->assertArrayHasKey('click_count', $event->data);
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