<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_contact_sources', function (Blueprint $table) {
        $table->uuid('id')->primary();

        $table->uuid('schoolId');

        $table->string('sourceUrl');
        $table->string('domain')->nullable();

        $table->enum('status', ['pending', 'success', 'failed', 'blocked'])->default('pending');

        $table->timestamp('lastScrapedAt')->nullable();
        $table->text('lastError')->nullable();

        $table->unsignedSmallInteger('pagesScraped')->default(0);

        $table->timestamps();

        $table->foreign('schoolId')->references('id')->on('schools')->onDelete('cascade');

        $table->unique(['schoolId', 'sourceUrl']);
    });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_contact_sources');
    }
};