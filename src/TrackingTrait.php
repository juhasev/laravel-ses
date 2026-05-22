<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes;

use Exception;
use Juhasev\LaravelSes\Contracts\BatchContract;
use Juhasev\LaravelSes\Contracts\SentEmailContract;
use Juhasev\LaravelSes\Models\Batch;

/**
 * @psalm-suppress UndefinedMethod
 */
trait TrackingTrait
{
    private bool $openTracking = false;

    private bool $linkTracking = false;

    private bool $bounceTracking = false;

    private bool $complaintTracking = false;

    private bool $deliveryTracking = false;

    private bool $rejectTracking = false;

    private ?BatchContract $batch = null;

    private ?string $customDomain = null;

    /**
     * @param string $setupTracking
     * @throws Exception
     */
    public function setupTracking($setupTracking, SentEmailContract $sentEmail): string
    {
        $this->batch = null;

        $mailProcessor = new MailProcessor($sentEmail, $setupTracking, $this->customDomain);

        // Custom domain is per-message data (like the batch above), not a
        // persistent toggle. Reset it after it has been consumed so it cannot
        // silently leak onto a subsequent send to a different domain.
        $this->customDomain = null;

        if ($this->openTracking) {
            $mailProcessor->openTracking();
        }

        if ($this->linkTracking) {
            $mailProcessor->linkTracking();
        }

        return $mailProcessor->getEmailBody();
    }

    /**
     * @throws Exception
     */
    public function setBatch(string $batch): SesMailerInterface
    {
        /**
         * @var Batch $batchModel
         * @psalm-suppress UndefinedMethod
         */
        $batchModel = ModelResolver::get('Batch')::query();

        $batchResult = $batchModel->where('name', $batch)->first();

        if (! $batchResult) {
            $batchResult = $batchModel->create(['name' => $batch]);
        }

        $this->batch = $batchResult;

        return $this;
    }

    public function getBatch(): ?BatchContract
    {
        return $this->batch;
    }

    /**
     * Override the global tracking domain (config('app.url')) for this mailer.
     *
     * Affects every tracking URL generated for the message — the open-tracking
     * beacon and the rewritten link-tracking hrefs. Pass null to fall back to
     * the configured global domain.
     */
    public function customDomain(?string $domain): SesMailerInterface
    {
        $this->customDomain = $domain;

        return $this;
    }

    public function getCustomDomain(): ?string
    {
        return $this->customDomain;
    }

    public function getBatchId(): ?int
    {
        return $this->batch?->getId();
    }

    public function enableOpenTracking(): SesMailerInterface
    {
        $this->openTracking = true;

        return $this;
    }

    public function enableLinkTracking(): SesMailerInterface
    {
        $this->linkTracking = true;

        return $this;
    }

    public function enableBounceTracking(): SesMailerInterface
    {
        $this->bounceTracking = true;

        return $this;
    }

    public function enableComplaintTracking(): SesMailerInterface
    {
        $this->complaintTracking = true;

        return $this;
    }

    public function enableDeliveryTracking(): SesMailerInterface
    {
        $this->deliveryTracking = true;

        return $this;
    }

    public function enableRejectTracking(): SesMailerInterface
    {
        $this->rejectTracking = true;

        return $this;
    }

    public function disableOpenTracking(): SesMailerInterface
    {
        $this->openTracking = false;

        return $this;
    }

    public function disableLinkTracking(): SesMailerInterface
    {
        $this->linkTracking = false;

        return $this;
    }

    public function disableBounceTracking(): SesMailerInterface
    {
        $this->bounceTracking = false;

        return $this;
    }

    public function disableComplaintTracking(): SesMailerInterface
    {
        $this->complaintTracking = false;

        return $this;
    }

    public function disableDeliveryTracking(): SesMailerInterface
    {
        $this->deliveryTracking = false;

        return $this;
    }

    public function disableRejectTracking(): SesMailerInterface
    {
        $this->rejectTracking = false;

        return $this;
    }

    public function enableAllTracking(): SesMailerInterface
    {
        return $this->enableOpenTracking()
            ->enableLinkTracking()
            ->enableBounceTracking()
            ->enableComplaintTracking()
            ->enableDeliveryTracking();
    }

    public function disableAllTracking(): SesMailerInterface
    {
        return $this->disableOpenTracking()
            ->disableLinkTracking()
            ->disableBounceTracking()
            ->disableComplaintTracking()
            ->disableDeliveryTracking();
    }

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
