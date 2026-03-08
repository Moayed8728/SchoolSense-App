<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_contact_extractions', function (Blueprint $table) {
        $table->uuid('id')->primary();

        $table->uuid('schoolId');
        $table->uuid('sourceId')->nullable();

        $table->json('foundEmails')->nullable();
        $table->json('foundPhones')->nullable();
        $table->json('foundAddresses')->nullable();
        $table->json('foundSocialLinks')->nullable();
        $table->json('pages')->nullable();

        $table->uuid('approvedBy')->nullable();
        $table->timestamp('approvedAt')->nullable();

        $table->timestamps();

        $table->foreign('schoolId')->references('id')->on('schools')->onDelete('cascade');
        $table->foreign('sourceId')->references('id')->on('school_contact_sources')->nullOnDelete();
        $table->foreign('approvedBy')->references('id')->on('users')->nullOnDelete();
    });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_contact_extractions');
    }
};