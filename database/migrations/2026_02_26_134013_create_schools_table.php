<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
        $table->uuid('id')->primary();

        $table->string('name');
        $table->string('country', 2);
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

        $table->timestamps();
        $table->softDeletes();

        $table->index(['country', 'city']);
        $table->index(['feesMin', 'feesMax']);
    });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};