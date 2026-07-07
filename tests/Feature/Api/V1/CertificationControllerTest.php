<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enum\CertificationStatus;
use App\Enum\CertificationType;
use App\Http\Controllers\Api\V1\CertificationController;
use App\Models\Certification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(CertificationController::class)]
#[Group('api')]
#[Group('certification')]
class CertificationControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    #[Test]
    #[TestDox('获取认证认证状态成功')]
    public function test_show_certification_status_success(): void
    {
        $response = $this->getJson('/api/v1/certification');

        $response->assertOk();
        $response->assertJsonStructure([
            'id',
            'type',
            'status',
            'status_label',
            'real_name',
            'id_card_no',
            'id_card_front',
            'id_card_back',
            'id_card_in_hand',
            'license',
            'contact_person',
            'contact_phone',
            'contact_email',
            'failed_reason',
            'submitted_at',
            'verified_at',
        ]);
    }

    #[Test]
    #[TestDox('获取已提交的认证信息')]
    public function test_show_submitted_certification(): void
    {
        $certification = Certification::factory()->create([
            'certifiable_type' => $this->user->getMorphClass(),
            'certifiable_id' => $this->user->id,
            'type' => CertificationType::PERSONAL,
            'status' => CertificationStatus::PENDING,
        ]);

        $response = $this->getJson('/api/v1/certification');

        $response->assertOk();
        $response->assertJson([
            'id' => $certification->id,
            'type' => CertificationType::PERSONAL->value,
            'status' => CertificationStatus::PENDING->value,
            'status_label' => '待审核',
        ]);
    }

    #[Test]
    #[TestDox('提交个人实名认证成功')]
    public function test_submit_personal_certification_success(): void
    {
        $data = [
            'real_name' => '张三',
            'id_card_no' => '110101199003078574',
            'id_card_front' => '/uploads/certifications/front.jpg',
            'id_card_back' => '/uploads/certifications/back.jpg',
            'id_card_in_hand' => '/uploads/certifications/hand.jpg',
        ];

        $response = $this->postJson('/api/v1/certification/personal', $data);

        $response->assertOk();
        $response->assertJson([
            'message' => '实名认证信息提交成功，请等待审核',
        ]);

        $this->assertDatabaseHas('certifications', [
            'certifiable_type' => $this->user->getMorphClass(),
            'certifiable_id' => $this->user->id,
            'type' => CertificationType::PERSONAL->value,
            'real_name' => '张三',
            'id_card_no' => '110101199003078574',
            'status' => CertificationStatus::PENDING->value,
        ]);
    }

    #[Test]
    #[TestDox('已认证用户无法重复提交个人认证')]
    public function test_certified_user_cannot_submit_personal_certification(): void
    {
        Certification::factory()->create([
            'certifiable_type' => $this->user->getMorphClass(),
            'certifiable_id' => $this->user->id,
            'type' => CertificationType::PERSONAL,
            'status' => CertificationStatus::APPROVED,
        ]);

        $data = [
            'real_name' => '张三',
            'id_card_no' => '110101199003078574',
            'id_card_front' => '/uploads/certifications/front.jpg',
            'id_card_back' => '/uploads/certifications/back.jpg',
            'id_card_in_hand' => '/uploads/certifications/hand.jpg',
        ];

        $response = $this->postJson('/api/v1/certification/personal', $data);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => '您已完成实名认证，无需重复提交',
        ]);
    }

    #[Test]
    #[TestDox('待审核用户无法重复提交个人认证')]
    public function test_pending_user_cannot_submit_personal_certification(): void
    {
        Certification::factory()->create([
            'certifiable_type' => $this->user->getMorphClass(),
            'certifiable_id' => $this->user->id,
            'type' => CertificationType::PERSONAL,
            'status' => CertificationStatus::PENDING,
        ]);

        $data = [
            'real_name' => '张三',
            'id_card_no' => '110101199003078574',
            'id_card_front' => '/uploads/certifications/front.jpg',
            'id_card_back' => '/uploads/certifications/back.jpg',
            'id_card_in_hand' => '/uploads/certifications/hand.jpg',
        ];

        $response = $this->postJson('/api/v1/certification/personal', $data);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => '您的认证正在审核中，请耐心等待',
        ]);
    }

    #[Test]
    #[TestDox('被拒绝的用户可以重新提交个人认证')]
    public function test_rejected_user_can_resubmit_personal_certification(): void
    {
        Certification::factory()->create([
            'certifiable_type' => $this->user->getMorphClass(),
            'certifiable_id' => $this->user->id,
            'type' => CertificationType::PERSONAL,
            'status' => CertificationStatus::REJECTED,
            'failed_reason' => '照片不清晰',
        ]);

        $data = [
            'real_name' => '张三',
            'id_card_no' => '110101199003078574',
            'id_card_front' => '/uploads/certifications/front_new.jpg',
            'id_card_back' => '/uploads/certifications/back_new.jpg',
            'id_card_in_hand' => '/uploads/certifications/hand_new.jpg',
        ];

        $response = $this->postJson('/api/v1/certification/personal', $data);

        $response->assertOk();
        $response->assertJson([
            'message' => '实名认证信息提交成功，请等待审核',
        ]);
    }

    #[Test]
    #[TestDox('提交个人认证缺少必填字段')]
    public function test_submit_personal_certification_missing_required_fields(): void
    {
        $data = [
            'real_name' => '张三',
            'id_card_no' => '110101199003078574',
            // 缺少 id_card_front, id_card_back, id_card_in_hand
        ];

        $response = $this->postJson('/api/v1/certification/personal', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id_card_front', 'id_card_back', 'id_card_in_hand']);
    }

    #[Test]
    #[TestDox('提交个人认证身份证号格式错误')]
    public function test_submit_personal_certification_invalid_id_card(): void
    {
        $data = [
            'real_name' => '张三',
            'id_card_no' => 'invalid_id_card',
            'id_card_front' => '/uploads/certifications/front.jpg',
            'id_card_back' => '/uploads/certifications/back.jpg',
            'id_card_in_hand' => '/uploads/certifications/hand.jpg',
        ];

        $response = $this->postJson('/api/v1/certification/personal', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id_card_no']);
    }

    #[Test]
    #[TestDox('提交企业实名认证成功')]
    public function test_submit_enterprise_certification_success(): void
    {
        $data = [
            'enterprise_name' => '测试科技有限公司',
            'license_no' => '91110108MA00123456',
            'license' => '/uploads/certifications/license.jpg',
            'contact_person' => '李四',
            'contact_phone' => '13800138000',
            'contact_email' => 'contact@example.com',
        ];

        $response = $this->postJson('/api/v1/certification/enterprise', $data);

        $response->assertOk();
        $response->assertJson([
            'message' => '企业认证信息提交成功，请等待审核',
        ]);

        $this->assertDatabaseHas('certifications', [
            'certifiable_type' => $this->user->getMorphClass(),
            'certifiable_id' => $this->user->id,
            'type' => CertificationType::ENTERPRISE->value,
            'real_name' => '测试科技有限公司',
            'id_card_no' => '91110108MA00123456',
            'status' => CertificationStatus::PENDING->value,
        ]);
    }

    #[Test]
    #[TestDox('已认证用户无法重复提交企业认证')]
    public function test_certified_user_cannot_submit_enterprise_certification(): void
    {
        Certification::factory()->create([
            'certifiable_type' => $this->user->getMorphClass(),
            'certifiable_id' => $this->user->id,
            'type' => CertificationType::ENTERPRISE,
            'status' => CertificationStatus::APPROVED,
        ]);

        $data = [
            'enterprise_name' => '测试科技有限公司',
            'license_no' => '91110108MA00123456',
            'license' => '/uploads/certifications/license.jpg',
            'contact_person' => '李四',
            'contact_phone' => '13800138000',
            'contact_email' => 'contact@example.com',
        ];

        $response = $this->postJson('/api/v1/certification/enterprise', $data);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => '您已完成实名认证，无需重复提交',
        ]);
    }

    #[Test]
    #[TestDox('提交企业认证缺少必填字段')]
    public function test_submit_enterprise_certification_missing_required_fields(): void
    {
        $data = [
            'enterprise_name' => '测试科技有限公司',
            'license_no' => '91110108MA00123456',
            // 缺少其他必填字段
        ];

        $response = $this->postJson('/api/v1/certification/enterprise', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['license', 'contact_person', 'contact_phone', 'contact_email']);
    }

    #[Test]
    #[TestDox('提交企业认证手机号格式错误')]
    public function test_submit_enterprise_certification_invalid_phone(): void
    {
        $data = [
            'enterprise_name' => '测试科技有限公司',
            'license_no' => '91110108MA00123456',
            'license' => '/uploads/certifications/license.jpg',
            'contact_person' => '李四',
            'contact_phone' => 'invalid_phone',
            'contact_email' => 'contact@example.com',
        ];

        $response = $this->postJson('/api/v1/certification/enterprise', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['contact_phone']);
    }

    #[Test]
    #[TestDox('提交企业认证邮箱格式错误')]
    public function test_submit_enterprise_certification_invalid_email(): void
    {
        $data = [
            'enterprise_name' => '测试科技有限公司',
            'license_no' => '91110108MA00123456',
            'license' => '/uploads/certifications/license.jpg',
            'contact_person' => '李四',
            'contact_phone' => '13800138000',
            'contact_email' => 'invalid_email',
        ];

        $response = $this->postJson('/api/v1/certification/enterprise', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['contact_email']);
    }

    #[Test]
    #[TestDox('未认证用户访问认证接口返回空数据')]
    public function test_unauthenticated_user_gets_empty_certification_data(): void
    {
        $response = $this->getJson('/api/v1/certification');

        $response->assertOk();
        $response->assertJson([
            'id' => null,
            'type' => null,
            'status' => 'unsubmitted',
            'status_label' => '未提交',
            'real_name' => null,
            'id_card_no' => null,
        ]);
    }
}
