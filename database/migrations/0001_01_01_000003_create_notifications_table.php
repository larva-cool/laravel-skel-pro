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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('通知ID');
            $table->string('type')->comment('通知类型');
            $table->unsignedBigInteger('notifiable_id')->comment('notifiableID');
            $table->string('notifiable_type')->comment('notifiable类型');
            $table->text('data')->comment('通知数据');
            $table->timestamp('read_at')->nullable()->comment('阅读时间');
            $table->timestamps();
            $table->index(['notifiable_type', 'notifiable_id'], 'uniq_notifiable');

            $table->comment('消息通知表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
