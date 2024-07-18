<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes\Repositories;

use Exception;
use Illuminate\Support\Collection;
use Juhasev\LaravelSes\ModelResolver;
use Juhasev\LaravelSes\Models\SentEmail;

class EmailRepository
{
    /**
     * @return Collection<int, SentEmail>
     * @throws Exception
     */
    public static function getSent(string $email): Collection
    {
        /** @psalm-suppress UndefinedMethod */
        return ModelResolver::get('SentEmail')::whereEmail($email)->get();
    }

    /**
     * @return Collection<int, SentEmail>
     * @throws Exception
     */
    public static function getDeliveries(string $email): Collection
    {
        /** @psalm-suppress UndefinedMethod */
        return ModelResolver::get('SentEmail')::whereEmail($email)->whereNotNull('delivered_at')->get();
    }

    /**
     * @return Collection<int, SentEmail>
     * @throws Exception
     */
    public static function getOpens(string $email): Collection
    {
        /** @psalm-suppress UndefinedMethod */
        return ModelResolver::get('SentEmail')::whereEmail($email)
            ->with('emailOpen')
            ->whereHas('emailOpen', function ($query) {
                $query->whereNotNull('opened_at');
            })->get();
    }

    /**
     * @return Collection<int, SentEmail>
     * @throws Exception
     */
    public static function getBounces(string $email): Collection
    {
        /** @psalm-suppress UndefinedMethod */
        return ModelResolver::get('SentEmail')::whereEmail($email)
            ->with('emailBounce')
            ->whereHas('emailBounce', function ($query) {
                $query->whereNotNull('bounced_at');
            })->get();
    }

    /**
     * @return Collection<int, SentEmail>
     * @throws Exception
     */
    public static function getComplaints(string $email): Collection
    {
        /** @psalm-suppress UndefinedMethod */
        return ModelResolver::get('SentEmail')::whereEmail($email)
            ->with('emailComplaint')
            ->whereHas('emailComplaint', function ($query) {
                $query->whereNotNull('complained_at');
            })->get();
    }

    /**
     * @return Collection<int, SentEmail>
     * @throws Exception
     */

    public static function getClicks(string $email): Collection
    {
        /** @psalm-suppress UndefinedMethod */
        return ModelResolver::get('SentEmail')::whereEmail($email)
            ->with(['emailLinks' => function ($query) {
                $query->where('clicked', true);
            }])
            ->whereHas('emailLinks', function ($query) {
                $query->where('clicked', true);
            })->get();
    }
}