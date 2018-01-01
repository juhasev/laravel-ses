<?php

namespace oliveready7\LaravelSes\Tests\Unit;
use SesMail;
use oliveready7\LaravelSes\Mocking\TestMailable;
use oliveready7\LaravelSes\Exceptions\TooManyEmails;

class SesMailerTest extends UnitTestCase {

    public function test_exception_is_thrown_when_trying_to_send_to_more_than_one_person() {
        SesMail::fake();
        $mail = new TestMailable();
        $exceptionThrown = false;

        try {
            SesMail::to(['oliveready@gmail.com', 'something@whatever.com'])->send($mail);
        }catch(TooManyEmails $e){
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }

    public function test_tracking_settings_are_set_correctly() {
        SesMail::enableOpenTracking()
            ->enableLinkTracking()
            ->enableBounceTracking();

        $this->assertEquals([
            'openTracking' => true,
            'linkTracking' => true,
            'bounceTracking' => true,
            'deliveryTracking' => false,
            'complaintTracking' => false
        ], SesMail::trackingSettings());

        //check that disabling works
        SesMail::disableOpenTracking()
            ->disableLinkTracking()
            ->disableBounceTracking();

        $this->assertEquals([
            'openTracking' => false,
            'linkTracking' => false,
            'bounceTracking' => false,
            'deliveryTracking' => false,
            'complaintTracking' => false
        ], SesMail::trackingSettings());

        //check all tracking methods work
        SesMail::enableAllTracking();

        $this->assertEquals([
            'openTracking' => true,
            'linkTracking' => true,
            'bounceTracking' => true,
            'deliveryTracking' => true,
            'complaintTracking' => true
        ], SesMail::trackingSettings());

        SesMail::disableAllTracking();

        $this->assertEquals([
            'openTracking' => false,
            'linkTracking' => false,
            'bounceTracking' => false,
            'deliveryTracking' => false,
            'complaintTracking' => false
        ], SesMail::trackingSettings());
    }
}
