<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use App\Enums\FeedbackStatus;
use App\Enums\FeedbackType;
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
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id()->comment('反馈ID');
            $table->unsignedBigInteger('user_id')->index()->comment('反馈用户');
            $table->string('type', 32)->default(FeedbackType::OTHER->value)->comment('反馈类型');
            $table->string('title', 120)->nullable()->comment('反馈标题');
            $table->string('content', 2000)->comment('反馈内容');
            $table->string('contact', 100)->nullable()->comment('联系方式');
            $table->json('attachments')->nullable()->comment('附件URL');
            $table->string('status', 20)->default(FeedbackStatus::PENDING->value)->comment('处理状态');
            $table->text('reply')->nullable()->comment('管理员回复');
            $table->unsignedBigInteger('handled_by')->nullable()->comment('处理人');
            $table->timestamp('handled_at')->nullable()->comment('处理时间');
            $table->ipAddress('ip_address')->nullable()->comment('提交IP');
            $table->string('user_agent', 255)->nullable()->comment('客户端UA');
            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'idx_feedback_user_at');
            $table->index(['status', 'created_at'], 'idx_feedback_status_at');
            $table->index('type', 'idx_feedback_type');
            $table->comment('用户反馈表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
