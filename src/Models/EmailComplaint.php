<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Juhasev\LaravelSes\Contracts\EmailComplaintContract;
use Juhasev\LaravelSes\ModelResolver;

class EmailComplaint extends Model implements EmailComplaintContract
{
    protected $table = 'laravel_ses_email_complaints';

    public $timestamps = false;
    
    protected $guarded = [];

    protected $casts = [
        'sent_email_id' => 'integer',
        'complained_at' => 'datetime',
    ];

    public function sentEmail(): BelongsTo
    {
        /**
         * @psalm-suppress InvalidArgument
         * @psalm-suppress InvalidCast
         */
        return $this->belongsTo(ModelResolver::get('SentEmail'));
    }

    public function getId(): mixed
    {
        return $this->getKey();
    }
}
