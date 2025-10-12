<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            // 可視需要改成 ->constrained() 連外鍵
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('pile_id');

            // 你原本用的是 timestamptz，Laravel 的 timestampTz 在 sqlite 也能工作
            $table->timestampTz('start_time');
            $table->timestampTz('end_time');

            // pending | confirmed | cancelled | completed
            $table->string('status', 32)->default('confirmed');

            $table->timestampsTz();

            // 常用查詢索引
            $table->index(['pile_id', 'start_time']);
            $table->index(['user_id', 'end_time']);
        });

        $driver = DB::getDriverName();

        /**
         * 一位使用者同時間只允許一筆「有效（pending/confirmed）且尚未結束」的預約：
         *  - PG：建立 partial unique index（支援 WHERE）
         *  - SQLite：支援 WHERE，但時間函式要用 CURRENT_TIMESTAMP
         *  - MySQL：不支援 partial index（用應用層檢查）
         */
        $driver = DB::getDriverName();

if ($driver === 'pgsql') {
    DB::statement("CREATE EXTENSION IF NOT EXISTS btree_gist");

    DB::statement(<<<SQL
        CREATE UNIQUE INDEX IF NOT EXISTS uniq_user_active_resv
        ON reservations(user_id)
        WHERE status IN ('pending','confirmed') AND end_time > NOW();
    SQL);

    DB::statement(<<<SQL
        ALTER TABLE reservations
        ADD CONSTRAINT ex_pile_time_overlap
        EXCLUDE USING GIST (
            pile_id WITH =,
            tstzrange(start_time, end_time, '[)') WITH &&
        )
        WHERE (status IN ('pending','confirmed'));
    SQL);

} elseif ($driver === 'sqlite') {
    // ❌ 不要做以下這種帶 CURRENT_TIMESTAMP 的 partial unique index（會報你看到的錯）
    // DB::statement(<<<SQL
    //     CREATE UNIQUE INDEX IF NOT EXISTS uniq_user_active_resv
    //     ON reservations(user_id)
    //     WHERE status IN ('pending','confirmed') AND end_time > CURRENT_TIMESTAMP
    // SQL);

    // ✅ 改成一般索引，效能 OK，唯一性用「應用層檢查」來保證
    DB::statement(<<<SQL
        CREATE INDEX IF NOT EXISTS idx_user_end_time ON reservations(user_id, end_time)
    SQL);
    DB::statement(<<<SQL
        CREATE INDEX IF NOT EXISTS idx_pile_time ON reservations(pile_id, start_time, end_time)
    SQL);

} else {
    // MySQL/其他：同樣不支援 partial index，給一般索引，在應用層檢查
    DB::statement("CREATE INDEX IF NOT EXISTS idx_user_end_time ON reservations(user_id, end_time)");
    DB::statement("CREATE INDEX IF NOT EXISTS idx_pile_time ON reservations(pile_id, start_time, end_time)");
}

    }

    public function down(): void
    {
        // 解除 PG 的 constraint / index
        try { DB::statement("ALTER TABLE reservations DROP CONSTRAINT IF EXISTS ex_pile_time_overlap"); } catch (\Throwable $e) {}
        try { DB::statement("DROP INDEX IF EXISTS uniq_user_active_resv"); } catch (\Throwable $e) {}

        // 解除 SQLite / MySQL 的輔助索引（存在則刪）
        try { DB::statement("DROP INDEX IF EXISTS idx_pile_time_active"); } catch (\Throwable $e) {}
        try { DB::statement("DROP INDEX IF EXISTS idx_user_end_time"); } catch (\Throwable $e) {}
        try { DB::statement("DROP INDEX IF EXISTS idx_pile_time"); } catch (\Throwable $e) {}

        Schema::dropIfExists('reservations');
    }
};