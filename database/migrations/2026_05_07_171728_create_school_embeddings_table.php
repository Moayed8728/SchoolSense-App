<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $dimensions = (int) config('services.gemini.embedding_dimensions', 768);

        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');

        Schema::create('school_embeddings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('schoolId')->unique();
            $table->uuid('documentId');

            $table->string('model');
            $table->unsignedSmallInteger('dimensions')->default(768);
            $table->string('contentHash', 64);
            $table->timestamp('embeddedAt')->nullable();

            $table->timestamps();

            $table->foreign('schoolId')
                ->references('id')
                ->on('schools')
                ->cascadeOnDelete();

            $table->foreign('documentId')
                ->references('id')
                ->on('school_documents')
                ->cascadeOnDelete();

            $table->index('contentHash');
        });

        DB::statement("ALTER TABLE school_embeddings ADD COLUMN embedding vector({$dimensions})");
    }

    public function down(): void
    {
        Schema::dropIfExists('school_embeddings');
    }
};
