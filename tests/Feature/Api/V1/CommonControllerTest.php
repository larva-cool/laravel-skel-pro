<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enum\CacheKey;
use App\Enum\StatusSwitch;
use App\Http\Controllers\Api\V1\CommonController;
use App\Models\System\Dict;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(CommonController::class)]
#[TestDox('通用控制器测试')]
class CommonControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试重载 Fpm 方法
     */
    #[Test]
    #[TestDox('测试重载 Fpm 方法')]
    public function test_fpm()
    {
        $response = $this->get('/api/v1/common/fpm');

        $response->assertStatus(200);
        $response->assertJson(['message' => __('system.successful_operation')]);
    }

    /**
     * 测试系统配置方法
     */
    #[Test]
    #[TestDox('测试系统配置方法')]
    public function test_settings()
    {
        $response = $this->get('/api/v1/common/settings');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'system' => [
                'title',
                'keywords',
                'description',
                'icp_beian',
                'police_beian',
                'support_email',
                'lawyer_email',
                'url',
                'm_url',
            ],
            'user' => [
                'enable_register',
                'enable_phone_register',
                'enable_email_register',
                'enable_wechat_login',
                'enable_phone_login',
                'enable_password_login',
                'enable_change_username',
                'username_change',
            ],
        ]);
    }

    /**
     * 测试短信验证码方法
     */
    #[Test]
    #[TestDox('测试短信验证码方法')]
    public function test_sms_captcha()
    {
        $response = $this->post('/api/v1/common/sms-captcha', [
            'phone' => '13800138000',
            'scene' => 'login',
        ]);

        $response->assertStatus(200);
    }

    /**
     * 测试邮件验证码方法
     */
    #[Test]
    #[TestDox('测试邮件验证码方法')]
    public function test_mail_captcha()
    {
        $response = $this->post('/api/v1/common/mail-captcha', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200);
    }

    /**
     * 测试字典接口方法
     */
    #[Test]
    #[TestDox('测试字典接口方法')]
    public function test_dict()
    {
        // 创建父字典
        $parentDict = Dict::create([
            'name' => '测试字典',
            'code' => 'test',
            'description' => '测试字典描述',
            'status' => StatusSwitch::ENABLED->value,
        ]);

        // 创建子字典
        Dict::create([
            'parent_id' => $parentDict->id,
            'name' => '测试选项1',
            'code' => 'option1',
            'description' => '测试选项1描述',
            'status' => StatusSwitch::ENABLED->value,
        ]);

        Dict::create([
            'parent_id' => $parentDict->id,
            'name' => '测试选项2',
            'code' => 'option2',
            'description' => '测试选项2描述',
            'status' => StatusSwitch::ENABLED->value,
        ]);

        // 清除缓存，确保获取最新数据
        Cache::forget(sprintf(CacheKey::DICT_TYPE, 'test'));

        // 发送请求
        $response = $this->get('/api/v1/common/dict?type=test');

        // 验证响应
        $response->assertStatus(200);
        $response->assertJsonIsArray();
        $response->assertJsonStructure([
            '*' => [
                'name',
                'value',
            ],
        ]);
    }

    /**
     * 测试地区接口方法
     */
    #[Test]
    #[TestDox('测试地区接口方法')]
    public function test_area()
    {
        $response = $this->get('/api/v1/common/area');

        $response->assertStatus(200);
        $response->assertJsonIsArray();
    }

    /**
     * 测试获取 Source Types 方法
     */
    #[Test]
    #[TestDox('测试获取 Source Types 方法')]
    public function test_source_types()
    {
        $response = $this->get('/api/v1/common/source-types');

        $response->assertStatus(200);
        $response->assertJsonIsArray();
    }
}
