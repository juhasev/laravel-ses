<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Juhasev\LaravelSes\Controllers\BounceController;
use Juhasev\LaravelSes\Controllers\ComplaintController;
use Juhasev\LaravelSes\Controllers\DeliveryController;
use Juhasev\LaravelSes\Controllers\LinkController;
use Juhasev\LaravelSes\Controllers\OpenController;

Route::prefix('/ses')->group(function () {

    //receive SNS notifications
    Route::post('notification/bounce', [BounceController::class, 'bounce']);
    Route::post('notification/delivery', [DeliveryController::class, 'delivery']);
    Route::post('notification/complaint', [ComplaintController::class, 'complaint']);

    //user tracking
    Route::get('beacon/{beaconIdentifier}', [OpenController::class, 'open']);
    Route::get('link/{linkId}', [LinkController::class, 'click']);
});
