<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

use App\Enums\UserStatus;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id()->from(10000000)->comment('用户ID');
            $table->string('username', 50)->unique()->nullable()->comment('用户名');
            $table->string('email')->unique()->nullable()->comment('邮箱');
            $table->string('phone', 30)->unique()->nullable()->comment('手机号（支持国际格式，如+8613800138000）');
            $table->string('name', 50)->nullable()->comment('昵称');
            $table->string('avatar', 1000)->nullable()->comment('头像');
            $table->unsignedTinyInteger('status')->default(UserStatus::STATUS_ACTIVE->value)->comment('状态：0、frozen,1、active，2、not_active');
            $table->unsignedInteger('available_points')->nullable()->default(0)->comment('可用积分');
            $table->unsignedInteger('available_coins')->nullable()->default(0)->comment('可用金币');
            $table->string('password')->nullable()->comment('密码');
            $table->rememberToken()->comment('记住我token');
            $table->unsignedBigInteger('login_count')->nullable()->default(0)->comment('登录次数');
            $table->ipAddress('last_login_ip')->nullable()->comment('最后登录IP地址');
            $table->dateTime('vip_expires_at')->nullable()->comment('VIP过期时间');
            $table->timestamp('last_active_at')->nullable()->comment('最后活动时间');
            $table->timestamp('last_login_at')->nullable()->comment('最后登录时间');
            $table->timestamps();
            $table->softDeletes()->comment('删除时间');

            $table->comment('用户表');
        });

        Schema::create('user_extras', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary()->comment('用户ID');
            $table->unsignedTinyInteger('username_change_count')->default(0)->nullable()->comment('用户名修改次数');

            $table->comment('用户扩展信息表');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary()->comment('邮箱');
            $table->string('token')->comment('Token');
            $table->timestamp('created_at')->nullable()->comment('创建时间');

            $table->comment('密码重置表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('user_extras');
        Schema::dropIfExists('users');
    }
};
