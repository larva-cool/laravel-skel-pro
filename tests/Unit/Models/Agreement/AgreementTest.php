<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\Agreement;

use App\Enum\StatusSwitch;
use App\Models\System\Agreement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 测试协议管理模型
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
#[CoversClass(Agreement::class)]
class AgreementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试可填充属性
     */
    #[Test]
    #[TestDox('测试可填充属性')]
    public function test_fillable_attributes()
    {
        $fillable = (new Agreement)->getFillable();

        $this->assertEquals([
            'type', 'title', 'content', 'status', 'order', 'admin_id',
        ], $fillable);
    }

    /**
     * 测试默认属性
     */
    #[Test]
    #[TestDox('测试默认属性')]
    public function test_default_attributes()
    {
        $agreement = new Agreement;

        $this->assertEquals(StatusSwitch::ENABLED, $agreement->status);
        $this->assertEquals(0, $agreement->order);
    }

    /**
     * 测试属性类型转换
     */
    #[Test]
    #[TestDox('测试属性类型转换')]
    public function test_casts()
    {
        $casts = (new Agreement)->getCasts();

        $this->assertEquals('integer', $casts['id']);
        $this->assertEquals('string', $casts['type']);
        $this->assertEquals('string', $casts['title']);
        $this->assertEquals('string', $casts['content']);
        $this->assertEquals(StatusSwitch::class, $casts['status']);
        $this->assertEquals('integer', $casts['order']);
        $this->assertEquals('integer', $casts['admin_id']);
        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
    }

    /**
     * 测试 active 作用域
     */
    #[Test]
    #[TestDox('测试 active 作用域')]
    public function test_active_scope()
    {
        // 创建测试数据
        Agreement::create([
            'type' => 'privacy',
            'title' => '隐私协议',
            'content' => '隐私协议内容',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
        ]);

        Agreement::create([
            'type' => 'privacy',
            'title' => '隐私协议（已禁用）',
            'content' => '隐私协议内容',
            'status' => StatusSwitch::DISABLED,
            'admin_id' => 1,
        ]);

        Agreement::create([
            'type' => 'terms',
            'title' => '服务条款',
            'content' => '服务条款内容',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
        ]);

        // 测试 active 作用域
        $activePrivacyAgreements = Agreement::active('privacy')->get();
        $this->assertCount(1, $activePrivacyAgreements);
        $this->assertEquals('隐私协议', $activePrivacyAgreements->first()->title);

        $activeTermsAgreements = Agreement::active('terms')->get();
        $this->assertCount(1, $activeTermsAgreements);
        $this->assertEquals('服务条款', $activeTermsAgreements->first()->title);
    }
}
