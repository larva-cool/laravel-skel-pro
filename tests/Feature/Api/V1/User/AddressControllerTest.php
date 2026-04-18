<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Http\Controllers\Api\V1\User\AddressController;
use App\Models\User;
use App\Models\User\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(AddressController::class)]
#[TestDox('地址控制器测试')]
class AddressControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected Address $address;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->address = Address::factory()->create(['user_id' => $this->user->id]);
    }

    #[Test]
    #[TestDox('未认证用户无法访问地址相关端点')]
    public function unauthenticated_user_cannot_access_address_endpoints()
    {
        $endpoints = [
            ['GET', '/api/v1/user/address'],
            ['GET', '/api/v1/user/address/'.$this->address->id],
            ['POST', '/api/v1/user/address'],
            ['PUT', '/api/v1/user/address/'.$this->address->id],
            ['DELETE', '/api/v1/user/address/'.$this->address->id],
        ];

        foreach ($endpoints as [$method, $endpoint]) {
            $response = $this->json($method, $endpoint);
            $response->assertUnauthorized();
        }
    }

    #[Test]
    #[TestDox('用户可以获取地址列表')]
    public function user_can_get_address_list()
    {
        // Create additional address
        Address::factory()->count(2)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->json('GET', '/api/v1/user/address');

        $response->assertOk();
        $response->assertJsonCount(3);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'country',
                    'province',
                    'city',
                    'district',
                    'address',
                    'zipcode',
                    'phone',
                    'is_default',
                    'address',
                    'is_default',
                    'created_at',
                    'updated_at',
                    'full_address',
                    'phone_text',
                ],
            ],
        ]);
    }

    #[Test]
    #[TestDox('用户可以获取特定地址详情')]
    public function user_can_get_specific_address()
    {
        $response = $this->actingAs($this->user)->json('GET', "/api/v1/user/address/{$this->address->id}");

        $response->assertOk();
        $response->assertJson([
            'id' => $this->address->id,
            'name' => $this->address->name,
            'phone' => $this->address->phone,
            'address' => $this->address->address,
        ]);
    }

    #[Test]
    #[TestDox('用户无法获取其他用户的地址')]
    public function user_cannot_get_other_users_address()
    {
        $otherUser = User::factory()->create();
        $otherAddress = Address::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->json('GET', "/api/v1/user/address/{$otherAddress->id}");

        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('用户可以创建新地址')]
    public function user_can_create_new_address()
    {
        $data = [
            'name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'province' => $this->faker->state(),
            'city' => $this->faker->city(),
            'district' => $this->faker->streetName(),
            'address' => $this->faker->address,
            'is_default' => false,
        ];

        $response = $this->actingAs($this->user)->json('POST', '/api/v1/user/address', $data);

        $response->assertCreated();
        $this->assertDatabaseHas('addresses', [
            'user_id' => $this->user->id,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'address' => $data['address'],
        ]);
    }

    #[Test]
    #[TestDox('用户不能使用无效数据创建地址')]
    public function user_cannot_create_address_with_invalid_data()
    {
        $response = $this->actingAs($this->user)->json('POST', '/api/v1/user/address', [
            'name' => '', // Required
            'phone' => 'invalid-phone', // Invalid format
            'province' => 999, // Non-existent area
            'address' => '', // Required
        ]);
        $response->assertJsonValidationErrors(['name', 'phone', 'province', 'address']);
    }

    #[Test]
    #[TestDox('用户可以更新自己的地址')]
    public function user_can_update_own_address()
    {
        $updatedData = [
            'name' => 'Updated Name',
            'phone' => '13800138000',
            'address' => 'Updated Address',
            'province' => $this->faker->state(),
            'city' => $this->faker->city(),
            'district' => $this->faker->streetName(),
        ];

        $response = $this->actingAs($this->user)->json(
            'PUT',
            "/api/v1/user/address/{$this->address->id}",
            $updatedData
        );

        $response->assertOk();
        $this->assertDatabaseHas('addresses', array_merge([
            'id' => $this->address->id,
            'user_id' => $this->user->id,
        ], $updatedData));
    }

    #[Test]
    #[TestDox('用户不能更新其他用户的地址')]
    public function user_cannot_update_other_users_address()
    {
        $otherUser = User::factory()->create();
        $otherAddress = Address::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->json('PUT', "/api/v1/user/address/{$otherAddress->id}", [
            'name' => 'Hacked Name',
        ]);

        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('用户可以删除自己的地址')]
    public function user_can_delete_own_address()
    {
        $address = Address::factory()->create(['user_id' => $this->user->id]);
        $response = $this->actingAs($this->user)->json('DELETE', "/api/v1/user/address/{$address->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('addresses', ['id' => $address->id]);
    }

    #[Test]
    #[TestDox('用户不能删除其他用户的地址')]
    public function user_cannot_delete_other_users_address()
    {
        $otherUser = User::factory()->create();
        $otherAddress = Address::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->json('DELETE', "/api/v1/user/address/{$otherAddress->id}");

        $response->assertForbidden();
    }
}
