<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes;

use Exception;
use Juhasev\LaravelSes\Contracts\BatchContract;
use Juhasev\LaravelSes\Contracts\SentEmailContract;
use Ramsey\Uuid\Uuid;
use voku\helper\HtmlDomParser;

class MailProcessor
{
    protected string $emailBody;

    protected BatchContract $batch;

    protected SentEmailContract $sentEmail;

    public function __construct(SentEmailContract $sentEmail, string $emailBody)
    {
        $this->setEmailBody($emailBody);
        $this->setSentEmail($sentEmail);
    }

    public function getEmailBody(): string
    {
        return $this->emailBody;
    }

    private function setEmailBody(string $body): void
    {
        $this->emailBody = $body;
    }

    private function setSentEmail(SentEmailContract $email): void
    {
        $this->sentEmail = $email;
    }

    /**
     * @throws Exception
     */
    public function openTracking(): self
    {
        $beaconIdentifier = Uuid::uuid4()->toString();
        $beaconUrl = config('app.url') . "/ses/beacon/$beaconIdentifier";

        /** @psalm-suppress UndefinedMethod */
        ModelResolver::get('EmailOpen')::create([
            'sent_email_id' => $this->sentEmail->getId(),
            'beacon_identifier' => $beaconIdentifier
        ]);

        $this->setEmailBody($this->getEmailBody() . "<img src=\"$beaconUrl\""
        . " alt=\"\" style=\"width:1px;height:1px;\"/>");

        return $this;
    }

    /**
     * @throws Exception
     */
    public function linkTracking(): self
    {
        $dom = HtmlDomParser::str_get_html($this->getEmailBody());

        foreach ($dom->findMulti('a') as $anchor) {
            $originalUrl = $anchor->getAttribute('href');

            if ((string) $originalUrl !== '') {
                $anchor->setAttribute('href', $this->createAppLink($originalUrl));
            }
        }

        $this->setEmailBody($dom->innerHtml);

        return $this;
    }

    /**
     * @throws Exception
     */
    private function createAppLink(string $originalUrl): string
    {
        $linkIdentifier = Uuid::uuid4()->toString();

        /** @psalm-suppress UndefinedMethod */
        ModelResolver::get('EmailLink')::create([
            'sent_email_id' => $this->sentEmail->getId(),
            'link_identifier' => $linkIdentifier,
            'original_url' => $originalUrl
        ]);

        return config('app.url') . "/ses/link/$linkIdentifier";
    }
}
