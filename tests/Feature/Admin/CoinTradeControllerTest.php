<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enum\CoinType;
use App\Http\Controllers\Admin\CoinTradeController;
use App\Http\Middleware\RefreshUserActiveAt;
use App\Models\Admin\Admin;
use App\Models\Coin\CoinTrade;
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
 * 后台金币交易控制器测试
 */
#[CoversClass(CoinTradeController::class)]
#[TestDox('后台金币交易控制器测试')]
class CoinTradeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::query()->delete();

        Permission::findOrCreate('coin_trades.index', 'admin');

        $this->admin = $this->makeAdmin();
        $this->admin->givePermissionTo(['coin_trades.index']);
    }

    /**
     * 创建管理员（绕过 booted 事件）。
     */
    protected function makeAdmin(array $attributes = []): Admin
    {
        static $seq = 0;
        $seq++;
        $suffix = substr(md5((string) microtime(true).$seq.random_int(0, 9999)), 0, 8);

        $email = $attributes['email'] ?? "coin_adm{$suffix}@example.com";
        $phone = $attributes['phone'] ?? '13'.str_pad((string) ($seq.random_int(1000000, 9999999)), 9, '0', STR_PAD_LEFT);
        $user = UserHelper::createByEmail($email, 'password123');

        return Admin::withoutEvents(function () use ($user, $email, $suffix, $phone, $attributes) {
            $admin = new Admin;
            $fill = array_merge([
                'user_id' => $user->id,
                'username' => "coin_adm{$suffix}",
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
     * 创建一条金币交易记录。
     */
    protected function makeCoinTrade(?User $user = null, array $attributes = []): CoinTrade
    {
        $user ??= UserHelper::createByEmail('coin_user_'.random_int(1000, 9999).'@example.com');

        return CoinTrade::create(array_merge([
            'user_id' => $user->id,
            'coins' => 100,
            'description' => '测试金币交易',
            'type' => CoinType::TYPE_SIGN_IN,
            'source_id' => 0,
            'source_type' => User::class,
        ], $attributes));
    }

    #[Test]
    #[TestDox('未认证用户访问金币交易列表被重定向到登录页')]
    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);
        $response = $this->get('/admin/coins');
        $response->assertRedirect('/admin/auth/login');
    }

    #[Test]
    #[TestDox('无权限用户访问金币交易列表返回403')]
    public function test_forbidden_without_permission(): void
    {
        $another = $this->makeAdmin();
        $this->actingAsAdmin($another);

        $response = $this->getJson('/admin/coins');
        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('获取金币交易列表JSON')]
    public function test_index_returns_json_list(): void
    {
        $this->actingAsAdmin();
        $this->makeCoinTrade();
        $this->makeCoinTrade();

        $response = $this->getJson('/admin/coins');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'coins', 'description', 'type'],
            ],
            'links',
            'meta',
        ]);
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    #[Test]
    #[TestDox('金币交易列表页面返回视图')]
    public function test_index_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/coins');

        $response->assertOk();
        $response->assertViewIs('admin.coin_trade.index');
    }

    #[Test]
    #[TestDox('金币交易列表按ID倒序排列')]
    public function test_index_orders_by_id_desc(): void
    {
        $this->actingAsAdmin();
        $first = $this->makeCoinTrade();
        sleep(0); // 确保时间顺序
        $second = $this->makeCoinTrade();

        $response = $this->getJson('/admin/coins');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals($second->id, $data[0]['id']);
    }
}
