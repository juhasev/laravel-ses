<?php

namespace Juhasev\LaravelSes\Tests\Feature;

use Juhasev\LaravelSes\Facades\SesMail;
use Juhasev\LaravelSes\Mocking\TestMailable;
use Juhasev\LaravelSes\Models\EmailOpen;
use Juhasev\LaravelSes\Tests\FeatureTestCase;

class CustomDomainTest extends FeatureTestCase
{
    public function testCustomDomainOverridesGlobalDomainOnRenderedTrackingUrl()
    {
        SesMail::fake();

        $mailable = new TestMailable();

        SesMail::enableOpenTracking()
            ->customDomain('https://newdomain.com')
            ->to('harrykane9@example.com')
            ->send($mailable);

        $beaconId = EmailOpen::first()->beacon_identifier;

        $this->assertStringContainsString(
            'https://newdomain.com/ses/beacon/' . $beaconId,
            $mailable->sesBody
        );

        // The global config('app.url') domain must not be used for tracking.
        $this->assertStringNotContainsString('https://laravel-ses.com/ses/beacon', $mailable->sesBody);
    }

    public function testTrackingFallsBackToGlobalDomainWhenCustomDomainNotSet()
    {
        SesMail::fake();

        $mailable = new TestMailable();

        SesMail::enableOpenTracking()
            ->to('harrykane9@example.com')
            ->send($mailable);

        $beaconId = EmailOpen::first()->beacon_identifier;

        $this->assertStringContainsString(
            'https://laravel-ses.com/ses/beacon/' . $beaconId,
            $mailable->sesBody
        );
    }

    public function testEnableAllTrackingChainThreadsCustomDomainOntoMailer()
    {
        SesMail::fake();

        // Mirrors the documented usage exactly. The custom domain is consumed
        // for this message and then reset, so it does not leak to later sends.
        SesMail::enableAllTracking()
            ->customDomain('https://newdomain.com')
            ->to('harrykane9@example.com')
            ->send(new TestMailable());

        $this->assertNull(SesMail::getCustomDomain());
    }

    public function testCustomDomainDoesNotLeakToASubsequentSend()
    {
        SesMail::fake();

        $first = new TestMailable();

        SesMail::enableOpenTracking()
            ->customDomain('https://tenant-a.com')
            ->to('a@example.com')
            ->send($first);

        $firstBeacon = EmailOpen::orderBy('id')->first()->beacon_identifier;
        $this->assertStringContainsString('https://tenant-a.com/ses/beacon/' . $firstBeacon, $first->sesBody);

        // Second send does NOT re-specify a custom domain: it must fall back to
        // the global APP_URL, never reuse tenant A's domain.
        $second = new TestMailable();

        SesMail::enableOpenTracking()
            ->to('b@example.com')
            ->send($second);

        $secondBeacon = EmailOpen::orderByDesc('id')->first()->beacon_identifier;
        $this->assertStringContainsString('https://laravel-ses.com/ses/beacon/' . $secondBeacon, $second->sesBody);
        $this->assertStringNotContainsString('https://tenant-a.com', $second->sesBody);
    }

    public function testEmptyStringCustomDomainFallsBackToGlobalDomain()
    {
        SesMail::fake();

        $mailable = new TestMailable();

        SesMail::enableOpenTracking()
            ->customDomain('')
            ->to('harrykane9@example.com')
            ->send($mailable);

        $beaconId = EmailOpen::first()->beacon_identifier;

        // An empty string must not produce a relative "/ses/beacon" URL.
        $this->assertStringContainsString('https://laravel-ses.com/ses/beacon/' . $beaconId, $mailable->sesBody);
        $this->assertStringNotContainsString('"/ses/beacon', $mailable->sesBody);
    }
}
