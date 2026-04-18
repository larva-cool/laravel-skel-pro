<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\User;

use App\Enum\Gender;
use App\Models\System\Area;
use App\Models\User;
use App\Models\User\UserProfile;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * UserProfile 模型测试
 */
#[CoversClass(UserProfile::class)]
class UserProfileTest extends TestCase
{
    #[Test]
    #[TestDox('测试模型基本属性')]
    public function test_basic_properties(): void
    {
        $model = new UserProfile;

        // 测试表名
        $this->assertEquals('user_profiles', $model->getTable());

        // 测试主键
        $this->assertEquals('user_id', $model->getKeyName());

        // 测试非自增
        $this->assertFalse($model->getIncrementing());

        // 测试无时间戳
        $this->assertFalse($model->usesTimestamps());

        // 测试可填充字段
        $fillable = [
            'gender', 'birthday', 'province_id', 'city_id', 'district_id', 'website', 'intro', 'bio',
        ];
        $this->assertEquals($fillable, $model->getFillable());

        // 测试隐藏字段
        $this->assertEquals(['user_id'], $model->getHidden());

        // 测试附加属性
        $this->assertEquals(['gender_label'], $model->getAppends());
    }

    #[Test]
    #[TestDox('测试默认值设置')]
    public function test_default_values(): void
    {
        $model = new UserProfile;

        // 测试默认值
        $this->assertInstanceOf(Gender::class, $model->gender);
        $this->assertEquals(Gender::GENDER_UNKNOWN, $model->gender);
    }

    #[Test]
    #[TestDox('测试类型转换')]
    public function test_casts(): void
    {
        $model = new UserProfile;

        // 测试类型转换
        $casts = $model->getCasts();

        $this->assertEquals('integer', $casts['user_id']);
        $this->assertEquals(Gender::class, $casts['gender']);
        $this->assertEquals('date:Y-m-d', $casts['birthday']);
        $this->assertEquals('integer', $casts['province_id']);
        $this->assertEquals('integer', $casts['city_id']);
        $this->assertEquals('integer', $casts['district_id']);
        $this->assertEquals('string', $casts['website']);
        $this->assertEquals('string', $casts['intro']);
        $this->assertEquals('string', $casts['bio']);
    }

    #[Test]
    #[TestDox('测试 gender_label 访问器')]
    public function test_gender_label_accessor(): void
    {
        // 测试 Gender 枚举的 label() 方法（gender_label 访问器内部使用）
        $this->assertEquals('保密', Gender::GENDER_UNKNOWN->label());
        $this->assertEquals('男', Gender::GENDER_MALE->label());
        $this->assertEquals('女', Gender::GENDER_FEMALE->label());
    }

    #[Test]
    #[TestDox('测试 user 关联关系')]
    public function test_user_relation(): void
    {
        $model = new UserProfile;
        $relation = $model->user();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getOwnerKeyName());
        $this->assertEquals(User::class, $relation->getRelated()::class);
    }

    #[Test]
    #[TestDox('测试 province 关联关系')]
    public function test_province_relation(): void
    {
        $model = new UserProfile;
        $relation = $model->province();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('province_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getOwnerKeyName());
        $this->assertEquals(Area::class, $relation->getRelated()::class);
    }

    #[Test]
    #[TestDox('测试 city 关联关系')]
    public function test_city_relation(): void
    {
        $model = new UserProfile;
        $relation = $model->city();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('city_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getOwnerKeyName());
        $this->assertEquals(Area::class, $relation->getRelated()::class);
    }

    #[Test]
    #[TestDox('测试 district 关联关系')]
    public function test_district_relation(): void
    {
        $model = new UserProfile;
        $relation = $model->district();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('district_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getOwnerKeyName());
        $this->assertEquals(Area::class, $relation->getRelated()::class);
    }

    #[Test]
    #[TestDox('测试生日日期字段')]
    public function test_birthday_field(): void
    {
        $birthday = Carbon::now()->subYears(20);
        $model = new UserProfile;
        $model->forceFill([
            'user_id' => 1,
            'birthday' => $birthday,
        ]);

        $this->assertInstanceOf(Carbon::class, $model->birthday);
        $this->assertEquals($birthday->toDateString(), $model->birthday->toDateString());
    }

    #[Test]
    #[TestDox('测试字符串字段')]
    public function test_string_fields(): void
    {
        $model = new UserProfile;
        $model->forceFill([
            'user_id' => 1,
            'website' => 'https://example.com',
            'intro' => '这是个人介绍',
            'bio' => '这是个性签名',
        ]);

        $this->assertIsString($model->website);
        $this->assertEquals('https://example.com', $model->website);
        $this->assertIsString($model->intro);
        $this->assertEquals('这是个人介绍', $model->intro);
        $this->assertIsString($model->bio);
        $this->assertEquals('这是个性签名', $model->bio);
    }

    #[Test]
    #[TestDox('测试地区 ID 字段')]
    public function test_area_id_fields(): void
    {
        $model = new UserProfile;
        $model->forceFill([
            'user_id' => 1,
            'province_id' => 1,
            'city_id' => 2,
            'district_id' => 3,
        ]);

        $this->assertIsInt($model->province_id);
        $this->assertEquals(1, $model->province_id);
        $this->assertIsInt($model->city_id);
        $this->assertEquals(2, $model->city_id);
        $this->assertIsInt($model->district_id);
        $this->assertEquals(3, $model->district_id);
    }

    #[Test]
    #[TestDox('测试空值处理')]
    public function test_null_values(): void
    {
        $model = new UserProfile;
        $model->forceFill([
            'user_id' => 1,
            'birthday' => null,
            'province_id' => null,
            'city_id' => null,
            'district_id' => null,
            'website' => null,
            'intro' => null,
            'bio' => null,
        ]);

        $this->assertNull($model->birthday);
        $this->assertNull($model->province_id);
        $this->assertNull($model->city_id);
        $this->assertNull($model->district_id);
        $this->assertNull($model->website);
        $this->assertNull($model->intro);
        $this->assertNull($model->bio);
    }
}
