<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enum\PointType;
use App\Http\Controllers\Admin\PointTradeController;
use App\Http\Middleware\RefreshUserActiveAt;
use App\Models\Admin\Admin;
use App\Models\Point\PointTrade;
use App\Models\System\Setting;
use App\Models\User;
use App\Support\UserHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * 后台积分交易控制器测试
 */
#[CoversClass(PointTradeController::class)]
#[TestDox('后台积分交易控制器测试')]
class PointTradeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::query()->delete();

        Permission::findOrCreate('point_trades.index', 'admin');

        $this->admin = $this->makeAdmin();
        $this->admin->givePermissionTo(['point_trades.index']);
    }

    /**
     * 创建管理员（绕过 booted 事件）。
     */
    protected function makeAdmin(array $attributes = []): Admin
    {
        static $seq = 0;
        $seq++;
        $suffix = substr(md5((string) microtime(true).$seq.random_int(0, 9999)), 0, 8);

        $email = $attributes['email'] ?? "pt_adm{$suffix}@example.com";
        $phone = $attributes['phone'] ?? '13'.str_pad((string) ($seq.random_int(1000000, 9999999)), 9, '0', STR_PAD_LEFT);
        $user = UserHelper::createByEmail($email, 'password123');

        return Admin::withoutEvents(function () use ($user, $email, $suffix, $phone, $attributes) {
            $admin = new Admin;
            $fill = array_merge([
                'user_id' => $user->id,
                'username' => "pt_adm{$suffix}",
                'name' => '测试管理员'.$suffix,
                'email' => $email,
                'phone' => $phone,
                'password' => 'password123',
                'status' => 1,
            ], $attributes);
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
     * 创建一条积分交易记录。
     */
    protected function makePointTrade(?User $user = null, array $attributes = []): PointTrade
    {
        $user ??= UserHelper::createByEmail('pt_user_'.random_int(1000, 9999).'@example.com');

        return PointTrade::create(array_merge([
            'user_id' => $user->id,
            'points' => 50,
            'description' => '测试积分交易',
            'type' => PointType::TYPE_SIGN_IN,
            'source_id' => 0,
            'source_type' => User::class,
            'expired_at' => null,
        ], $attributes));
    }

    #[Test]
    #[TestDox('未认证用户访问积分交易列表被重定向到登录页')]
    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);
        $response = $this->get('/admin/points');
        $response->assertRedirect('/admin/auth/login');
    }

    #[Test]
    #[TestDox('无权限用户访问积分交易列表返回403')]
    public function test_forbidden_without_permission(): void
    {
        $another = $this->makeAdmin();
        $this->actingAsAdmin($another);

        $response = $this->getJson('/admin/points');
        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('获取积分交易列表JSON')]
    public function test_index_returns_json_list(): void
    {
        $this->actingAsAdmin();
        $this->makePointTrade();
        $this->makePointTrade();

        $response = $this->getJson('/admin/points');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'points', 'description', 'type'],
            ],
            'links',
            'meta',
        ]);
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    #[Test]
    #[TestDox('积分交易列表页面返回视图')]
    public function test_index_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/points');

        $response->assertOk();
        $response->assertViewIs('admin.point_trade.index');
    }

    #[Test]
    #[TestDox('积分交易列表按ID倒序排列')]
    public function test_index_orders_by_id_desc(): void
    {
        $this->actingAsAdmin();
        $first = $this->makePointTrade();
        $second = $this->makePointTrade();

        $response = $this->getJson('/admin/points');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals($second->id, $data[0]['id']);
    }
}
