<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\Coin;

use App\Enum\CoinType;
use App\Models\Coin\CoinTrade;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 测试金币交易流水模型
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
#[CoversClass(CoinTrade::class)]
class CoinTradeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试用户
     */
    protected User $user;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 创建测试用户
        $this->user = User::create([
            'username' => 'testuser',
            'password' => bcrypt('password123'),
            'name' => 'Test User',
            'available_coins' => 0,
        ]);
    }

    /**
     * 测试可填充属性
     */
    #[Test]
    #[TestDox('测试可填充属性')]
    public function test_fillable_attributes()
    {
        $fillable = (new CoinTrade)->getFillable();

        $this->assertEquals([
            'user_id', 'coins', 'description', 'type', 'source_id', 'source_type',
        ], $fillable);
    }

    /**
     * 测试隐藏属性
     */
    #[Test]
    #[TestDox('测试隐藏属性')]
    public function test_hidden_attributes()
    {
        $hidden = (new CoinTrade)->getHidden();

        $this->assertEquals(['user_id'], $hidden);
    }

    /**
     * 测试属性类型转换
     */
    #[Test]
    #[TestDox('测试属性类型转换')]
    public function test_casts()
    {
        $casts = (new CoinTrade)->getCasts();

        $this->assertEquals('integer', $casts['id']);
        $this->assertEquals('integer', $casts['user_id']);
        $this->assertEquals('integer', $casts['coins']);
        $this->assertEquals('string', $casts['description']);
        $this->assertEquals(CoinType::class, $casts['type']);
        $this->assertEquals('integer', $casts['source_id']);
        $this->assertEquals('string', $casts['source_type']);
        $this->assertEquals('datetime', $casts['created_at']);
    }

    /**
     * 测试追加属性
     */
    #[Test]
    #[TestDox('测试追加属性')]
    public function test_appends_attributes()
    {
        $appends = (new CoinTrade)->getAppends();

        $this->assertEquals(['type_label'], $appends);
    }

    /**
     * 测试类型标签访问器
     */
    #[Test]
    #[TestDox('测试类型标签访问器')]
    public function test_type_label_accessor()
    {
        // 测试签到类型
        $trade1 = new CoinTrade;
        $trade1->type = CoinType::TYPE_SIGN_IN;
        $this->assertEquals('签到', $trade1->type_label);

        // 测试邀请注册类型
        $trade2 = new CoinTrade;
        $trade2->type = CoinType::TYPE_INVITE_REGISTER;
        $this->assertEquals('邀请注册', $trade2->type_label);

        // 测试未知类型
        $trade3 = new CoinTrade;
        $trade3->type = CoinType::TYPE_UNKNOWN;
        $this->assertEquals('未知', $trade3->type_label);
    }

    /**
     * 测试来源关联关系
     */
    #[Test]
    #[TestDox('测试来源关联关系')]
    public function test_source_relation()
    {
        $trade = new CoinTrade;
        $relation = $trade->source();

        $this->assertInstanceOf(MorphTo::class, $relation);
    }

    /**
     * 测试获取当前用户今日的金币数量
     */
    #[Test]
    #[TestDox('测试获取当前用户今日的金币数量')]
    public function test_get_today_coins()
    {
        // 创建今日的金币交易记录
        CoinTrade::create([
            'user_id' => $this->user->id,
            'coins' => 100,
            'description' => '今日签到',
            'type' => CoinType::TYPE_SIGN_IN,
            'source_id' => $this->user->id,
            'source_type' => User::class,
        ]);

        CoinTrade::create([
            'user_id' => $this->user->id,
            'coins' => 50,
            'description' => '今日邀请',
            'type' => CoinType::TYPE_INVITE_REGISTER,
            'source_id' => $this->user->id,
            'source_type' => User::class,
        ]);

        // 创建昨日的金币交易记录（使用 DB 直接插入）
        DB::table('coin_trades')->insert([
            'user_id' => $this->user->id,
            'coins' => 200,
            'description' => '昨日签到',
            'type' => CoinType::TYPE_SIGN_IN->value,
            'source_id' => $this->user->id,
            'source_type' => User::class,
            'created_at' => Carbon::yesterday(),
        ]);

        // 创建负金币交易记录（消耗金币）
        CoinTrade::create([
            'user_id' => $this->user->id,
            'coins' => -50,
            'description' => '消耗金币',
            'type' => CoinType::TYPE_UNKNOWN,
            'source_id' => $this->user->id,
            'source_type' => User::class,
        ]);

        // 获取今日金币数量
        $todayCoins = CoinTrade::getTodayCoins($this->user->id);

        // 验证结果（只计算今日的正金币）
        $this->assertEquals(150, $todayCoins);
    }

    /**
     * 测试获取当前用户今日的金币数量（无记录）
     */
    #[Test]
    #[TestDox('测试获取当前用户今日的金币数量（无记录）')]
    public function test_get_today_coins_no_records()
    {
        // 获取今日金币数量（无记录）
        $todayCoins = CoinTrade::getTodayCoins($this->user->id);

        // 验证结果
        $this->assertEquals(0, $todayCoins);
    }

    /**
     * 测试修复当前用户的金币数量
     */
    #[Test]
    #[TestDox('测试修复当前用户的金币数量')]
    public function test_fix_current_coins()
    {
        // 创建金币交易记录
        CoinTrade::create([
            'user_id' => $this->user->id,
            'coins' => 100,
            'description' => '签到',
            'type' => CoinType::TYPE_SIGN_IN,
            'source_id' => $this->user->id,
            'source_type' => User::class,
        ]);

        CoinTrade::create([
            'user_id' => $this->user->id,
            'coins' => 50,
            'description' => '邀请',
            'type' => CoinType::TYPE_INVITE_REGISTER,
            'source_id' => $this->user->id,
            'source_type' => User::class,
        ]);

        CoinTrade::create([
            'user_id' => $this->user->id,
            'coins' => -30,
            'description' => '消耗',
            'type' => CoinType::TYPE_UNKNOWN,
            'source_id' => $this->user->id,
            'source_type' => User::class,
        ]);

        // 修复金币数量
        $result = CoinTrade::fixCurrentCoins($this->user->id);

        // 验证操作成功
        $this->assertTrue($result);

        // 验证用户金币数量更新成功
        $updatedUser = User::find($this->user->id);
        $this->assertEquals(120, $updatedUser->available_coins);
    }

    /**
     * 测试获取当前用户的金币数量
     */
    #[Test]
    #[TestDox('测试获取当前用户的金币数量')]
    public function test_get_current_coins()
    {
        // 更新用户金币数量
        $this->user->update(['available_coins' => 200]);

        // 获取当前金币数量
        $currentCoins = CoinTrade::getCurrentCoins($this->user->id);

        // 验证结果
        $this->assertEquals(200, $currentCoins);
    }
}
