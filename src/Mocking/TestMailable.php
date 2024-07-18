<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes\Mocking;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMailable extends Mailable
{
    use Queueable, SerializesModels;

    public string $test = 'replace with some junk';

    public function build(): self
    {
        return $this
            ->from('test@oliveready.com')
            ->view('LaravelSes::test');
    }
}
