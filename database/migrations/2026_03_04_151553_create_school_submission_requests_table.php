<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('school_submission_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('userId');

            // proposed school data (pending)
            $table->string('name');
            $table->string('country', 2)->default('SA');
            $table->string('city')->nullable();
            $table->string('address')->nullable();

            $table->string('websiteUrl')->nullable();
            $table->string('contactEmail')->nullable();
            $table->string('contactPhone')->nullable();
            $table->string('contactPageUrl')->nullable();

            $table->text('description')->nullable();

            $table->integer('feesMin')->nullable();
            $table->integer('feesMax')->nullable();
            $table->string('currency', 3)->default('SAR');
            $table->enum('feePeriod', ['yearly', 'semester'])->default('yearly');

            // store chosen ids as JSON arrays (admin will attach pivots on approve)
            $table->jsonb('curriculumIds')->nullable();
            $table->jsonb('activityIds')->nullable();
            $table->jsonb('languageIds')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('adminReason')->nullable();

            $table->uuid('reviewedBy')->nullable();
            $table->timestamp('reviewedAt')->nullable();

            $table->timestamps();

            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewedBy')->references('id')->on('users')->nullOnDelete();

            $table->index(['userId', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_submission_requests');
    }
};