<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('language_school', function (Blueprint $table) {
        $table->uuid('schoolId');
        $table->uuid('languageId');
        $table->timestamps();

        $table->primary(['schoolId', 'languageId']);

        $table->foreign('schoolId')->references('id')->on('schools')->onDelete('cascade');
        $table->foreign('languageId')->references('id')->on('languages')->onDelete('cascade');
    });
    }

    public function down(): void
    {
        Schema::dropIfExists('language_school');
    }
};