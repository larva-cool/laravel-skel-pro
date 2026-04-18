<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Api\V1\User\Address;

use App\Http\Requests\Api\V1\User\Address\UpdateAddressRequest;
use App\Rules\PhoneRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(UpdateAddressRequest::class)]
#[TestDox('地址更新请求测试')]
class UpdateAddressRequestTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected UpdateAddressRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new UpdateAddressRequest;
    }

    #[Test]
    #[TestDox('测试验证规则')]
    public function test_validation_rules()
    {
        // Valid data
        $validData = [
            'name' => $this->faker->name(),
            'country' => 'CN',
            'phone' => $this->faker->phoneNumber(),
            'is_default' => 1,
            'province' => 'Beijing',
            'city' => 'Beijing',
            'district' => 'Chaoyang',
            'address' => $this->faker->streetAddress(),
            'zipcode' => '100000',
        ];

        $validator = Validator::make($validData, $this->request->rules());
        $this->assertFalse($validator->fails());

        // Invalid phone data
        $invalidData = [
            'name' => $this->faker->name(),
            'country' => 'CN',
            'phone' => 'invalid-phone',
            'is_default' => 1,
            'province' => 'Beijing',
            'city' => 'Beijing',
            'district' => 'Chaoyang',
            'address' => $this->faker->streetAddress(),
            'zipcode' => '100000',
        ];

        $validator = Validator::make($invalidData, $this->request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('phone', $validator->errors()->messages());

        // Missing required fields
        $invalidData = [
            'name' => $this->faker->name(),
        ];

        $validator = Validator::make($invalidData, $this->request->rules());
        $this->assertTrue($validator->fails());
        $requiredFields = ['country', 'phone', 'is_default', 'province', 'city', 'district', 'address'];
        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $validator->errors()->messages());
        }
    }

    #[Test]
    #[TestDox('测试验证规则定义')]
    public function test_rules_definition()
    {
        $rules = $this->request->rules();

        $requiredFields = ['name', 'country', 'phone', 'is_default', 'province', 'city', 'district', 'address'];
        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $rules);
            $this->assertContains('required', $rules[$field]);
        }

        $this->assertContainsInstanceOf(PhoneRule::class, $rules['phone']);

        $this->assertArrayHasKey('zipcode', $rules);
        $this->assertContains('nullable', $rules['zipcode']);
        $this->assertContains('numeric', $rules['zipcode']);
    }

    protected function assertContainsInstanceOf(string $class, array $rules)
    {
        $found = false;
        foreach ($rules as $rule) {
            if ($rule instanceof $class) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "Failed to find instance of {$class} in rules");
    }
}
