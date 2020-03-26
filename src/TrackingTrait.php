<?php

namespace Juhasev\LaravelSes;

use Juhasev\LaravelSes\Models\SentEmail;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\CurlException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Exceptions\StrictException;

trait TrackingTrait
{
    private $openTracking = false;
    private $linkTracking = false;
    private $bounceTracking = false;
    private $complaintTracking = false;
    private $deliveryTracking = false;
    private $batch;

    /**
     * Set tracking
     *
     * @param $emailBody
     * @param SentEmail $sentEmail
     *
     * @return string
     *
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws NotLoadedException
     * @throws StrictException
     */
    public function setupTracking($emailBody, SentEmail $sentEmail)
    {
        $mailProcessor = new MailProcessor($sentEmail, $emailBody);

        if ($this->openTracking) {
            $mailProcessor->openTracking();
        }
        if ($this->linkTracking) {
            $mailProcessor->linkTracking();
        }

        return $mailProcessor->getEmailBody();
    }

    /**
     * Set batch identifier
     *
     * @param string $batch
     * @return SesMailerInterface
     */
    public function setBatch(string $batch): SesMailerInterface
    {
        $this->batch = $batch;
        return $this;
    }

    /**
     * Get batch identifier
     *
     * @return string
     */
    public function getBatch(): string
    {
        return $this->batch;
    }

    /**
     * Enable open tracking
     *
     * @return SesMailerInterface
     */
    public function enableOpenTracking(): SesMailerInterface
    {
        $this->openTracking = true;
        return $this;
    }

    /**
     * Enable link tracking
     *
     * @return SesMailerInterface
     */
    public function enableLinkTracking(): SesMailerInterface
    {
        $this->linkTracking = true;
        return $this;
    }

    /**
     * Enable bounce tracking
     *
     * @return SesMailerInterface
     */
    public function enableBounceTracking(): SesMailerInterface
    {
        $this->bounceTracking = true;
        return $this;
    }

    /**
     * Enable complaint tracking
     *
     * @return SesMailerInterface
     */
    public function enableComplaintTracking(): SesMailerInterface
    {
        $this->complaintTracking = true;
        return $this;
    }

    /**
     * Enable delivery tracking
     *
     * @return SesMailerInterface
     */
    public function enableDeliveryTracking(): SesMailerInterface
    {
        $this->deliveryTracking = true;
        return $this;
    }

    /**
     * Disable open tracking
     *
     * @return SesMailerInterface
     */
    public function disableOpenTracking(): SesMailerInterface
    {
        $this->openTracking = false;
        return $this;
    }

    /**
     * Disable link tracking
     *
     * @return SesMailerInterface
     */
    public function disableLinkTracking(): SesMailerInterface
    {
        $this->linkTracking = false;
        return $this;
    }

    /**
     * Disable bounce tracking
     *
     * @return SesMailerInterface
     */
    public function disableBounceTracking(): SesMailerInterface
    {
        $this->bounceTracking = false;
        return $this;
    }

    /**
     * Disable complaint tracking
     *
     * @return SesMailerInterface
     */
    public function disableComplaintTracking(): SesMailerInterface
    {
        $this->complaintTracking = false;
        return $this;
    }

    /**
     * Disable delivery tracking
     *
     * @return SesMailerInterface
     */
    public function disableDeliveryTracking(): SesMailerInterface
    {
        $this->deliveryTracking = false;
        return $this;
    }

    /**
     * Enable all tracking
     *
     * @return SesMailerInterface
     */
    public function enableAllTracking(): SesMailerInterface
    {
        return $this->enableOpenTracking()
            ->enableLinkTracking()
            ->enableBounceTracking()
            ->enableComplaintTracking()
            ->enableDeliveryTracking();
    }

    /**
     * Disable all tracking
     *
     * @return SesMailerInterface
     */
    public function disableAllTracking(): SesMailerInterface
    {
        return $this->disableOpenTracking()
            ->disableLinkTracking()
            ->disableBounceTracking()
            ->disableComplaintTracking()
            ->disableDeliveryTracking();
    }

    /**
     * Get tracking settings
     *
     * @return array
     */
    public function trackingSettings(): array
    {
        return [
            'openTracking' => $this->openTracking,
            'linkTracking' => $this->linkTracking,
            'bounceTracking' => $this->bounceTracking,
            'complaintTracking' => $this->complaintTracking,
            'deliveryTracking' => $this->deliveryTracking
         ];
    }
}
