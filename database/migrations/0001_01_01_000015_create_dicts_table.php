<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use App\Enum\StatusSwitch;
use App\Models\System\Dict;
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
        Schema::create('dicts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable()->comment('父ID');
            $table->string('name')->comment('字典名称');
            $table->string('description')->nullable()->comment('字典描述');
            $table->string('code')->comment('字典编码');
            $table->unsignedTinyInteger('status')->default(StatusSwitch::ENABLED->value)->comment('状态');
            $table->unsignedInteger('order')->nullable()->default(99)->comment('排序');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['code', 'deleted_at'], 'uk_code');
            $table->index(['parent_id', 'status', 'deleted_at', 'order'], 'idx_parent_status');
            $table->index(['deleted_at', 'status', 'order'], 'idx_list');

            $table->comment('字典表');
        });

        $feedbackType = Dict::create([
            'name' => '反馈类型',
            'code' => 'FEEDBACK_TYPE',
        ]);
        $feedbackType->children()->createMany([
            ['name' => '分类1', 'code' => 'category1'],
            ['name' => '分类2', 'code' => 'category2'],
            ['name' => '分类3', 'code' => 'category3'],
        ]);

        $reportType = Dict::create([
            'name' => '举报类型',
            'code' => 'REPORT_TYPE',
        ]);
        $reportType->children()->createMany([
            ['name' => '分类1', 'code' => 'category1'],
            ['name' => '分类2', 'code' => 'category2'],
            ['name' => '分类3', 'code' => 'category3'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dicts');
    }
};
