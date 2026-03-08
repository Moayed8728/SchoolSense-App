<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('school_rep_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('userId');
            $table->string('fullName');
            $table->string('schoolName');
            $table->string('websiteUrl')->nullable();
            $table->text('proofText')->nullable();

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
        Schema::dropIfExists('school_rep_requests');
    }
};