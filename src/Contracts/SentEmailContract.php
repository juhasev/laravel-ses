<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes\Contracts;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static loadMissing($relations)
 * @method array toArray()
 */
interface SentEmailContract
{
    public function setMessageId(string $messageId): self;

    public function getMessageId(): string;

    public function setDeliveredAt(DateTimeInterface $time): self;
    
    public function emailOpen(): HasOne;

    public function emailLinks(): HasMany;

    public function emailBounce(): HasOne;

    public function emailComplaint(): HasOne;

    public function emailReject(): HasOne;

    public function getId(): mixed;
}