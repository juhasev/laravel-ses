<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes;

use Juhasev\LaravelSes\Contracts\BatchContract;
use Juhasev\LaravelSes\Contracts\SentEmailContract;
use Symfony\Component\Mime\Email;

interface SesMailerInterface
{
    public function initMessage(Email $message): SentEmailContract;

    public function setupTracking($setupTracking, SentEmailContract $sentEmail): string;

    public function setBatch(string $batch): SesMailerInterface;

    public function getBatchId(): ?int;

    public function getBatch(): ?BatchContract;

    public function setTrackingModelCallback(callable $callback): SesMailerInterface;

    public function getTrackingModelCallback(): ?callable;

    public function enableOpenTracking(): SesMailerInterface;

    public function enableLinkTracking(): SesMailerInterface;

    public function enableBounceTracking(): SesMailerInterface;

    public function enableComplaintTracking(): SesMailerInterface;

    public function enableDeliveryTracking(): SesMailerInterface;

    public function enableRejectTracking(): SesMailerInterface;

    public function disableOpenTracking(): SesMailerInterface;

    public function disableLinkTracking(): SesMailerInterface;

    public function disableBounceTracking(): SesMailerInterface;

    public function disableComplaintTracking(): SesMailerInterface;

    public function disableDeliveryTracking(): SesMailerInterface;

    public function disableRejectTracking(): SesMailerInterface;

    public function enableAllTracking(): SesMailerInterface;

    public function disableAllTracking(): SesMailerInterface;

    public function trackingSettings(): array;
}
