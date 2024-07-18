<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes\Repositories;

use Exception;
use Illuminate\Support\Facades\DB;
use Juhasev\LaravelSes\Contracts\BatchContract;
use Juhasev\LaravelSes\ModelResolver;

class BatchStatRepository
{
    /**
     * @throws Exception
     */
    public static function getSentCount(BatchContract $batch): mixed
    {
        /** @psalm-suppress UndefinedMethod */
        return ModelResolver::get('SentEmail')::where('batch_id', $batch->getId())->count();
    }

    /**
     * @throws Exception
     */
    public static function getOpenedCount(BatchContract $batch): int
    {
        /** @psalm-suppress UndefinedMethod */
        return ModelResolver::get('SentEmail')::where('batch_id', $batch->getId())
            ->join(
                'laravel_ses_email_opens',
                'laravel_ses_sent_emails.id',
                'laravel_ses_email_opens.sent_email_id'
            )
            ->whereNotNull('opened_at')
            ->count();
    }

    /**
     * @throws Exception
     */
    public static function getBouncedCount(BatchContract $batch): int
    {
        /** @psalm-suppress UndefinedMethod */
        return ModelResolver::get('SentEmail')::where('batch_id', $batch->getId())
            ->join(
                'laravel_ses_email_bounces',
                'laravel_ses_sent_emails.id',
                'laravel_ses_email_bounces.sent_email_id'
            )
            ->whereNotNull('bounced_at')
            ->count();
    }

    /**
     * @throws Exception
     */
    public static function getComplaintsCount(BatchContract $batch): int
    {
        /** @psalm-suppress UndefinedMethod */
        return ModelResolver::get('SentEmail')::where('batch_id', $batch->getId())
        ->join(
            'laravel_ses_email_complaints',
            'laravel_ses_sent_emails.id',
            'laravel_ses_email_complaints.sent_email_id'
        )
            ->whereNotNull('complained_at')
            ->count();
    }

    /**
     * @throws Exception
     */
    public static function getDeliveriesCount(BatchContract $batch): int
    {
        /** @psalm-suppress UndefinedMethod */
        return ModelResolver::get('SentEmail')::where('batch_id', $batch->getId())
            ->whereNotNull('delivered_at')
            ->count();
    }

    /**
     * @throws Exception
     */
    public static function getClicksCount(BatchContract $batch): int
    {
        /** @psalm-suppress UndefinedMethod */
        return ModelResolver::get('SentEmail')::where('laravel_ses_sent_emails.batch_id', $batch->getId())
            ->join('laravel_ses_email_links', function ($join) {
                $join
                    ->on('laravel_ses_sent_emails.id', '=', 'sent_email_id')
                    ->where('laravel_ses_email_links.clicked', '=', true);
            })
            ->select('email')
            ->count(DB::raw('DISTINCT(email)'));
    }

    /**
     * @throws Exception
     */
    public static function getLinkPopularity(BatchContract $batch): array
    {
        /** @psalm-suppress UndefinedMethod */
        return ModelResolver::get('SentEmail')::where('laravel_ses_sent_emails.batch_id', $batch->getId())
            ->join('laravel_ses_email_links', function ($join) {
                $join
                    ->on('laravel_ses_sent_emails.id', '=', 'sent_email_id')
                    ->where('laravel_ses_email_links.clicked', '=', true);
            })
            ->get()
            ->groupBy('original_url')
            ->map(function ($linkClicks) {
                return ['clicks' => $linkClicks->count()];
            })
            ->sortByDesc('clicks')
            ->toArray();
    }
}