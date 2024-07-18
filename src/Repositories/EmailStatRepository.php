<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes\Repositories;

use Exception;
use Illuminate\Support\Facades\DB;
use Juhasev\LaravelSes\ModelResolver;

class EmailStatRepository
{
    /**
     * @throws Exception
     */
    public static function getSentCount(string $email): int
    {
        /** @psalm-suppress UndefinedMethod */
        return ModelResolver::get('SentEmail')::whereEmail($email)->count();
    }

    /**
     * @throws Exception
     */
    public static function getDeliveriesCount(string $email): int
    {
        /** @psalm-suppress UndefinedMethod */
        return ModelResolver::get('SentEmail')::whereEmail($email)->whereNotNull('delivered_at')->count();
    }

    /**
     * @throws Exception
     */
    public static function getOpenedCount(string $email): int
    {
        /** @psalm-suppress UndefinedMethod */
        return ModelResolver::get('SentEmail')::whereEmail($email)
            ->withCount(['emailOpen' => function ($query) {
                $query->whereNotNull('opened_at');
            }])->get()->sum('email_open_count');
    }

    /**
     * @throws Exception
     */
    public static function getComplaintsCount(string $email): int
    {
        /** @psalm-suppress UndefinedMethod */
        return ModelResolver::get('SentEmail')::whereEmail($email)
            ->withCount(['emailComplaint' => function ($query) {
                $query->whereNotNull('complained_at');
            }])->get()->sum('email_complaint_count');
    }

    /**
     * @throws Exception
     */
    public static function getBouncedCount(string $email): int
    {
        /** @psalm-suppress UndefinedMethod */
        return ModelResolver::get('SentEmail')::whereEmail($email)
            ->withCount(['emailBounce' => function ($query) {
                $query->whereNotNull('bounced_at');
            }])->get()->sum('email_bounce_count');
    }

    /**
     * @throws Exception
     */
    public static function getClicksCount(string $email): int
    {
        /** @psalm-suppress UndefinedMethod */
        return ModelResolver::get('EmailLink')::join(
            'laravel_ses_sent_emails',
            'laravel_ses_sent_emails.id',
            'laravel_ses_email_links.sent_email_id'
        )
            ->where('laravel_ses_sent_emails.email', '=', $email)
            ->whereClicked(true)
            ->count(DB::raw('DISTINCT(laravel_ses_sent_emails.id)'));
    }
}