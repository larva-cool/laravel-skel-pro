<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateMonthlyPartitionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:create-partition';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动创建分区';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (DB::connection()->getConfig('driver') == 'mysql') {
            $tablePrefix = DB::connection()->getTablePrefix();
            // 创建分区
            $this->createPartition($tablePrefix.'coin_trades');
        }
    }

    /**
     * 创建分区
     */
    protected function createPartition($table): void
    {
        // 生成下个月分区（提前1个月）
        $date = now()->addMonth();
        $month = $date->format('Ym');
        $partitionName = "p{$month}";
        $lessThan = $date->copy()->addMonth()->format('Y-m-01');
        $unixTimestamp = strtotime($lessThan);

        // 检查分区是否已存在
        $exists = DB::select('
            SELECT 1 FROM INFORMATION_SCHEMA.PARTITIONS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND PARTITION_NAME = ?
        ', [$table, $partitionName]);

        if ($exists) {
            $this->warn("{$table} 分区已存在：{$partitionName}");

            return;
        }

        // ✅ 核心：拆分 p_future 分区（解决 MAXVALUE 错误）
        DB::statement("
            ALTER TABLE {$table}
            REORGANIZE PARTITION p_future INTO (
                PARTITION {$partitionName} VALUES LESS THAN ({$unixTimestamp}),
                PARTITION p_future VALUES LESS THAN MAXVALUE
            )
        ");

        $this->info("{$table} 分区创建成功：{$partitionName}");
    }
}
