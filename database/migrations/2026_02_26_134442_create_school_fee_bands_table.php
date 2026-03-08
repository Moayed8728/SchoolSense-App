<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_fee_bands', function (Blueprint $table) {
        $table->uuid('id')->primary();

        $table->uuid('schoolId');

        $table->string('gradeFrom'); // KG1, G1
        $table->string('gradeTo');

        $table->unsignedSmallInteger('gradeFromOrder');
        $table->unsignedSmallInteger('gradeToOrder');

        $table->unsignedInteger('feesMin')->nullable();
        $table->unsignedInteger('feesMax')->nullable();
        $table->string('currency', 3)->default('SAR');
        $table->enum('feePeriod', ['yearly', 'semester'])->default('yearly');

        $table->timestamps();

        $table->foreign('schoolId')->references('id')->on('schools')->onDelete('cascade');

        $table->index(['gradeFromOrder', 'gradeToOrder']);
    });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_fee_bands');
    }
};