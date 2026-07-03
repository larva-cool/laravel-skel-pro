<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\MainController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 主控制器测试类
 */
#[CoversClass(MainController::class)]
#[TestDox('主控制器测试')]
class MainControllerTest extends TestCase
{
    #[Test]
    #[TestDox('测试首页返回视图')]
    public function test_index_returns_view()
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('main.index');
    }

    #[Test]
    #[TestDox('测试重定向页面返回视图')]
    public function test_redirect_returns_view_with_url()
    {
        $url = 'https://example.com';
        $response = $this->get('/redirect?url='.urlencode($url));

        $response->assertOk();
        $response->assertViewIs('main.redirect');
        $response->assertViewHas('url', $url);
    }

    #[Test]
    #[TestDox('测试 headers 端点返回请求头信息')]
    public function test_headers_returns_request_info()
    {
        $response = $this->getJson('/headers', ['X-Custom-Header' => 'test-value']);

        $response->assertOk();
        $response->assertJsonStructure(['is_secure', 'servers', 'headers']);
    }
}
