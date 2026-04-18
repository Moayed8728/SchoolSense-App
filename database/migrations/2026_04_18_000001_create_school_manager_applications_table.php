<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('school_manager_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('fullName');
            $table->string('email');
            $table->string('password');

            $table->string('schoolName');
            $table->string('country', 2)->default('SA');
            $table->string('city');
            $table->string('address')->nullable();
            $table->string('websiteUrl')->nullable();
            $table->string('contactEmail')->nullable();
            $table->string('contactPhone')->nullable();
            $table->string('contactPageUrl')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('feesMin')->nullable();
            $table->unsignedInteger('feesMax')->nullable();
            $table->string('currency', 3)->default('SAR');
            $table->enum('feePeriod', ['yearly', 'semester'])->default('yearly');
            $table->jsonb('curriculumIds')->nullable();
            $table->jsonb('activityIds')->nullable();
            $table->jsonb('languageIds')->nullable();
            $table->text('proofText')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('adminReason')->nullable();
            $table->uuid('reviewedBy')->nullable();
            $table->timestamp('reviewedAt')->nullable();
            $table->uuid('createdUserId')->nullable();
            $table->uuid('createdSchoolId')->nullable();

            $table->timestamps();

            $table->foreign('reviewedBy')->references('id')->on('users')->nullOnDelete();
            $table->foreign('createdUserId')->references('id')->on('users')->nullOnDelete();
            $table->foreign('createdSchoolId')->references('id')->on('schools')->nullOnDelete();
            $table->index(['email', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_manager_applications');
    }
};
