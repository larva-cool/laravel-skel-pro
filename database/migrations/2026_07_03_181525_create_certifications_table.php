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
        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->morphs('certifiable'); // certifiable_type + certifiable_id

            // 认证信息
            $table->string('type', 20)->default('personal')->comment('认证类型: personal 个人 / enterprise 企业');
            $table->string('real_name', 100)->comment('真实姓名/企业名称');
            $table->string('id_card_no', 50)->comment('身份证号/营业执照号');
            $table->string('id_card_front')->nullable()->comment('证件正面照片');
            $table->string('id_card_back')->nullable()->comment('证件背面照片');
            $table->string('id_card_in_hand')->nullable()->comment('手持证件照片');
            $table->string('license')->nullable()->comment('营业执照照片');

            // 企业联系信息
            $table->string('contact_person', 50)->nullable()->comment('联系人');
            $table->string('contact_phone', 20)->nullable()->comment('联系手机');
            $table->string('contact_email', 100)->nullable()->comment('联系邮箱');

            // 审核状态
            $table->unsignedTinyInteger('status')->default(0)->comment('认证状态: 0 未提交 1 待审核 2 被拒绝 3 已认证');
            $table->string('failed_reason', 500)->nullable()->comment('失败原因');
            $table->timestamp('verified_at')->nullable()->comment('认证通过时间');
            $table->timestamp('submitted_at')->nullable()->comment('提交时间');
            $table->timestamp('updated_at')->nullable();

            $table->unique(['certifiable_type', 'certifiable_id']);
            $table->comment('实名认证表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};
