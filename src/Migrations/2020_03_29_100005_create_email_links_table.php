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
        Schema::create('laravel_ses_email_links', static function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('link_identifier')->index();
            $table->unsignedBigInteger('sent_email_id');
            $table->text('original_url');
            $table->boolean('clicked')->default(false);
            $table->unsignedSmallInteger('click_count')->default(0);

            $table->foreign('sent_email_id')
                ->references('id')
                ->on('laravel_ses_sent_emails')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laravel_ses_email_links');
    }
};
