<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
        $table->uuid('id')->primary();

        $table->uuid('userId');
        $table->uuid('schoolId');

        $table->timestamps();

        $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('schoolId')->references('id')->on('schools')->onDelete('cascade');

        $table->unique(['userId', 'schoolId']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
