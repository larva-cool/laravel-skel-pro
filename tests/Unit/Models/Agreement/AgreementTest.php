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

    /**
     * 测试 reads 关联关系
     */
    #[Test]
    #[TestDox('测试 reads 关联关系')]
    public function test_reads_relation()
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
        $agreement->reads()->create(['user_id' => 1]);
        $agreement->reads()->create(['user_id' => 2]);

        // 测试关联关系
        $reads = $agreement->reads;
        $this->assertCount(2, $reads);
        $this->assertEquals(1, $reads[0]->user_id);
        $this->assertEquals(2, $reads[1]->user_id);
    }

    /**
     * 测试 markAsRead 方法
     */
    #[Test]
    #[TestDox('测试 markAsAgree 方法')]
    public function test_mark_as_agree()
    {
        // 创建协议
        $agreement = Agreement::create([
            'type' => 'privacy',
            'title' => '隐私协议',
            'content' => '隐私协议内容',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
        ]);

        // 标记为已读
        $result = $agreement->markAsAgree(1);
        $this->assertTrue($result);

        // 验证已读记录
        $readRecord = AgreementRead::where('agreement_id', $agreement->id)->where('user_id', 1)->first();
        $this->assertNotNull($readRecord);

        // 再次标记为已读（应该返回 true，但不会创建新记录）
        $result = $agreement->markAsAgree(1);
        $this->assertTrue($result);

        // 验证只创建了一条记录
        $readRecords = AgreementRead::where('agreement_id', $agreement->id)->get();
        $this->assertCount(1, $readRecords);
    }

    /**
     * 测试 getNotAgreedCount 方法
     */
    #[Test]
    #[TestDox('测试 getNotAgreedCount 方法')]
    public function test_get_not_agreed_count()
    {
        // 创建测试数据
        $agreement1 = Agreement::create([
            'type' => 'privacy',
            'title' => '隐私协议1',
            'content' => '隐私协议内容1',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
        ]);

        $agreement2 = Agreement::create([
            'type' => 'privacy',
            'title' => '隐私协议2',
            'content' => '隐私协议内容2',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
        ]);

        $agreement3 = Agreement::create([
            'type' => 'privacy',
            'title' => '隐私协议3',
            'content' => '隐私协议内容3',
            'status' => StatusSwitch::DISABLED,
            'admin_id' => 1,
        ]);

        // 标记一个协议为已读
        $agreement1->markAsAgree(1);

        // 获取未读协议数量
        $unreadCount = Agreement::getNotAgreedCount(1, 'privacy');

        // 验证结果（应该只有 agreement2 未读，agreement3 已禁用）
        $this->assertEquals(1, $unreadCount);
    }

    /**
     * 测试 getUnreadCount 方法（无未读协议）
     */
    #[Test]
    #[TestDox('测试 getNotAgreedCount 方法（无未读协议）')]
    public function test_get_not_agreed_count_no_unread()
    {
        // 创建测试数据
        $agreement1 = Agreement::create([
            'type' => 'privacy',
            'title' => '隐私协议1',
            'content' => '隐私协议内容1',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
        ]);

        $agreement2 = Agreement::create([
            'type' => 'privacy',
            'title' => '隐私协议2',
            'content' => '隐私协议内容2',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
        ]);

        // 标记所有协议为已读
        $agreement1->markAsAgree(1);
        $agreement2->markAsAgree(1);

        // 获取未读协议数量
        $unreadCount = Agreement::getNotAgreedCount(1, 'privacy');

        // 验证结果（应该为 0）
        $this->assertEquals(0, $unreadCount);
    }
}
