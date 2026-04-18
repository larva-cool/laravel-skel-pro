<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\Agreement;

use App\Enum\StatusSwitch;
use App\Models\Agreement\Agreement;
use App\Models\Agreement\AgreementRead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 测试协议已读记录模型
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
#[CoversClass(AgreementRead::class)]
class AgreementReadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试可填充属性
     */
    #[Test]
    #[TestDox('测试可填充属性')]
    public function test_fillable_attributes()
    {
        $fillable = (new AgreementRead)->getFillable();

        $this->assertEquals([
            'agreement_id', 'user_id',
        ], $fillable);
    }

    /**
     * 测试属性类型转换
     */
    #[Test]
    #[TestDox('测试属性类型转换')]
    public function test_casts()
    {
        $casts = (new AgreementRead)->getCasts();

        $this->assertEquals('integer', $casts['id']);
        $this->assertEquals('integer', $casts['agreement_id']);
        $this->assertEquals('integer', $casts['user_id']);
        $this->assertEquals('datetime', $casts['created_at']);
    }

    /**
     * 测试创建记录
     */
    #[Test]
    #[TestDox('测试创建记录')]
    public function test_create_record()
    {
        // 创建协议
        $agreement = Agreement::create([
            'type' => 'privacy',
            'title' => '隐私协议',
            'content' => '隐私协议内容',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
        ]);

        // 创建已读记录
        $readRecord = AgreementRead::create([
            'agreement_id' => $agreement->id,
            'user_id' => 1,
        ]);

        // 验证记录创建成功
        $this->assertNotNull($readRecord);
        $this->assertEquals($agreement->id, $readRecord->agreement_id);
        $this->assertEquals(1, $readRecord->user_id);
        $this->assertNotNull($readRecord->created_at);
    }

    /**
     * 测试没有更新时间
     */
    #[Test]
    #[TestDox('测试没有更新时间')]
    public function test_no_updated_at()
    {
        // 创建协议
        $agreement = Agreement::create([
            'type' => 'privacy',
            'title' => '隐私协议',
            'content' => '隐私协议内容',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
        ]);

        // 创建已读记录
        $readRecord = AgreementRead::create([
            'agreement_id' => $agreement->id,
            'user_id' => 1,
        ]);

        // 验证没有 updated_at 属性
        $this->assertFalse(property_exists($readRecord, 'updated_at'));
        $this->assertNull($readRecord->updated_at);
    }
}
