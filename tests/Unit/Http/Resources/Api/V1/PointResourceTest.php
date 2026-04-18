<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Resources\Api\V1;

use App\Enum\PointType;
use App\Http\Resources\Api\V1\PointResource;
use App\Models\Point\PointTrade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(PointResource::class)]
#[TestDox('PointResource 测试')]
class PointResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        // 创建用户用于测试
        $this->user = User::create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'phone' => '13800138000',
            'password' => bcrypt('password'),
        ]);
    }

    #[Test]
    #[TestDox('测试 toArray 返回正确的数据结构')]
    public function test_to_array_returns_correct_structure()
    {
        // 直接创建测试数据
        $pointTrade = new PointTrade;
        $pointTrade->user_id = $this->user->id;
        $pointTrade->points = 100;
        $pointTrade->type = PointType::TYPE_SIGN_IN;
        $pointTrade->description = '测试积分';
        $pointTrade->source_id = $this->user->id;
        $pointTrade->source_type = 'user';
        $pointTrade->expired_at = now()->addDays(30);
        $pointTrade->save();

        // 创建资源实例
        $resource = new PointResource($pointTrade);
        $request = $this->createRequest();
        $data = $resource->toArray($request);

        // 验证返回的数据结构
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('type_label', $data);
        $this->assertArrayHasKey('points', $data);
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('expired_at', $data);

        // 验证数据内容
        $this->assertEquals($pointTrade->id, $data['id']);
        $this->assertEquals($pointTrade->type, $data['type']);
        $this->assertEquals($pointTrade->type_label, $data['type_label']);
        $this->assertEquals($pointTrade->points, $data['points']);
        $this->assertEquals($pointTrade->description, $data['description']);
    }

    #[Test]
    #[TestDox('测试日期格式正确格式化')]
    public function test_to_array_formats_dates_correctly()
    {
        // 直接创建测试数据
        $pointTrade = new PointTrade;
        $pointTrade->user_id = $this->user->id;
        $pointTrade->points = 50;
        $pointTrade->type = PointType::TYPE_SIGN_IN;
        $pointTrade->description = '签到积分';
        $pointTrade->source_id = $this->user->id;
        $pointTrade->source_type = 'user';
        $pointTrade->expired_at = now()->addDays(30);
        $pointTrade->save();

        // 创建资源实例
        $resource = new PointResource($pointTrade);
        $request = $this->createRequest();
        $data = $resource->toArray($request);

        // 验证日期格式
        $this->assertEquals($pointTrade->created_at->toDateTimeString(), $data['created_at']);
        $this->assertEquals($pointTrade->expired_at->toDateTimeString(), $data['expired_at']);
    }

    #[Test]
    #[TestDox('测试正确处理空的过期时间')]
    public function test_to_array_handles_null_expired_at()
    {
        // 直接创建没有过期时间的测试数据
        $pointTrade = new PointTrade;
        $pointTrade->user_id = $this->user->id;
        $pointTrade->points = 20;
        $pointTrade->type = PointType::TYPE_SIGN_IN;
        $pointTrade->description = '无过期积分';
        $pointTrade->source_id = $this->user->id;
        $pointTrade->source_type = 'user';
        $pointTrade->expired_at = null;
        $pointTrade->save();

        // 创建资源实例
        $resource = new PointResource($pointTrade);
        $request = $this->createRequest();
        $data = $resource->toArray($request);

        // 验证过期时间为null
        $this->assertNull($data['expired_at']);
    }

    #[Test]
    #[TestDox('测试返回正确的类型文本')]
    public function test_to_array_returns_correct_type_text()
    {
        // 测试不同类型的交易
        $types = PointType::values();

        foreach ($types as $type => $expectedText) {
            $pointTrade = new PointTrade;
            $pointTrade->user_id = $this->user->id;
            $pointTrade->points = 10;
            $pointTrade->type = $expectedText;
            $pointTrade->description = '测试类型';
            $pointTrade->source_id = $this->user->id;
            $pointTrade->source_type = 'user';
            $pointTrade->save();

            $resource = new PointResource($pointTrade);
            $request = $this->createRequest();
            $data = $resource->toArray($request);

            $this->assertEquals(PointType::tryFrom($expectedText)->label(), $data['type_label']);
        }
    }

    /**
     * 创建模拟请求对象
     */
    private function createRequest(): Request
    {
        return new Request;
    }
}
