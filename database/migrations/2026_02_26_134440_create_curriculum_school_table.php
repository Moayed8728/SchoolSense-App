<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curriculum_school', function (Blueprint $table) {
        $table->uuid('schoolId');
        $table->uuid('curriculumId');
        $table->timestamps();

        $table->primary(['schoolId', 'curriculumId']);

        $table->foreign('schoolId')->references('id')->on('schools')->onDelete('cascade');
        $table->foreign('curriculumId')->references('id')->on('curricula')->onDelete('cascade');
    });
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum_school');
    }
};