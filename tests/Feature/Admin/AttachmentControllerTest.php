<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\AttachmentController;
use App\Http\Middleware\RefreshUserActiveAt;
use App\Models\Admin\Admin;
use App\Models\System\Attachment;
use App\Support\UserHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * 后台附件管理控制器测试
 */
#[CoversClass(AttachmentController::class)]
#[TestDox('后台附件管理控制器测试')]
class AttachmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // 清空 attachments 表，避免影响测试
        Attachment::query()->delete();

        // 配置文件存储
        Storage::fake('public');

        Permission::findOrCreate('areas.index', 'admin');
        Permission::findOrCreate('areas.create', 'admin');
        Permission::findOrCreate('areas.delete', 'admin');

        $this->admin = $this->makeAdmin();
        $this->admin->givePermissionTo([
            'areas.index', 'areas.create', 'areas.delete',
        ]);
    }

    /**
     * 创建管理员（绕过 booted 事件）。
     */
    protected function makeAdmin(array $attributes = []): Admin
    {
        static $seq = 0;
        $seq++;
        $suffix = substr(md5((string) microtime(true).$seq.random_int(0, 9999)), 0, 8);

        $email = $attributes['email'] ?? "att_adm{$suffix}@example.com";
        $phone = $attributes['phone'] ?? '13'.str_pad((string) ($seq.random_int(1000000, 9999999)), 9, '0', STR_PAD_LEFT);
        $user = UserHelper::createByEmail($email, 'password123');

        return Admin::withoutEvents(function () use ($user, $email, $suffix, $attributes, $phone) {
            $admin = new Admin;
            $fill = array_merge([
                'user_id' => $user->id,
                'username' => "att_adm{$suffix}",
                'name' => '测试管理员'.$suffix,
                'email' => $email,
                'phone' => $phone,
                'status' => 1,
            ], $attributes);
            if (! isset($fill['password'])) {
                $fill['password'] = 'password123';
            }
            $admin->forceFill($fill);
            $admin->save();

            return $admin;
        });
    }

    /**
     * 以管理员身份登录并禁用 RefreshUserActiveAt 中间件。
     */
    protected function actingAsAdmin(?Admin $admin = null): self
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);

        return $this->actingAs($admin ?? $this->admin, 'admin');
    }

    /**
     * 创建一条附件记录。
     */
    protected function makeAttachment(array $attributes = []): Attachment
    {
        return Attachment::create(array_merge([
            'user_id' => $this->admin->user_id,
            'storage' => 'public',
            'origin_name' => 'test.jpg',
            'file_name' => 'test_'.time().'.jpg',
            'file_path' => 'uploads/test.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 1024,
            'file_ext' => 'jpg',
        ], $attributes));
    }

    #[Test]
    #[TestDox('未认证用户访问附件列表被重定向')]
    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);
        $response = $this->get('/admin/attachments');
        $response->assertRedirect('/admin/auth/login');
    }

    #[Test]
    #[TestDox('无权限用户返回403')]
    public function test_forbidden_without_permission(): void
    {
        $another = $this->makeAdmin();
        $this->actingAsAdmin($another);

        $response = $this->getJson('/admin/attachments');
        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('获取附件列表JSON')]
    public function test_index_returns_json_list(): void
    {
        $this->actingAsAdmin();
        $this->makeAttachment(['origin_name' => 'fileA.jpg']);
        $this->makeAttachment(['origin_name' => 'fileB.jpg']);

        $response = $this->getJson('/admin/attachments');

        $response->assertOk();
        $response->assertJsonStructure();
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    #[TestDox('按关键词搜索附件')]
    public function test_index_search_by_keyword(): void
    {
        $this->actingAsAdmin();
        $target = $this->makeAttachment(['file_name' => 'SpecialFile.jpg']);
        $this->makeAttachment(['file_name' => 'OtherFile.jpg']);

        $response = $this->getJson('/admin/attachments?keyword=SpecialFile');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($target->id, $data[0]['id']);
    }

    #[Test]
    #[TestDox('创建页面返回视图')]
    public function test_create_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/attachments/create');
        $response->assertOk();
        $response->assertViewIs('admin.attachment.create');
    }

    #[Test]
    #[TestDox('上传附件成功')]
    public function test_store_uploads_file(): void
    {
        $this->actingAsAdmin();

        $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');

        $response = $this->postJson('/admin/attachments', [
            'file' => $file,
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'code',
            'message',
            'data' => ['file_name', 'file_path', 'url'],
        ]);
    }

    #[Test]
    #[TestDox('删除附件成功')]
    public function test_destroy_deletes_attachment(): void
    {
        $this->actingAsAdmin();
        $attachment = $this->makeAttachment();

        $response = $this->deleteJson('/admin/attachments/'.$attachment->id);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.delete_success')]);
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
    }
}
