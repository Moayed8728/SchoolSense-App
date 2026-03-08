<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
            Schema::create('activity_school', function (Blueprint $table) {
        $table->uuid('schoolId');
        $table->uuid('activityId');
        $table->timestamps();

        $table->primary(['schoolId', 'activityId']);

        $table->foreign('schoolId')->references('id')->on('schools')->onDelete('cascade');
        $table->foreign('activityId')->references('id')->on('activities')->onDelete('cascade');
    });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_school');
    }
};