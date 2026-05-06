<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $lastMonth = 'p'.Carbon::now()->subMonth()->format('Ym');
        $month = Carbon::now()->format('Y-m-01');
        if (DB::connection()->getConfig('driver') == 'mysql') {
            $tableName = DB::connection()->getTablePrefix().'coin_trades';
            DB::statement("ALTER TABLE `$tableName` DROP PRIMARY KEY, ADD PRIMARY KEY (id, user_id, created_at)");
            DB::statement("ALTER TABLE `$tableName`
            PARTITION BY RANGE ( UNIX_TIMESTAMP(created_at) ) (
                PARTITION $lastMonth VALUES LESS THAN (UNIX_TIMESTAMP('$month')),
                PARTITION p_future VALUES LESS THAN MAXVALUE
            )");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getConfig('driver') == 'mysql') {
            $tableName = DB::connection()->getTablePrefix().'coin_trades';
            DB::statement("ALTER TABLE `$tableName` REMOVE PARTITIONING");
            DB::statement("ALTER TABLE `$tableName` DROP PRIMARY KEY,ADD PRIMARY KEY (id)");
        }
    }
};
