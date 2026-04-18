<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use App\Enum\StatusSwitch;
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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id()->comment('公告ID');
            $table->json('coverage')->comment('覆盖范围');
            $table->string('title')->comment('标题');
            $table->text('content')->nullable()->comment('内容');
            $table->string('image')->nullable()->comment('图片');
            $table->string('jump_url')->nullable()->comment('跳转URL');
            $table->unsignedBigInteger('admin_id')->index()->comment('发布者');
            $table->unsignedTinyInteger('status')->default(StatusSwitch::ENABLED->value)->comment('状态');
            $table->unsignedTinyInteger('effective_time_type')->default(0)->comment('生效时间类型,0:立即生效,1:定时生效');
            $table->timestamp('effective_start_time')->nullable()->comment('生效开始时间');
            $table->timestamp('effective_end_time')->nullable()->comment('生效结束时间');
            $table->unsignedInteger('read_count')->default(0)->comment('已读次数');
            $table->index(['status', 'effective_time_type'], 'idx_active');
            $table->timestamps();
            $table->comment('公告表');
        });

        Schema::create('announcement_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('announcement_id')->index()->comment('公告ID');
            $table->morphs('user');
            $table->timestamp('created_at')->nullable()->comment('创建时间');
            $table->comment('公告已读');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_reads');
        Schema::dropIfExists('announcements');
    }
};
