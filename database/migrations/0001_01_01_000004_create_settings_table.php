<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use App\Enum\SettingType;
use App\Models\System\Setting;
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
            $table->id()->from(10000000);
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

        Setting::batchSet([
            // 基本设置
            ['name' => '网站URL', 'key' => 'system.url', 'value' => 'https://www.xxx.com', 'cast_type' => SettingType::CAST_TYPE_STRING, 'input_type' => SettingType::CAST_TYPE_STRING],
            ['name' => '移动网站URL', 'key' => 'system.m_url', 'value' => 'https://m.xxx.com', 'cast_type' => SettingType::CAST_TYPE_STRING, 'input_type' => SettingType::CAST_TYPE_STRING],
            ['name' => '网站标题', 'key' => 'system.title', 'value' => 'Laravel', 'cast_type' => SettingType::CAST_TYPE_STRING, 'input_type' => SettingType::CAST_TYPE_STRING],
            ['name' => '网站关键词', 'key' => 'system.keywords', 'value' => 'Laravel', 'cast_type' => SettingType::CAST_TYPE_STRING, 'input_type' => SettingType::CAST_TYPE_STRING],
            ['name' => '网站描述', 'key' => 'system.description', 'value' => 'Laravel', 'cast_type' => SettingType::CAST_TYPE_STRING, 'input_type' => SettingType::CAST_TYPE_STRING],
            ['name' => 'ICP备案号', 'key' => 'system.icp_beian', 'value' => 'ICP备XXXX号', 'cast_type' => SettingType::CAST_TYPE_STRING, 'input_type' => SettingType::CAST_TYPE_STRING],
            ['name' => '公安备案号', 'key' => 'system.police_beian', 'value' => '公安备XXXX号', 'cast_type' => SettingType::CAST_TYPE_STRING, 'input_type' => SettingType::CAST_TYPE_STRING],
            ['name' => '服务邮箱', 'key' => 'system.support_email', 'value' => 'support@xxx.com', 'cast_type' => SettingType::CAST_TYPE_STRING, 'input_type' => SettingType::CAST_TYPE_STRING],
            ['name' => '法律邮箱', 'key' => 'system.lawyer_email', 'value' => 'lawyer@xxx.com', 'cast_type' => SettingType::CAST_TYPE_STRING, 'input_type' => SettingType::CAST_TYPE_STRING],

            // SMS 配置
            ['name' => '短信验证码有效期', 'key' => 'sms_captcha.duration', 'value' => '10', 'cast_type' => SettingType::CAST_TYPE_INT, 'input_type' => SettingType::CAST_TYPE_INT],
            ['name' => '短信验证码长度', 'key' => 'sms_captcha.length', 'value' => '6', 'cast_type' => SettingType::CAST_TYPE_INT, 'input_type' => SettingType::CAST_TYPE_INT],
            ['name' => '短信验证码等待时间', 'key' => 'sms_captcha.wait_time', 'value' => '60', 'cast_type' => SettingType::CAST_TYPE_INT, 'input_type' => SettingType::CAST_TYPE_INT],
            ['name' => '短信验证码测试次数', 'key' => 'sms_captcha.test_limit', 'value' => '3', 'cast_type' => SettingType::CAST_TYPE_INT, 'input_type' => SettingType::CAST_TYPE_INT],
            ['name' => '短信验证码IP次数', 'key' => 'sms_captcha.ip_count', 'value' => '20', 'cast_type' => SettingType::CAST_TYPE_INT, 'input_type' => SettingType::CAST_TYPE_INT],
            ['name' => '短信验证码手机号次数', 'key' => 'sms_captcha.phone_count', 'value' => '10', 'cast_type' => SettingType::CAST_TYPE_INT, 'input_type' => SettingType::CAST_TYPE_INT],

            // 邮件配置
            ['name' => '邮件验证码有效期', 'key' => 'email_captcha.duration', 'value' => '10', 'cast_type' => SettingType::CAST_TYPE_INT, 'input_type' => SettingType::CAST_TYPE_INT],
            ['name' => '邮件验证码长度', 'key' => 'email_captcha.length', 'value' => '6', 'cast_type' => SettingType::CAST_TYPE_INT, 'input_type' => SettingType::CAST_TYPE_INT],
            ['name' => '邮件验证码等待时间', 'key' => 'email_captcha.wait_time', 'value' => '60', 'cast_type' => SettingType::CAST_TYPE_INT, 'input_type' => SettingType::CAST_TYPE_INT],
            ['name' => '邮件验证码测试次数', 'key' => 'email_captcha.test_limit', 'value' => '3', 'cast_type' => SettingType::CAST_TYPE_INT, 'input_type' => SettingType::CAST_TYPE_INT],

            // 用户配置
            ['name' => '用户注册', 'key' => 'user.enable_register', 'value' => 1, 'cast_type' => SettingType::CAST_TYPE_BOOL, 'input_type' => SettingType::CAST_TYPE_BOOL],
            ['name' => '手机注册', 'key' => 'user.enable_phone_register', 'value' => 1, 'cast_type' => SettingType::CAST_TYPE_BOOL, 'input_type' => SettingType::CAST_TYPE_BOOL],
            ['name' => '邮箱注册', 'key' => 'user.enable_email_register', 'value' => 1, 'cast_type' => SettingType::CAST_TYPE_BOOL, 'input_type' => SettingType::CAST_TYPE_BOOL],

            ['name' => '用户邀请注册', 'key' => 'user.enable_invite_register', 'value' => 1, 'cast_type' => SettingType::CAST_TYPE_BOOL, 'input_type' => SettingType::CAST_TYPE_BOOL],
            ['name' => '用户登录仅允许一个设备', 'key' => 'user.only_one_device_login', 'value' => 1, 'cast_type' => SettingType::CAST_TYPE_BOOL, 'input_type' => SettingType::CAST_TYPE_BOOL],
            ['name' => '用户名变更次数', 'key' => 'user.username_change', 'value' => 3, 'cast_type' => SettingType::CAST_TYPE_INT, 'input_type' => SettingType::CAST_TYPE_INT],
            ['name' => '用户注册节流', 'key' => 'user.register_throttle', 'value' => '10,3', 'cast_type' => SettingType::CAST_TYPE_STRING, 'input_type' => SettingType::CAST_TYPE_STRING],
            ['name' => '用户登录节流', 'key' => 'user.login_throttle', 'value' => '10,3', 'cast_type' => SettingType::CAST_TYPE_STRING, 'input_type' => SettingType::CAST_TYPE_STRING],
            ['name' => '用户token有效期', 'key' => 'user.token_expiration', 'value' => 525600, 'cast_type' => SettingType::CAST_TYPE_INT, 'input_type' => SettingType::CAST_TYPE_INT],
            ['name' => '允许手机号登录', 'key' => 'user.enable_phone_login', 'value' => 1, 'cast_type' => SettingType::CAST_TYPE_BOOL, 'input_type' => SettingType::CAST_TYPE_BOOL],
            ['name' => '允许密码登录', 'key' => 'user.enable_password_login', 'value' => 1, 'cast_type' => SettingType::CAST_TYPE_BOOL, 'input_type' => SettingType::CAST_TYPE_BOOL],
            ['name' => '允许微信登录', 'key' => 'user.enable_wechat_login', 'value' => 1, 'cast_type' => SettingType::CAST_TYPE_BOOL, 'input_type' => SettingType::CAST_TYPE_BOOL],
            ['name' => '允许Apple登录', 'key' => 'user.enable_apple_login', 'value' => 1, 'cast_type' => SettingType::CAST_TYPE_BOOL, 'input_type' => SettingType::CAST_TYPE_BOOL],
            ['name' => '用户积分有效期', 'key' => 'user.point_expiration', 'value' => 365, 'cast_type' => SettingType::CAST_TYPE_INT, 'input_type' => SettingType::CAST_TYPE_INT],

            // 上传配置
            ['name' => '上传存储', 'key' => 'upload.storage', 'value' => config('filesystems.default'), 'cast_type' => SettingType::CAST_TYPE_STRING, 'input_type' => SettingType::CAST_TYPE_STRING],
            ['name' => '上传文件名规则', 'key' => 'upload.name_rule', 'value' => 'datetime', 'cast_type' => SettingType::CAST_TYPE_STRING, 'input_type' => SettingType::CAST_TYPE_STRING],
            ['name' => '上传允许文件扩展名', 'key' => 'upload.allow_extension', 'value' => 'jpg,png,gif,jpeg,doc,docx,md,txt,pdf,7z,zip,rar,xls,ppt,pptx,wps,mp3,mp4,gz,tar,bz,psd,csv', 'cast_type' => SettingType::CAST_TYPE_STRING, 'input_type' => SettingType::CAST_TYPE_STRING],
            ['name' => '上传允许视频扩展名', 'key' => 'upload.allow_video_extension', 'value' => 'mp4,mov,flv,mkv,rm,rmvb,3gp,m4v,mpg,wmv,avi', 'cast_type' => SettingType::CAST_TYPE_STRING, 'input_type' => SettingType::CAST_TYPE_STRING],

            // OpenAI 配置
            ['name' => 'OpenAI API Key', 'key' => 'openai.api_key', 'value' => 'sk-', 'cast_type' => SettingType::CAST_TYPE_STRING, 'input_type' => SettingType::CAST_TYPE_STRING],
            ['name' => 'OpenAI Organization', 'key' => 'openai.organization', 'value' => null, 'cast_type' => SettingType::CAST_TYPE_STRING, 'input_type' => SettingType::CAST_TYPE_STRING],
            ['name' => 'OpenAI Project', 'key' => 'openai.project', 'value' => null, 'cast_type' => SettingType::CAST_TYPE_STRING, 'input_type' => SettingType::CAST_TYPE_STRING],
            ['name' => 'OpenAI Base URI', 'key' => 'openai.base_uri', 'value' => null, 'cast_type' => SettingType::CAST_TYPE_STRING, 'input_type' => SettingType::CAST_TYPE_STRING],
            ['name' => 'OpenAI Request Timeout', 'key' => 'openai.request_timeout', 'value' => 30, 'cast_type' => SettingType::CAST_TYPE_INT, 'input_type' => SettingType::CAST_TYPE_INT],

            // 运维配置
            ['name' => 'Telescope Switch', 'key' => 'telescope.enabled', 'value' => 1, 'cast_type' => SettingType::CAST_TYPE_BOOL, 'input_type' => SettingType::CAST_TYPE_BOOL],
            ['name' => 'Pulse Switch', 'key' => 'pulse.enabled', 'value' => 1, 'cast_type' => SettingType::CAST_TYPE_BOOL, 'input_type' => SettingType::CAST_TYPE_BOOL],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
