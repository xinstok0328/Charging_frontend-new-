<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure required extension for EXCLUDE constraints on ranges
        DB::statement("CREATE EXTENSION IF NOT EXISTS btree_gist");

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('pile_id');
            $table->timestampTz('start_time');
            $table->timestampTz('end_time');
            $table->string('status', 32)->default('confirmed'); // pending | confirmed | cancelled | completed
            $table->timestampsTz();

            $table->index(['pile_id', 'start_time']);
            $table->index(['user_id', 'end_time']);
        });

        // Unique active reservation per user (partial index)
        DB::statement(<<<SQL
            CREATE UNIQUE INDEX IF NOT EXISTS uniq_user_active_resv
            ON reservations(user_id)
            WHERE status IN ('pending','confirmed') AND end_time > now();
        SQL);

        // Prevent overlapping reservations per pile for active statuses using EXCLUDE with tstzrange
        DB::statement(<<<SQL
            ALTER TABLE reservations
            ADD CONSTRAINT ex_pile_time_overlap
            EXCLUDE USING GIST (
                pile_id WITH =,
                tstzrange(start_time, end_time, '[)') WITH &&
            )
            WHERE (status IN ('pending','confirmed'));
        SQL);
    }

    public function down(): void
    {
        // Drop constraint and index explicitly before dropping table
        try { DB::statement("ALTER TABLE reservations DROP CONSTRAINT IF EXISTS ex_pile_time_overlap"); } catch (\Throwable $e) {}
        try { DB::statement("DROP INDEX IF EXISTS uniq_user_active_resv"); } catch (\Throwable $e) {}
        Schema::dropIfExists('reservations');
    }
};


