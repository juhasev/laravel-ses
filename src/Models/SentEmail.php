<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Juhasev\LaravelSes\Contracts\SentEmailContract;
use Juhasev\LaravelSes\ModelResolver;

class SentEmail extends Model implements SentEmailContract
{
    protected $table = 'laravel_ses_sent_emails';

    protected $guarded = [];

    protected $casts = [
        'batch_id' => 'integer',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'complaint_tracking' => 'boolean',
        'delivery_tracking' => 'boolean',
        'bounce_tracking' => 'boolean',
    ];

    protected $hidden = [
        'complaint_tracking',
        'delivery_tracking',
        'bounce_tracking',
    ];

    public function emailOpen(): HasOne
    {
        return $this->hasOne(ModelResolver::get('EmailOpen'));
    }

    public function emailLinks(): HasMany
    {
        return $this->hasMany(ModelResolver::get('EmailLink'));
    }

    public function emailBounce(): HasOne
    {
        return $this->hasOne(ModelResolver::get('EmailBounce'));
    }

    public function emailComplaint(): HasOne
    {
        return $this->hasOne(ModelResolver::get('EmailComplaint'));
    }

    public function emailReject(): HasOne
    {
        return $this->hasOne(ModelResolver::get('EmailReject'));
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ModelResolver::get('Batch'));
    }

    public function setDeliveredAt(DateTimeInterface $time): self
    {
        $this->update(['delivered_at' => $time]);

        return $this;
    }

    public function setMessageId(string $messageId): self
    {
        $this->update(['message_id' => $messageId]);

        return $this;
    }

    public function getId(): mixed
    {
        return $this->getKey();
    }

    public function getMessageId(): string
    {
        return $this->message_id;
    }
}
