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
        Schema::create('settings', function (Blueprint $table) {
            $table->id()->comment('配置ID');
            $table->string('name', 255)->nullable()->comment('配置名称');
            $table->string('key', 100)->index()->comment('配置键名');
            $table->text('value')->nullable()->comment('配置值');
            $table->string('cast_type', 20)->nullable()->default('string')->comment('变量类型');
            $table->string('input_type')->nullable()->default('text')->comment('输入类型');
            $table->mediumText('param')->nullable()->comment('配置参数');
            $table->unsignedSmallInteger('order')->nullable()->default(99)->comment('排序');
            $table->string('remark')->nullable()->comment('备注');
            $table->timestamp('updated_at')->nullable()->comment('最后更新时间');
            $table->unique(['key']);

            $table->comment('参数配置信息表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
