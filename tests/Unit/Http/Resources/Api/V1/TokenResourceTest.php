<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\TokenResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(TokenResource::class)]
#[TestDox('TokenResource 测试')]
class TokenResourceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    #[TestDox('测试 TokenResource 正确转换数据')]
    public function test_token_resource_transforms_data_correctly()
    {
        // 创建请求对象
        $request = new Request;
        // Create a user
        $user = User::factory()->create();

        // Create a personal access token
        $token = $user->createBaseToken('test-token', ['*']);
        $accessToken = $token->accessToken;

        // Update last_used_at and expires_at for testing
        $accessToken->last_used_at = now();
        $accessToken->expires_at = now()->addYear();
        $accessToken->save();

        // Create the resource
        $resource = new TokenResource($accessToken);
        $data = $resource->toArray($request);

        // Assertions
        $this->assertEquals($accessToken->id, $data['id']);
        $this->assertEquals($accessToken->name, $data['name']);
        $this->assertEquals($accessToken->created_at->toDateTimeString(), $data['created_at']);
        $this->assertEquals($accessToken->last_used_at->toDateTimeString(), $data['last_used_at']);
        $this->assertEquals($accessToken->expires_at->toDateTimeString(), $data['expires_at']);
    }
}
