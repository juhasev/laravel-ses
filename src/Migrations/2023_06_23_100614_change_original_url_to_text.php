<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('laravel_ses_email_links', function (Blueprint $table) {
            $table->text('original_url')->change();
        });
    }

    public function down(): void
    {
        Schema::table('laravel_ses_email_links', function (Blueprint $table) {
            $table->string('original_url')->change();
        });
    }
};
