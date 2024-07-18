<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Juhasev\LaravelSes\Contracts\BatchContract;
use Juhasev\LaravelSes\ModelResolver;

class Batch extends Model implements BatchContract
{
    protected $table = 'laravel_ses_batches';

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function sentEmails(): HasMany
    {
        /**
         * @psalm-suppress InvalidArgument
         * @psalm-suppress InvalidCast
         */
        return $this->hasMany(ModelResolver::get('SentEmail'));
    }

    public static function resolve(string $name): BatchContract|null
    {
        return self::where('name', $name)->first();
    }

    public function getId(): mixed
    {
        return $this->getKey();
    }
}
