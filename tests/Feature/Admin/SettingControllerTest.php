<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\SettingController;
use App\Http\Middleware\RefreshUserActiveAt;
use App\Models\Admin\Admin;
use App\Models\System\Setting;
use App\Support\UserHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * 后台系统设置控制器测试
 */
#[CoversClass(SettingController::class)]
#[TestDox('后台系统设置控制器测试')]
class SettingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // 清空 settings 表，避免 Setting 模型自动插入的配置影响测试
        Setting::query()->delete();

        // 插入基础配置，避免视图报错
        Setting::batchSet([
            // 基本信息
            ['key' => 'system.url', 'value' => 'https://example.com'],
            ['key' => 'system.m_url', 'value' => 'https://example.com/m'],
            ['key' => 'system.title', 'value' => '系统标题'],
            ['key' => 'system.keywords', 'value' => ''],
            ['key' => 'system.description', 'value' => ''],
            ['key' => 'system.icp_beian', 'value' => ''],
            ['key' => 'system.police_beian', 'value' => ''],
            ['key' => 'system.support_email', 'value' => ''],
            ['key' => 'system.lawyer_email', 'value' => ''],
            // 用户设置（boolean 值存储为 '0'/'1' 字符串）
            ['key' => 'user.enable_register', 'value' => '1'],
            ['key' => 'user.enable_phone_register', 'value' => '0'],
            ['key' => 'user.enable_email_register', 'value' => '0'],
            ['key' => 'user.register_throttle', 'value' => ''],
            ['key' => 'user.enable_phone_login', 'value' => '1'],
            ['key' => 'user.enable_password_login', 'value' => '1'],
            ['key' => 'user.enable_wechat_login', 'value' => '0'],
            ['key' => 'user.enable_apple_login', 'value' => '0'],
            ['key' => 'user.only_one_device_login', 'value' => '0'],
            ['key' => 'user.login_throttle', 'value' => ''],
            ['key' => 'user.username_change', 'value' => '0'],
            ['key' => 'user.token_expiration', 'value' => '60'],
            ['key' => 'user.point_expiration', 'value' => '365'],
            // 短信设置
            ['key' => 'sms.region_id', 'value' => ''],
            ['key' => 'sms.sms_account', 'value' => ''],
            ['key' => 'sms.sign_name', 'value' => ''],
            ['key' => 'sms.template_id', 'value' => ''],
            // 短信验证码
            ['key' => 'sms_captcha.duration', 'value' => '300'],
            ['key' => 'sms_captcha.length', 'value' => '6'],
            ['key' => 'sms_captcha.wait_time', 'value' => '60'],
            ['key' => 'sms_captcha.test_limit', 'value' => '10'],
            ['key' => 'sms_captcha.ip_count', 'value' => '5'],
            ['key' => 'sms_captcha.phone_count', 'value' => '3'],
            // 邮件验证码
            ['key' => 'email_captcha.duration', 'value' => '300'],
            ['key' => 'email_captcha.length', 'value' => '6'],
            ['key' => 'email_captcha.wait_time', 'value' => '60'],
            ['key' => 'email_captcha.test_limit', 'value' => '10'],
            // 上传设置
            ['key' => 'upload.storage', 'value' => 'local'],
            ['key' => 'upload.name_rule', 'value' => 'md5'],
            ['key' => 'upload.allow_extension', 'value' => 'jpg,png'],
            ['key' => 'upload.allow_video_extension', 'value' => 'mp4'],
            // OpenAI 配置
            ['key' => 'openai.base_uri', 'value' => ''],
            ['key' => 'openai.organization', 'value' => ''],
            ['key' => 'openai.project', 'value' => ''],
            ['key' => 'openai.api_key', 'value' => ''],
            ['key' => 'openai.default_model', 'value' => ''],
            ['key' => 'openai.request_timeout', 'value' => '60'],
        ]);

        Permission::findOrCreate('settings.index', 'admin');
        Permission::findOrCreate('settings.create', 'admin');
        Permission::findOrCreate('settings.edit', 'admin');
        Permission::findOrCreate('settings.delete', 'admin');

        $this->admin = $this->makeAdmin();
        $this->admin->givePermissionTo([
            'settings.index', 'settings.create', 'settings.edit', 'settings.delete',
        ]);
    }

    /**
     * 创建管理员（绕过 booted 事件）。
     */
    protected function makeAdmin(array $attributes = []): Admin
    {
        static $seq = 0;
        $seq++;
        $suffix = substr(md5((string) microtime(true).$seq.random_int(0, 9999)), 0, 8);

        $email = $attributes['email'] ?? "set_adm{$suffix}@example.com";
        $phone = $attributes['phone'] ?? '13'.str_pad((string) ($seq.random_int(1000000, 9999999)), 9, '0', STR_PAD_LEFT);
        $user = UserHelper::createByEmail($email, 'password123');

        return Admin::withoutEvents(function () use ($user, $email, $suffix, $attributes, $phone) {
            $admin = new Admin;
            $fill = array_merge([
                'user_id' => $user->id,
                'username' => "set_adm{$suffix}",
                'name' => '测试管理员'.$suffix,
                'email' => $email,
                'phone' => $phone,
                'status' => 1,
            ], $attributes);
            if (! isset($fill['password'])) {
                $fill['password'] = 'password123';
            }
            $admin->forceFill($fill);
            $admin->save();

            return $admin;
        });
    }

    /**
     * 以管理员身份登录并禁用 RefreshUserActiveAt 中间件。
     */
    protected function actingAsAdmin(?Admin $admin = null): self
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);

        return $this->actingAs($admin ?? $this->admin, 'admin');
    }

    /**
     * 创建一条配置记录。
     */
    protected function makeSetting(array $attributes = []): Setting
    {
        return Setting::create(array_merge([
            'name' => '测试配置'.Str::random(4),
            'key' => 'test.key.'.Str::random(6),
            'value' => 'test.value',
            'cast_type' => 'string',
            'input_type' => 'text',
            'order' => 0,
        ], $attributes));
    }

    #[Test]
    #[TestDox('未认证用户访问设置列表被重定向')]
    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);
        $response = $this->get('/admin/settings');
        $response->assertRedirect('/admin/auth/login');
    }

    #[Test]
    #[TestDox('无权限用户返回403')]
    public function test_forbidden_without_permission(): void
    {
        $another = $this->makeAdmin();
        $this->actingAsAdmin($another);

        $response = $this->getJson('/admin/settings');
        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('获取设置列表JSON')]
    public function test_index_returns_json_list(): void
    {
        $this->actingAsAdmin();
        $this->makeSetting(['name' => '配置A', 'key' => 'key.a']);
        $this->makeSetting(['name' => '配置B', 'key' => 'key.b']);

        $response = $this->getJson('/admin/settings');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'key', 'value', 'cast_type'],
            ],
            'links',
            'meta',
        ]);
        // setUp 中 13 条基础配置 + 测试中 2 条 = 15 条
        $this->assertCount(15, $response->json('data'));
    }

    #[Test]
    #[TestDox('按关键词搜索设置')]
    public function test_index_search_by_keyword(): void
    {
        $this->actingAsAdmin();
        $target = $this->makeSetting(['name' => 'SpecialName', 'key' => 'special.key']);
        $this->makeSetting(['name' => 'OtherName']);

        $response = $this->getJson('/admin/settings?keyword=SpecialName');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($target->id, $data[0]['id']);
    }

    #[Test]
    #[TestDox('创建页面返回视图')]
    public function test_create_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/settings/create');
        $response->assertOk();
        $response->assertViewIs('admin.setting.create');
    }

    #[Test]
    #[TestDox('编辑页面返回视图')]
    public function test_edit_returns_view(): void
    {
        $this->actingAsAdmin();
        $setting = $this->makeSetting();

        $response = $this->get('/admin/settings/'.$setting->id.'/edit');

        $response->assertOk();
        $response->assertViewIs('admin.setting.edit');
        $response->assertViewHas('item', fn ($item) => $item->id === $setting->id);
    }

    #[Test]
    #[TestDox('创建配置成功')]
    public function test_store_creates_setting(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/settings', [
            'name' => '新配置',
            'key' => 'new.key',
            'value' => 'new.value',
            'cast_type' => 'string',
            'input_type' => 'text',
            'order' => 1,
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.create_success')]);
        $this->assertDatabaseHas('settings', [
            'name' => '新配置',
            'key' => 'new.key',
        ]);
    }

    #[Test]
    #[TestDox('创建配置时 name/key/value 必填')]
    public function test_store_requires_fields(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/settings', [
            'value' => 'only.value',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'key']);
    }

    #[Test]
    #[TestDox('更新配置成功')]
    public function test_update_setting(): void
    {
        $this->actingAsAdmin();
        $setting = $this->makeSetting(['name' => '原名', 'key' => 'old.key']);

        $response = $this->putJson('/admin/settings/'.$setting->id, [
            'name' => '新名称',
            'key' => 'old.key',
            'value' => 'new.value',
            'cast_type' => 'string',
            'input_type' => 'text',
            'order' => 2,
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.update_success')]);

        $setting->refresh();
        $this->assertEquals('新名称', $setting->name);
        $this->assertEquals('new.value', $setting->value);
    }

    #[Test]
    #[TestDox('删除配置成功')]
    public function test_destroy_deletes_setting(): void
    {
        $this->actingAsAdmin();
        $setting = $this->makeSetting();

        $response = $this->deleteJson('/admin/settings/'.$setting->id);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => '删除成功']);
        $this->assertDatabaseMissing('settings', ['id' => $setting->id]);
    }

    #[Test]
    #[TestDox('配置管理页面返回视图')]
    public function test_config_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/settings/config');
        $response->assertOk();
        $response->assertViewIs('admin.setting.config');
        $response->assertViewHas('settings');
        $response->assertViewHas('disks');
    }

    #[Test]
    #[TestDox('批量保存配置成功')]
    public function test_store_config_batch_sets_settings(): void
    {
        $this->actingAsAdmin();

        // 传递所有必填字段
        $response = $this->postJson('/admin/settings/config', [
            'system' => [
                'url' => 'https://example.com',
                'm_url' => 'https://example.com/m',
                'title' => '系统标题',
            ],
            'user' => [
                'enable_register' => '1',
                'enable_phone_register' => '0',
                'enable_email_register' => '0',
                'enable_wechat_login' => '0',
                'enable_apple_login' => '0',
                'enable_phone_login' => '1',
                'enable_password_login' => '1',
                'only_one_device_login' => '0',
                'username_change' => '0',
                'token_expiration' => '60',
                'point_expiration' => '365',
            ],
            'sms_captcha' => [
                'duration' => '300',
                'length' => '6',
                'wait_time' => '60',
                'test_limit' => '10',
                'ip_count' => '5',
                'phone_count' => '3',
            ],
            'email_captcha' => [
                'duration' => '300',
                'length' => '6',
                'wait_time' => '60',
                'test_limit' => '10',
            ],
            'upload' => [
                'storage' => 'local',
                'name_rule' => 'md5',
                'allow_extension' => 'jpg,png',
                'allow_video_extension' => 'mp4',
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => '设置完成']);

        $this->assertDatabaseHas('settings', ['key' => 'system.url']);
        $this->assertDatabaseHas('settings', ['key' => 'system.m_url']);
        $this->assertDatabaseHas('settings', ['key' => 'system.title']);
    }
}
