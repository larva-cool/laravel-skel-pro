<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_stats', function (Blueprint $table) {
            $table->id();
            $table->date('stat_date')->index()->comment('统计日期');
            $table->unsignedBigInteger('total_user_count')->default(0)->comment('用户总数');
            $table->unsignedBigInteger('total_coin_count')->default(0)->comment('金币总数');
            $table->unsignedBigInteger('total_point_count')->default(0)->comment('总积分数');
            $table->unsignedBigInteger('new_user_count')->default(0)->comment('注册用户数');
            $table->unsignedBigInteger('active_user_count')->default(0)->comment('活跃用户总数');
            $table->unsignedBigInteger('coin_transaction_count')->default(0)->comment('金币交易总数');
            $table->unsignedBigInteger('point_transaction_count')->default(0)->comment('积分交易总数');
            $table->timestamp('created_at')->nullable()->comment('统计时间');
            $table->index(['stat_date', 'created_at']);
            
            $table->comment('用户统计表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_stats');
    }
};
