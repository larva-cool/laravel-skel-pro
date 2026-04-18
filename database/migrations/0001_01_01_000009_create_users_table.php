<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use App\Enum\Gender;
use App\Enum\UserStatus;
use App\Models\User\UserGroup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_groups', function (Blueprint $table) {
            $table->id()->comment('用户组ID');
            $table->string('name')->comment('用户组名称');
            $table->string('desc', 500)->nullable()->comment('用户组描述');
            $table->timestamps();
            $table->comment('用户组表');
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id()->from(10000000)->comment('用户ID');
            $table->unsignedBigInteger('group_id')->nullable()->comment('用户组ID');
            $table->string('username')->nullable()->comment('用户名');
            $table->string('email')->nullable()->comment('邮箱');
            $table->string('phone', 20)->nullable()->comment('手机号（支持国际格式，如+8613800138000）');
            $table->string('name')->nullable()->comment('昵称');
            $table->string('avatar', 1000)->nullable()->comment('头像');
            $table->unsignedTinyInteger('status')->default(UserStatus::STATUS_ACTIVE->value)->comment('状态：1、active，0、frozen');
            $table->string('socket_id')->nullable()->comment('SocketId');
            $table->string('device_id')->nullable()->comment('设备ID');
            $table->unsignedInteger('available_points')->nullable()->default(0)->comment('可用积分');
            $table->unsignedInteger('available_coins')->nullable()->default(0)->comment('可用金币');
            $table->string('password')->nullable()->comment('密码');
            $table->string('pay_password')->nullable()->comment('支付密码');
            $table->rememberToken()->comment('记住我token');
            $table->dateTime('vip_expires_at')->nullable()->comment('VIP过期时间');
            $table->timestamps();
            $table->softDeletes()->comment('删除时间');

            // 唯一索引（软删除安全版）
            $table->unique(['username']);
            $table->unique(['email']);
            $table->unique(['phone']);

            // 普通索引（deleted_at 放第一位）
            $table->index(['deleted_at', 'group_id']);
            $table->index(['deleted_at', 'name']);
            $table->index(['deleted_at', 'socket_id']);
            $table->index(['deleted_at', 'device_id']);

            $table->comment('用户表');
        });
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary()->comment('用户ID');
            $table->unsignedTinyInteger('gender')->default(Gender::GENDER_UNKNOWN->value)->comment('性别：0/1/2');
            $table->date('birthday')->nullable()->comment('生日');
            $table->unsignedInteger('province_id')->nullable()->comment('省ID');
            $table->unsignedInteger('city_id')->nullable()->comment('市ID');
            $table->unsignedInteger('district_id')->nullable()->comment('区县ID');
            $table->string('website')->nullable()->comment('个人网站');
            $table->string('intro')->nullable()->comment('个人简介');
            $table->text('bio')->nullable()->comment('个性签名');

            $table->comment('用户资料表');
        });
        Schema::create('user_extras', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary()->comment('用户ID');
            $table->unsignedBigInteger('referrer_id')->nullable()->comment('推荐人UserID');
            $table->ipAddress('last_login_ip')->nullable()->comment('最后登录IP地址');
            $table->unsignedInteger('invite_registered_count')->default(0)->nullable()->comment('邀请人数');
            $table->string('invite_code')->unique()->comment('邀请码');
            $table->string('reg_source')->nullable()->comment('注册来源');
            $table->unsignedTinyInteger('username_change_count')->default(0)->nullable()->comment('用户名修改次数');
            $table->unsignedBigInteger('collection_count')->nullable()->default(0)->comment('收藏数');
            $table->unsignedBigInteger('login_count')->nullable()->default(0)->comment('登录次数');
            $table->timestamp('first_signed_at')->nullable()->comment('开始签到时间');
            $table->timestamp('first_active_at')->nullable()->comment('首次活动时间');
            $table->timestamp('last_active_at')->nullable()->comment('最后活动时间');
            $table->timestamp('last_login_at')->nullable()->comment('最后登录时间');
            $table->timestamp('phone_verified_at')->nullable()->comment('手机验证时间');
            $table->timestamp('email_verified_at')->nullable()->comment('邮件验证时间');
            $table->json('settings')->nullable()->comment('用户设置');
            $table->json('restore_data')->nullable()->comment('恢复数据');

            $table->comment('用户扩展信息表');
        });
        Schema::create('user_socials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->string('provider')->comment('服务渠道');
            $table->string('openid')->comment('开放平台ID');
            $table->string('unionid')->nullable()->comment('联合ID');
            $table->string('access_token')->nullable()->comment('访问令牌');
            $table->string('refresh_token')->nullable()->comment('刷新令牌');
            $table->timestamp('expiry_at')->nullable()->comment('过期时间');
            $table->mediumText('identity_token')->nullable()->comment('身份令牌');
            $table->timestamps();

            $table->index(['user_id', 'provider', 'openid']);
            $table->comment('用户社交账号表');
        });
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary()->comment('邮箱');
            $table->string('token')->comment('Token');
            $table->timestamp('created_at')->nullable()->comment('创建时间');

            $table->comment('密码重置表');
        });
        Schema::create('nicknames', function (Blueprint $table) {
            $table->id();
            $table->string('nickname')->comment('昵称');
            $table->timestamp('updated_at')->nullable()->comment('更新时间');
            $table->comment('昵称模型');
        });
        UserGroup::create([
            'name' => '注册会员',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nicknames');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('user_groups');
        Schema::dropIfExists('user_socials');
        Schema::dropIfExists('user_extras');
        Schema::dropIfExists('user_profiles');
        Schema::dropIfExists('users');
    }
};
