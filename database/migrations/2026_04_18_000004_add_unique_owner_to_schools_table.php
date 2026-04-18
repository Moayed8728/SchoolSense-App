<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('schools') || !Schema::hasColumn('schools', 'ownerUserId')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            $table->unique('ownerUserId');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('schools') || !Schema::hasColumn('schools', 'ownerUserId')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            $table->dropUnique(['ownerUserId']);
        });
    }
};
