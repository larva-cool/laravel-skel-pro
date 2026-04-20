<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use App\Enum\StatusSwitch;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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

            $table->index(['type', 'status', 'order', 'created_at']);
            $table->comment('用户协议');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agreements');
    }
};
