<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\AddressResource;
use App\Models\User;
use App\Models\User\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(AddressResource::class)]
#[TestDox('AddressResource 测试')]
class AddressResourceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    #[TestDox('测试地址资源数据转换正确性')]
    public function test_address_resource_transforms_data_correctly()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an address
        $address = Address::factory()->create([
            'user_id' => $user->id,
            'name' => 'John Doe',
            'country' => 'CN',
            'province' => 'Beijing',
            'city' => 'Beijing',
            'district' => 'Haidian',
            'address' => '123 Main St',
            'zipcode' => '100000',
            'phone' => '12345678901',
            'is_default' => true,
        ]);

        // Create the resource
        $resource = new AddressResource($address);

        // 创建模拟请求对象
        $request = new Request;
        $data = $resource->toArray($request);

        // Assertions
        $this->assertEquals($address->id, $data['id']);
        $this->assertEquals($address->name, $data['name']);
        $this->assertEquals($address->country, $data['country']);
        $this->assertEquals($address->province, $data['province']);
        $this->assertEquals($address->city, $data['city']);
        $this->assertEquals($address->district, $data['district']);
        $this->assertEquals($address->address, $data['address']);
        $this->assertEquals($address->zipcode, $data['zipcode']);
        $this->assertEquals($address->phone, $data['phone']);
        $this->assertEquals($address->is_default, $data['is_default']);
        $this->assertEquals($address->created_at->toDateTimeString(), $data['created_at']);
        $this->assertEquals($address->updated_at->toDateTimeString(), $data['updated_at']);
        $this->assertEquals($address->full_address, $data['full_address']);
        $this->assertEquals($address->phone_text, $data['phone_text']);
    }
}
