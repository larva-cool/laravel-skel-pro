<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace Feature\Admin;

use App\Http\Controllers\Admin\AuthController;
use App\Models\Admin\Admin;
use Database\Seeders\AdminMenuSeeder;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 后台认证控制器功能测试
 */
#[CoversClass(AuthController::class)]
#[Group('admin')]
#[Group('auth')]
class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 设置测试环境：运行 seeders 创建管理员和权限
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([AdminMenuSeeder::class, AdminSeeder::class]);
    }

    /**
     * 测试登录成功返回 token
     */
    #[Test]
    #[TestDox('管理员使用正确凭证登录返回 200 和 token')]
    public function login_with_valid_credentials(): void
    {
        $response = $this->postJson('/admin/auth/login', [
            'account' => 'admin',
            'password' => '123456',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'access_token',
                    'user',
                ],
            ])
            ->assertJson(['code' => 200]);
    }

    /**
     * 测试登录失败返回验证错误
     */
    #[Test]
    #[TestDox('管理员使用错误密码登录返回 422 验证错误')]
    public function login_with_wrong_password(): void
    {
        $response = $this->postJson('/admin/auth/login', [
            'account' => 'admin',
            'password' => 'wrong_password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.password.0', trans('auth.failed'));
    }

    /**
     * 测试登录参数验证失败
     */
    #[Test]
    #[TestDox('登录缺少必填字段返回 422 验证错误')]
    public function login_with_missing_fields(): void
    {
        $response = $this->postJson('/admin/auth/login', [
            'account' => 'admin',
        ]);

        $response->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors' => ['password'],
            ]);
    }

    /**
     * 测试登录成功后获取用户信息
     */
    #[Test]
    #[TestDox('使用有效 token 获取用户信息返回 200 和完整信息')]
    public function info_with_valid_token(): void
    {
        $loginResponse = $this->postJson('/admin/auth/login', [
            'account' => 'admin',
            'password' => '123456',
        ]);

        $token = $loginResponse->json('data.access_token');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/admin/auth/info');

        $response->assertOk()
            ->assertJson([
                'code' => 200,
            ])
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'user_id',
                    'user_name',
                    'email',
                    'avatar',
                    'roles',
                    'buttons',
                ],
            ]);
    }

    /**
     * 测试未登录时获取用户信息返回 401
     */
    #[Test]
    #[TestDox('未登录时获取用户信息返回 401')]
    public function info_without_token(): void
    {
        $response = $this->getJson('/admin/auth/info');

        $response->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    /**
     * 测试退出登录
     */
    #[Test]
    #[TestDox('退出登录成功返回 200')]
    public function logout_successfully(): void
    {
        $loginResponse = $this->postJson('/admin/auth/login', [
            'account' => 'admin',
            'password' => '123456',
        ]);

        $token = $loginResponse->json('data.access_token');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/admin/auth/logout');

        $response->assertOk()
            ->assertJson([
                'code' => 200,
            ]);
    }

    /**
     * 测试退出后 token 从数据库删除
     */
    #[Test]
    #[TestDox('退出登录后 token 从数据库删除')]
    public function token_deleted_after_logout(): void
    {
        $loginResponse = $this->postJson('/admin/auth/login', [
            'account' => 'admin',
            'password' => '123456',
        ]);

        $token = $loginResponse->json('data.access_token');

        // 退出前 token 存在
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/admin/auth/logout')
            ->assertOk();

        // 退出后 token 已从数据库删除
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    /**
     * 测试被禁用账号无法登录
     */
    #[Test]
    #[TestDox('被禁用的管理员账号返回验证错误')]
    public function login_with_disabled_admin(): void
    {
        Admin::query()->create([
            'username' => 'disabled_user',
            'name' => 'Disabled User',
            'password' => bcrypt('123456'),
            'status' => 0,
        ]);

        $response = $this->postJson('/admin/auth/login', [
            'account' => 'disabled_user',
            'password' => '123456',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.account.0', trans('user.blocked'));
    }
}
