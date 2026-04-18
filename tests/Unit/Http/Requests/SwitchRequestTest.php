<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Requests;

use App\Enum\StatusSwitch;
use App\Http\Requests\SwitchRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(SwitchRequest::class)]
class SwitchRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestDox('验证有效的请求数据通过验证')]
    public function test_valid_request_passes_validation()
    {
        $request = new SwitchRequest;

        $validData = [
            'id' => 1,
            'status' => StatusSwitch::ENABLED->value,
        ];

        $validator = $this->app['validator']->make($validData, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    #[TestDox('验证缺少 id 字段的请求失败')]
    public function test_request_fails_without_id()
    {
        $request = new SwitchRequest;

        $invalidData = [
            'status' => StatusSwitch::ENABLED->value,
        ];

        $validator = $this->app['validator']->make($invalidData, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('id', $validator->errors()->toArray());
    }

    #[Test]
    #[TestDox('验证 id 字段不是整数的请求失败')]
    public function test_request_fails_with_non_integer_id()
    {
        $request = new SwitchRequest;

        $invalidData = [
            'id' => 'string',
            'status' => StatusSwitch::ENABLED->value,
        ];

        $validator = $this->app['validator']->make($invalidData, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('id', $validator->errors()->toArray());
    }

    #[Test]
    #[TestDox('验证缺少 status 字段的请求失败')]
    public function test_request_fails_without_status()
    {
        $request = new SwitchRequest;

        $invalidData = [
            'id' => 1,
        ];

        $validator = $this->app['validator']->make($invalidData, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    #[Test]
    #[TestDox('验证 status 字段不是有效枚举值的请求失败')]
    public function test_request_fails_with_invalid_status_enum()
    {
        $request = new SwitchRequest;

        $invalidData = [
            'id' => 1,
            'status' => 'invalid_status',
        ];

        $validator = $this->app['validator']->make($invalidData, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    #[Test]
    #[TestDox('验证使用 OFF 状态的请求通过')]
    public function test_request_with_off_status_passes()
    {
        $request = new SwitchRequest;

        $validData = [
            'id' => 1,
            'status' => StatusSwitch::DISABLED->value,
        ];

        $validator = $this->app['validator']->make($validData, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    #[TestDox('验证默认授权方法返回 true')]
    public function test_default_authorize_method_returns_true()
    {
        $request = new SwitchRequest;

        $this->assertTrue($request->authorize());
    }
}
