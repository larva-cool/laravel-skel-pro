<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Resources\Api\V1;

use App\Enum\CoinType;
use App\Http\Resources\Api\V1\CoinResource;
use App\Models\Coin\CoinTrade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(CoinResource::class)]
#[TestDox('CoinResource 测试')]
class CoinResourceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    #[TestDox('测试资源数组结构是否正确')]
    public function test_resource_structure()
    {
        // 创建测试数据
        $coinTrade = $this->createCoinTrade();

        // 创建资源实例
        $resource = new CoinResource($coinTrade);

        // 创建模拟请求对象
        $request = new Request;
        // 获取资源数组
        $result = $resource->toArray($request);

        // 验证数组结构
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('type_label', $result);
        $this->assertArrayHasKey('coins', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('created_at', $result);

        // 验证字段数量
        $this->assertCount(6, $result);
    }

    #[Test]
    #[TestDox('测试日期格式是否正确')]
    public function test_date_format()
    {
        // 创建带特定日期的测试数据
        $createdAt = Carbon::now();
        $coinTrade = $this->createCoinTrade([
            'created_at' => $createdAt,
        ]);

        // 创建资源实例
        $resource = new CoinResource($coinTrade);

        // 创建模拟请求对象
        $request = new Request;
        // 获取资源数组
        $result = $resource->toArray($request);

        // 验证日期格式
        $this->assertEquals($createdAt->toDateTimeString(), $result['created_at']);
    }

    #[Test]
    #[TestDox('测试type_text映射是否正确')]
    public function test_type_text_mapping()
    {
        // 测试签到类型
        $signInTrade = $this->createCoinTrade([
            'type' => CoinType::TYPE_SIGN_IN,
        ]);
        $signInResource = new CoinResource($signInTrade);
        $request = new Request;
        $signInResult = $signInResource->toArray($request);
        $this->assertEquals('签到', $signInResult['type_label']);

        // 测试邀请注册类型
        $inviteTrade = $this->createCoinTrade([
            'type' => CoinType::TYPE_INVITE_REGISTER,
        ]);
        $inviteResource = new CoinResource($inviteTrade);
        $request = new Request;
        $inviteResult = $inviteResource->toArray($request);
        $this->assertEquals('邀请注册', $inviteResult['type_label']);

        // 测试未知类型
        $unknownTrade = $this->createCoinTrade([
            'type' => CoinType::TYPE_UNKNOWN,
        ]);
        $unknownResource = new CoinResource($unknownTrade);
        $request = new Request;
        $unknownResult = $unknownResource->toArray($request);
        $this->assertEquals('未知', $unknownResult['type_label']);
    }

    #[Test]
    #[TestDox('测试字段值映射是否正确')]
    public function test_field_values()
    {
        // 创建带特定字段值的测试数据
        $testData = [
            'id' => 123,
            'type' => CoinType::TYPE_SIGN_IN,
            'coins' => 100,
            'description' => '每日签到奖励',
        ];

        $coinTrade = $this->createCoinTrade($testData);

        // 创建资源实例
        $resource = new CoinResource($coinTrade);

        // 创建模拟请求对象
        $request = new Request;
        // 获取资源数组
        $result = $resource->toArray($request);

        // 验证字段值映射
        $this->assertEquals($testData['id'], $result['id']);
        $this->assertEquals($testData['type'], $result['type']);
        $this->assertEquals('签到', $result['type_label']); // 验证type_label
        $this->assertEquals($testData['coins'], $result['coins']);
        $this->assertEquals($testData['description'], $result['description']);
    }

    /**
     * 创建金币交易测试数据
     */
    private function createCoinTrade(array $data = []): CoinTrade
    {
        // 创建基础数据，不包含id和created_at
        $baseData = [
            'user_id' => 1,
            'type' => CoinType::TYPE_SIGN_IN,
            'coins' => 10,
            'description' => '测试描述',
            'source_id' => 1,
            'source_type' => 'App\\Models\\User',
        ];

        // 合并数据
        $mergedData = array_merge($baseData, $data);

        // 提取id（如果存在）
        $id = $mergedData['id'] ?? 1;
        unset($mergedData['id']);

        // 提取created_at（如果存在）
        $createdAt = $mergedData['created_at'] ?? Carbon::now();
        unset($mergedData['created_at']);

        // 创建CoinTrade实例
        $coinTrade = new CoinTrade($mergedData);

        // 直接设置id属性
        $coinTrade->setAttribute('id', $id);

        // 直接设置created_at属性
        $coinTrade->setAttribute('created_at', $createdAt);

        // 绕过创建时的事件和钩子
        $coinTrade->exists = true;

        return $coinTrade;
    }
}
