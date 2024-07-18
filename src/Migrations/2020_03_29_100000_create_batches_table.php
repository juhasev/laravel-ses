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
        Schema::create('laravel_ses_batches', static function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique()->nullable()->default(null);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laravel_ses_batches');
    }
};
