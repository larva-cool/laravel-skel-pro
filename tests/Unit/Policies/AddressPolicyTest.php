<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Models\User\Address;
use App\Policies\AddressPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(AddressPolicy::class)]
#[TestDox('地址策略测试')]
class AddressPolicyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestDox('测试用户查看所有地址')]
    public function test_view_any()
    {
        $policy = new AddressPolicy;
        $user = User::factory()->create();

        // 验证任何用户都可以查看地址列表
        $this->assertTrue($policy->viewAny($user));
    }

    #[Test]
    #[TestDox('测试用户查看地址')]
    public function test_view()
    {
        $policy = new AddressPolicy;
        $user = User::factory()->create();
        $ownAddress = Address::factory()->create(['user_id' => $user->id]);
        $otherAddress = Address::factory()->create();

        // 验证用户可以查看自己的地址
        $this->assertTrue($policy->view($user, $ownAddress));

        // 验证用户不能查看他人的地址
        $this->assertFalse($policy->view($user, $otherAddress));
    }

    #[Test]
    #[TestDox('测试用户创建地址')]
    public function test_create()
    {
        $policy = new AddressPolicy;
        $user = User::factory()->create();

        // 验证任何用户都可以创建地址
        $this->assertTrue($policy->create($user));
    }

    #[Test]
    #[TestDox('测试用户更新地址')]
    public function test_update()
    {
        $policy = new AddressPolicy;
        $user = User::factory()->create();
        $ownAddress = Address::factory()->create(['user_id' => $user->id]);
        $otherAddress = Address::factory()->create();

        // 验证用户可以更新自己的地址
        $this->assertTrue($policy->update($user, $ownAddress));

        // 验证用户不能更新他人的地址
        $this->assertFalse($policy->update($user, $otherAddress));
    }

    #[Test]
    #[TestDox('测试用户删除地址')]
    public function test_delete()
    {
        $policy = new AddressPolicy;
        $user = User::factory()->create();
        $ownAddress = Address::factory()->create(['user_id' => $user->id]);
        $otherAddress = Address::factory()->create();

        // 验证用户可以删除自己的地址
        $this->assertTrue($policy->delete($user, $ownAddress));

        // 验证用户不能删除他人的地址
        $this->assertFalse($policy->delete($user, $otherAddress));
    }

    #[Test]
    #[TestDox('测试用户恢复地址')]
    public function test_restore()
    {
        $policy = new AddressPolicy;
        $user = User::factory()->create();
        $ownAddress = Address::factory()->create(['user_id' => $user->id]);
        $otherAddress = Address::factory()->create();

        // 验证用户可以恢复自己的地址
        $this->assertTrue($policy->restore($user, $ownAddress));

        // 验证用户不能恢复他人的地址
        $this->assertFalse($policy->restore($user, $otherAddress));
    }

    #[Test]
    #[TestDox('测试用户永久删除地址')]
    public function test_force_delete()
    {
        $policy = new AddressPolicy;
        $user = User::factory()->create();
        $ownAddress = Address::factory()->create(['user_id' => $user->id]);
        $otherAddress = Address::factory()->create();

        // 验证用户可以永久删除自己的地址
        $this->assertTrue($policy->forceDelete($user, $ownAddress));

        // 验证用户不能永久删除他人的地址
        $this->assertFalse($policy->forceDelete($user, $otherAddress));
    }
}
