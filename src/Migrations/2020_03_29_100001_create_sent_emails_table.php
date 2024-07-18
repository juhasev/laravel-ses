<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes\Migrations;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laravel_ses_sent_emails', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('batch_id')->nullable();
            $table->string('message_id')->index();
            $table->string('email')->index();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->boolean('complaint_tracking')->default(false);
            $table->boolean('delivery_tracking')->default(false);
            $table->boolean('bounce_tracking')->default(false);
            $table->timestamps();

            $table->foreign('batch_id')
                ->references('id')
                ->on('laravel_ses_batches')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laravel_ses_sent_emails');
    }
};
