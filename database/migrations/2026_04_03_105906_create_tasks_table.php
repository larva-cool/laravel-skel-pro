<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('任务分组名称');
            $table->string('description')->nullable()->comment('任务分组描述');
            $table->string('type')->comment('任务类型');
            $table->unsignedTinyInteger('visibility')->default(1)->comment('任务分组可见性');
            $table->unsignedTinyInteger('status')->default(1)->comment('任务分组状态');
            $table->unsignedInteger('order')->default(0)->comment('任务分组排序');
            $table->unsignedBigInteger('log_count')->nullable()->default(0)->comment('日志数');
            $table->timestamps();
            $table->softDeletes();
            $table->comment('任务分组表');
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id')->comment('任务分组ID');
            $table->string('name')->comment('任务名称');
            $table->string('type')->comment('任务类型');
            $table->unsignedInteger('coins')->default(0)->comment('任务奖励金币');
            $table->boolean('activity_bonus')->default(false)->comment('是否开启活跃度奖励');
            $table->string('description')->nullable()->comment('任务描述');
            $table->json('condition')->nullable()->comment('任务条件');
            $table->unsignedTinyInteger('status')->default(1)->comment('任务状态');
            $table->unsignedInteger('order')->default(0)->comment('任务排序');
            $table->unsignedBigInteger('log_count')->nullable()->default(0)->comment('日志数');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['group_id', 'type', 'status']);

            $table->foreign('group_id')->references('id')->on('task_groups')->onDelete('cascade');
            $table->comment('任务表');
        });

        Schema::create('task_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id')->index()->comment('任务分组ID');
            $table->unsignedBigInteger('task_id')->comment('任务ID');
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->unsignedInteger('coins')->default(0)->comment('任务奖励金币');
            $table->string('trade_id')->nullable()->comment('交易ID');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->nullable()->comment('交易时间');
            $table->timestamp('updated_at')->nullable()->comment('更新时间');

            $table->index(['task_id', 'user_id']);
            $table->comment('任务日志表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_logs');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('task_groups');
    }
};
