<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use App\Enums\ReportStatus;
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
        Schema::create('reports', function (Blueprint $table) {
            $table->id()->comment('举报ID');
            $table->unsignedBigInteger('user_id')->index()->comment('举报人');
            $table->string('reportable_type', 64)->comment('被举报对象类型');
            $table->unsignedBigInteger('reportable_id')->comment('被举报对象ID');
            $table->string('reason', 32)->comment('举报原因');
            $table->string('content', 500)->nullable()->comment('补充说明');
            $table->json('evidence')->nullable()->comment('证据URL');
            $table->string('status', 20)->default(ReportStatus::PENDING->value)->comment('处理状态');
            $table->text('remark')->nullable()->comment('管理员备注');
            $table->unsignedBigInteger('handled_by')->nullable()->comment('处理人');
            $table->timestamp('handled_at')->nullable()->comment('处理时间');
            $table->ipAddress('ip_address')->nullable()->comment('举报IP');
            $table->timestamps();

            $table->index(['reportable_type', 'reportable_id', 'created_at'], 'idx_report_target');
            $table->index(['user_id', 'created_at'], 'idx_report_user_at');
            $table->index(['status', 'created_at'], 'idx_report_status_at');
            $table->comment('举报表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
