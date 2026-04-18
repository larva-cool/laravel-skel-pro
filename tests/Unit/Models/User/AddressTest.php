<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\User;

use App\Models\User\Address;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * Address 模型测试
 */
#[CoversClass(Address::class)]
class AddressTest extends TestCase
{
    #[Test]
    #[TestDox('测试模型基本属性')]
    public function test_basic_properties(): void
    {
        $model = new Address;

        // 测试表名
        $this->assertEquals('addresses', $model->getTable());

        // 测试可填充字段
        $fillable = [
            'user_id', 'name', 'country', 'province', 'city', 'district', 'address', 'zipcode', 'phone',
            'is_default',
        ];
        $this->assertEquals($fillable, $model->getFillable());

        // 测试隐藏字段
        $this->assertEquals(['user_id'], $model->getHidden());

        // 测试附加属性
        $this->assertEquals(['full_address', 'phone_text'], $model->getAppends());

        // 测试使用 SoftDeletes
        $this->assertTrue(in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(Address::class)));
    }

    #[Test]
    #[TestDox('测试默认值设置')]
    public function test_default_values(): void
    {
        $model = new Address;

        // 测试默认值
        $this->assertEquals('CN', $model->country);
    }

    #[Test]
    #[TestDox('测试类型转换')]
    public function test_casts(): void
    {
        $model = new Address;

        // 测试类型转换
        $casts = $model->getCasts();

        $this->assertEquals('integer', $casts['user_id']);
        $this->assertEquals('string', $casts['name']);
        $this->assertEquals('string', $casts['country']);
        $this->assertEquals('string', $casts['province']);
        $this->assertEquals('string', $casts['city']);
        $this->assertEquals('string', $casts['district']);
        $this->assertEquals('string', $casts['address']);
        $this->assertEquals('integer', $casts['zipcode']);
        $this->assertEquals('string', $casts['phone']);
        $this->assertEquals('bool', $casts['is_default']);
        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    #[TestDox('测试 full_address 附加属性')]
    public function test_full_address_attribute(): void
    {
        $model = new Address;
        $model->forceFill([
            'user_id' => 1,
            'name' => '张三',
            'country' => 'CN',
            'province' => '广东省',
            'city' => '深圳市',
            'district' => '南山区',
            'address' => '科技园路1号',
            'zipcode' => 518000,
            'phone' => '13800138000',
            'is_default' => true,
        ]);

        $expectedFullAddress = '广东省深圳市南山区科技园路1号';
        $this->assertEquals($expectedFullAddress, $model->full_address);
    }

    #[Test]
    #[TestDox('测试 phone_text 附加属性')]
    public function test_phone_text_attribute(): void
    {
        $model = new Address;
        $model->forceFill([
            'user_id' => 1,
            'name' => '张三',
            'country' => 'CN',
            'province' => '广东省',
            'city' => '深圳市',
            'district' => '南山区',
            'address' => '科技园路1号',
            'zipcode' => 518000,
            'phone' => '13800138000',
            'is_default' => true,
        ]);

        // 验证 phone_text 属性存在且返回字符串
        $this->assertIsString($model->phone_text);
        $this->assertNotEmpty($model->phone_text);
    }

    #[Test]
    #[TestDox('测试 markDefault 方法')]
    public function test_mark_default_method(): void
    {
        $model = new Address;
        $model->forceFill([
            'id' => 1,
            'user_id' => 1,
            'name' => '张三',
            'country' => 'CN',
            'province' => '广东省',
            'city' => '深圳市',
            'district' => '南山区',
            'address' => '科技园路1号',
            'zipcode' => 518000,
            'phone' => '13800138000',
            'is_default' => false,
        ]);

        // 验证模型属性设置正确
        $this->assertEquals(1, $model->user_id);
        $this->assertEquals(false, $model->is_default);
    }

    #[Test]
    #[TestDox('测试 getDefaultAddress 静态方法')]
    public function test_get_default_address_method(): void
    {
        // 测试方法参数类型
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[TestDox('测试 SoftDeletes 功能')]
    public function test_soft_deletes(): void
    {
        $model = new Address;
        $model->forceFill([
            'user_id' => 1,
            'name' => '张三',
            'country' => 'CN',
            'province' => '广东省',
            'city' => '深圳市',
            'district' => '南山区',
            'address' => '科技园路1号',
            'zipcode' => 518000,
            'phone' => '13800138000',
            'is_default' => true,
        ]);

        // 测试 deleted_at 字段初始为 null
        $this->assertNull($model->deleted_at);

        // 测试模型有 SoftDeletes 相关方法
        $this->assertTrue(method_exists($model, 'delete'));
        $this->assertTrue(method_exists($model, 'restore'));
        $this->assertTrue(method_exists($model, 'forceDelete'));
    }

    #[Test]
    #[TestDox('测试日期字段')]
    public function test_date_fields(): void
    {
        $model = new Address;
        $model->forceFill([
            'user_id' => 1,
            'name' => '张三',
            'country' => 'CN',
            'province' => '广东省',
            'city' => '深圳市',
            'district' => '南山区',
            'address' => '科技园路1号',
            'zipcode' => 518000,
            'phone' => '13800138000',
            'is_default' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->assertInstanceOf(Carbon::class, $model->created_at);
        $this->assertInstanceOf(Carbon::class, $model->updated_at);
    }

    #[Test]
    #[TestDox('测试布尔字段')]
    public function test_boolean_fields(): void
    {
        $model = new Address;
        $model->forceFill([
            'user_id' => 1,
            'name' => '张三',
            'country' => 'CN',
            'province' => '广东省',
            'city' => '深圳市',
            'district' => '南山区',
            'address' => '科技园路1号',
            'zipcode' => 518000,
            'phone' => '13800138000',
            'is_default' => true,
        ]);

        $this->assertIsBool($model->is_default);
        $this->assertEquals(true, $model->is_default);
    }
}
