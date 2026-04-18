<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\UserDetailResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(UserDetailResource::class)]
#[TestDox('UserDetailResource 测试')]
class UserDetailResourceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    #[TestDox('测试用户详情资源数据转换正确性')]
    public function test_user_detail_resource_transforms_data_correctly()
    {
        // Create a user
        $user = User::factory()->create([
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'phone' => '12345678901',
            'avatar' => 'avatar.jpg',
            'available_points' => 100,
        ]);

        // Create user profile
        $user->profile->update([
            'gender' => 1,
            'birthday' => '1990-01-01',
            'website' => 'https://example.com',
            'intro' => 'This is my intro',
            'bio' => 'This is my bio',
        ]);

        // Create user extra
        $user->extra->update([
            'invite_code' => 'INVITE123',
        ]);

        // Create the resource
        $resource = new UserDetailResource($user);

        // 创建模拟请求对象
        $request = new Request;
        $data = $resource->toArray($request);

        // Assertions
        $this->assertEquals($user->id, $data['id']);
        $this->assertEquals($user->username, $data['username']);
        $this->assertEquals($user->email, $data['email']);
        $this->assertEquals($user->phone, $data['phone']);
        $this->assertEquals($user->name, $data['name']);
        $this->assertEquals($user->avatar, $data['avatar']);
        $this->assertEquals($user->available_points, $data['available_points']);

        // Profile assertions
        $this->assertEquals($user->profile->gender, $data['gender']);
        $this->assertEquals($user->profile->birthday->toDateString(), $data['birthday']);
        $this->assertEquals($user->profile->website, $data['website']);
        $this->assertEquals($user->profile->intro, $data['intro']);
        $this->assertEquals($user->profile->bio, $data['bio']);

        // Extra assertions
        $this->assertEquals($user->extra->invite_code, $data['invite_code']);

        // Timestamps
        $this->assertEquals($user->created_at->toDateTimeString(), $data['register_time']);
    }
}
