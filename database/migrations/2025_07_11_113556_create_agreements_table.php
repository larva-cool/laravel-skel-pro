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
        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('类型');
            $table->string('title', 500)->comment('标题');
            $table->text('content')->comment('内容');
            $table->unsignedBigInteger('admin_id')->index()->comment('发布者');
            $table->unsignedInteger('order')->nullable()->default(0)->comment('排序');
            $table->unsignedTinyInteger('status')->nullable()->default(StatusSwitch::ENABLED->value)->comment('状态');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status']);
            $table->comment('用户协议');
        });

        Schema::create('agreement_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agreement_id')->comment('协议ID');
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->timestamp('created_at')->nullable()->comment('创建时间');
            $table->index(['agreement_id', 'user_id'], 'idx_agreement_user');
            $table->comment('协议已读');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agreement_reads');
        Schema::dropIfExists('agreements');
    }
};
