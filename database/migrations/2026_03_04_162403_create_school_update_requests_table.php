<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('school_update_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('userId');
            $table->uuid('schoolId');
            $table->enum('type', ['update', 'delete'])->default('update');

            // Only changed fields - store as json patch
            $table->jsonb('changes');

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('adminReason')->nullable();

            $table->uuid('reviewedBy')->nullable();
            $table->timestamp('reviewedAt')->nullable();

            $table->timestamps();

            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('schoolId')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('reviewedBy')->references('id')->on('users')->nullOnDelete();

            $table->index(['userId', 'schoolId', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_update_requests');
    }
};
