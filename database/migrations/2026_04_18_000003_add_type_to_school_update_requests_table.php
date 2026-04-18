<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('school_update_requests') || Schema::hasColumn('school_update_requests', 'type')) {
            return;
        }

        Schema::table('school_update_requests', function (Blueprint $table) {
            $table->enum('type', ['update', 'delete'])->default('update')->after('schoolId');
        });

        DB::table('school_update_requests')
            ->select('id', 'changes')
            ->orderBy('id')
            ->each(function ($request): void {
                $changes = is_string($request->changes)
                    ? json_decode($request->changes, true)
                    : (array) $request->changes;

                if (($changes['_action'] ?? null) !== 'delete') {
                    return;
                }

                unset($changes['_action']);

                DB::table('school_update_requests')
                    ->where('id', $request->id)
                    ->update([
                        'type' => 'delete',
                        'changes' => json_encode($changes),
                    ]);
            });
    }

    public function down(): void
    {
        if (!Schema::hasTable('school_update_requests') || !Schema::hasColumn('school_update_requests', 'type')) {
            return;
        }

        Schema::table('school_update_requests', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
