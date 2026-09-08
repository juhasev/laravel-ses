<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes\DataTransferObjects;

use Carbon\Carbon;
use DateTimeInterface;
use JsonException;

class MessageContent
{
    public string $id;
    public ?string $destination;
    public ?string $complaintFeedbackType;
    public ?string $bounceType;
    public ?DateTimeInterface $deliveryTimestamp;
    public ?DateTimeInterface $mailTimestamp;

    public function __construct(
        string             $id,
        ?string            $destination,
        ?string             $complaintFeedbackType,
        ?string            $bounceType,
        ?DateTimeInterface $deliveryTimestamp,
        ?DateTimeInterface $mailTimestamp,
    )
    {
        $this->id = $id;
        $this->destination = $destination;
        $this->complaintFeedbackType = $complaintFeedbackType;
        $this->bounceType = $bounceType;
        $this->deliveryTimestamp = $deliveryTimestamp;
        $this->mailTimestamp = $mailTimestamp;
    }

    /**
     * @throws JsonException
     */
    public static function fromJson(string $data): self
    {
        $data = json_decode($data, false, 512, JSON_THROW_ON_ERROR);

        $messageId = trim(collect(data_get($data, 'mail.headers', []))
            ->where('name', 'Message-ID')
            ->pluck('value')
            ->first() ?? '', '<>');

        return new self(
            id: $messageId,
            destination: data_get($data, 'mail.destination.0'),
            complaintFeedbackType: data_get($data, 'complaint.complaintFeedbackType', 'unknown'),
            bounceType: data_get($data, 'bounce.bounceType'),
            deliveryTimestamp: Carbon::parse(data_get($data, 'delivery.timestamp')),
            mailTimestamp: Carbon::parse(data_get($data, 'mail.timestamp')),
        );
    }
}