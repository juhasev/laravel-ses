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
        Schema::create('laravel_ses_email_complaints', static function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('sent_email_id');
            $table->string('type');
            $table->dateTime('complained_at');

            $table->foreign('sent_email_id')
                ->references('id')
                ->on('laravel_ses_sent_emails')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laravel_ses_email_complaints');
    }
};
