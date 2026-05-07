<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('schoolId')->unique();

            $table->longText('content');
            $table->string('contentHash', 64);
            $table->timestamp('generatedAt')->nullable();

            $table->timestamps();

            $table->foreign('schoolId')
                ->references('id')
                ->on('schools')
                ->cascadeOnDelete();

            $table->index('contentHash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_documents');
    }
};