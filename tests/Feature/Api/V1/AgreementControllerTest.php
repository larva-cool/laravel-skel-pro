<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enum\StatusSwitch;
use App\Http\Controllers\Api\V1\AgreementController;
use App\Models\System\Agreement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 协议控制器测试类
 */
#[CoversClass(AgreementController::class)]
#[TestDox('协议控制器测试')]
class AgreementControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestDox('测试按类型获取最新已发布的协议')]
    public function test_show_returns_latest_active_agreement()
    {
        // 创建多个协议，最后一个应该被返回（按 ID 降序）
        Agreement::create([
            'type' => 'user',
            'title' => '旧的用户协议',
            'content' => '旧内容',
            'status' => StatusSwitch::ENABLED->value,
            'order' => 0,
            'admin_id' => 1,
        ]);

        $latest = Agreement::create([
            'type' => 'user',
            'title' => '最新用户协议',
            'content' => '最新内容',
            'status' => StatusSwitch::ENABLED->value,
            'order' => 0,
            'admin_id' => 1,
        ]);

        $response = $this->getJson('/api/v1/agreement/user');

        $response->assertOk();
        $response->assertJsonStructure(['title', 'content', 'updated_at']);
        $response->assertJson([
            'title' => $latest->title,
            'content' => $latest->content,
        ]);
    }

    #[Test]
    #[TestDox('测试禁用状态的协议不应返回')]
    public function test_show_ignores_disabled_agreement()
    {
        // 创建一个已启用的协议
        $enabled = Agreement::create([
            'type' => 'privacy',
            'title' => '启用的协议',
            'content' => '启用的内容',
            'status' => StatusSwitch::ENABLED->value,
            'order' => 0,
            'admin_id' => 1,
        ]);

        // 创建一个已禁用的协议（ID 更大）
        Agreement::create([
            'type' => 'privacy',
            'title' => '禁用的协议',
            'content' => '禁用的内容',
            'status' => StatusSwitch::DISABLED->value,
            'order' => 0,
            'admin_id' => 1,
        ]);

        $response = $this->getJson('/api/v1/agreement/privacy');

        $response->assertOk();
        $response->assertJson([
            'title' => $enabled->title,
            'content' => $enabled->content,
        ]);
    }

    #[Test]
    #[TestDox('测试查询不存在类型的协议返回404')]
    public function test_show_returns_404_when_not_found()
    {
        $response = $this->getJson('/api/v1/agreement/non-existent-type');

        $response->assertNotFound();
    }

    #[Test]
    #[TestDox('测试不同类型的协议隔离')]
    public function test_show_isolates_agreements_by_type()
    {
        Agreement::create([
            'type' => 'user',
            'title' => '用户协议',
            'content' => '用户内容',
            'status' => StatusSwitch::ENABLED->value,
            'order' => 0,
            'admin_id' => 1,
        ]);

        Agreement::create([
            'type' => 'privacy',
            'title' => '隐私协议',
            'content' => '隐私内容',
            'status' => StatusSwitch::ENABLED->value,
            'order' => 0,
            'admin_id' => 1,
        ]);

        $response = $this->getJson('/api/v1/agreement/user');
        $response->assertOk();
        $response->assertJson(['title' => '用户协议']);

        $response = $this->getJson('/api/v1/agreement/privacy');
        $response->assertOk();
        $response->assertJson(['title' => '隐私协议']);
    }
}
