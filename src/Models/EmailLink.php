<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Juhasev\LaravelSes\Contracts\EmailLinkContract;
use Juhasev\LaravelSes\ModelResolver;

class EmailLink extends Model implements EmailLinkContract
{
    protected $table = 'laravel_ses_email_links';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'sent_email_id' => 'integer',
        'clicked' => 'boolean',
        'click_count' => 'integer',
    ];

    public function sentEmail(): BelongsTo
    {
        /**
         * @psalm-suppress InvalidArgument
         * @psalm-suppress InvalidCast
         */
        return $this->belongsTo(ModelResolver::get('SentEmail'));
    }

    public function setClicked(bool $clicked): self
    {
        $this->update(['clicked' => $clicked]);

        return $this;
    }

    public function incrementClickCount(): self
    {
        $this->increment('click_count');

        return $this;
    }

    public function getId(): mixed
    {
        return $this->getKey();
    }

    public function originalUrl(): string
    {
        return $this->original_url;
    }
}
