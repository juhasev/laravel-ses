<?php

namespace Juhasev\LaravelSes;

use Exception;
use Juhasev\LaravelSes\Contracts\BatchContract;
use Juhasev\LaravelSes\Contracts\SentEmailContract;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\CurlException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Exceptions\StrictException;
use Ramsey\Uuid\Uuid;

class MailProcessor
{
    /**
     * @var string
     */
    protected $emailBody;

    /**
     * @var BatchContract
     */
    protected $batch;

    /**
     * @var SentEmailContract
     */
    protected $sentEmail;

    /**
     * MailProcessor constructor.
     *
     * @param SentEmailContract $sentEmail
     * @param string $emailBody
     */
    public function __construct(SentEmailContract $sentEmail, string $emailBody)
    {
        $this->setEmailBody($emailBody);
        $this->setSentEmail($sentEmail);
    }

    /**
     * Get email body
     *
     * @return string
     */
    public function getEmailBody(): string
    {
        return $this->emailBody;
    }

    /**
     * Set email body
     *
     * @param string $body
     */
    private function setEmailBody(string $body): void
    {
        $this->emailBody = $body;
    }

    /**
     * Set email sent
     *
     * @param SentEmailContract $email
     */
    private function setSentEmail(SentEmailContract $email): void
    {
        $this->sentEmail = $email;
    }

    /**
     * Open tracking
     *
     * @return MailProcessor
     * @throws Exception
     */
    public function openTracking(): MailProcessor
    {
        $beaconIdentifier = Uuid::uuid4()->toString();
        $beaconUrl = config('app.url') . "/ses/beacon/$beaconIdentifier";

        ModelResolver::get('EmailOpen')::create([
            'sent_email_id' => $this->sentEmail->getId(),
            'beacon_identifier' => $beaconIdentifier
        ]);

        $this->setEmailBody($this->getEmailBody() . "<img src=\"$beaconUrl\""
        . " alt=\"\" style=\"width:1px;height:1px;\"/>");

        return $this;
    }

    /**
     * Link tracking
     *
     * @return MailProcessor
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws NotLoadedException
     * @throws StrictException
     * @throws Exception
     */
    public function linkTracking(): MailProcessor
    {
        $dom = new Dom;
        $dom->load($this->getEmailBody());
        $anchors = $dom->find('a');

        foreach ($anchors as $anchor) {
            $originalUrl = $anchor->getAttribute('href');
            $anchor->setAttribute('href', $this->createAppLink($originalUrl));
        }

        $this->setEmailBody($dom->innerHtml);

        return $this;
    }

    /**
     * Create app link
     *
     * @param string $originalUrl
     * @return string
     * @throws Exception
     */
    private function createAppLink(string $originalUrl): string
    {
        $linkIdentifier = Uuid::uuid4()->toString();

        ModelResolver::get('EmailLink')::create([
            'sent_email_id' => $this->sentEmail->getId(),
            'link_identifier' => $linkIdentifier,
            'original_url' => $originalUrl
        ]);

        return config('app.url') . "/ses/link/$linkIdentifier";
    }
}
