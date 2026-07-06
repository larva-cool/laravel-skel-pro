<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\UploaderController;
use App\Http\Middleware\RefreshUserActiveAt;
use App\Models\Admin\Admin;
use App\Models\System\Setting;
use App\Support\UserHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 后台文件上传控制器测试
 */
#[CoversClass(UploaderController::class)]
#[TestDox('后台文件上传控制器测试')]
class UploaderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::query()->delete();

        // 插入上传配置，避免 StoreAttachmentRequest 验证失败
        Setting::batchSet([
            ['key' => 'upload.storage', 'value' => 'local'],
            ['key' => 'upload.name_rule', 'value' => 'md5'],
            ['key' => 'upload.allow_extension', 'value' => 'jpg,png,jpeg,gif'],
            ['key' => 'upload.allow_video_extension', 'value' => 'mp4,avi,mov'],
        ]);

        // 配置文件存储
        Storage::fake('public');

        $this->admin = $this->makeAdmin();
    }

    /**
     * 创建管理员（绕过 booted 事件）。
     */
    protected function makeAdmin(array $attributes = []): Admin
    {
        static $seq = 0;
        $seq++;
        $suffix = substr(md5((string) microtime(true).$seq.random_int(0, 9999)), 0, 8);

        $email = $attributes['email'] ?? "up_adm{$suffix}@example.com";
        $phone = $attributes['phone'] ?? '13'.str_pad((string) ($seq.random_int(1000000, 9999999)), 9, '0', STR_PAD_LEFT);
        $user = UserHelper::createByEmail($email, 'password123');

        return Admin::withoutEvents(function () use ($user, $email, $suffix, $phone, $attributes) {
            $admin = new Admin;
            $fill = array_merge([
                'user_id' => $user->id,
                'username' => "up_adm{$suffix}",
                'name' => '测试管理员'.$suffix,
                'email' => $email,
                'phone' => $phone,
                'password' => 'password123',
                'status' => 1,
            ], $attributes);
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

    #[Test]
    #[TestDox('未认证用户上传图片被重定向到登录页')]
    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);
        $response = $this->post('/admin/uploader/aieditor-image');
        $response->assertRedirect('/admin/auth/login');
    }

    #[Test]
    #[TestDox('AiEditor 上传图片成功')]
    public function test_ai_editor_image_upload_success(): void
    {
        $this->actingAsAdmin();

        $file = UploadedFile::fake()->image('test.jpg', 100, 100);

        $response = $this->postJson('/admin/uploader/aieditor-image', [
            'file' => $file,
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'errorCode',
            'data' => ['src', 'alt', 'loading'],
        ]);
        $this->assertEquals(0, $response->json('errorCode'));
    }

    #[Test]
    #[TestDox('AiEditor 上传图片时文件必填')]
    public function test_ai_editor_image_requires_file(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/uploader/aieditor-image', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    #[Test]
    #[TestDox('AiEditor 上传非图片文件验证失败')]
    public function test_ai_editor_image_rejects_non_image(): void
    {
        $this->actingAsAdmin();

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->postJson('/admin/uploader/aieditor-image', [
            'file' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    #[Test]
    #[TestDox('AiEditor 上传视频成功')]
    public function test_ai_editor_video_upload_success(): void
    {
        $this->actingAsAdmin();

        $file = UploadedFile::fake()->create('test.mp4', 1024, 'video/mp4');

        $response = $this->postJson('/admin/uploader/aieditor-video', [
            'file' => $file,
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'errorCode',
            'data' => ['src', 'poster'],
        ]);
        $this->assertEquals(0, $response->json('errorCode'));
    }

    #[Test]
    #[TestDox('AiEditor 上传文件成功')]
    public function test_ai_editor_file_upload_success(): void
    {
        $this->actingAsAdmin();

        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

        $response = $this->postJson('/admin/uploader/aieditor-file', [
            'file' => $file,
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'errorCode',
            'data' => ['href', 'fileName'],
        ]);
        $this->assertEquals(0, $response->json('errorCode'));
    }

    #[Test]
    #[TestDox('TinyMCE 编辑器上传图片成功')]
    public function test_tinymce_upload_success(): void
    {
        $this->actingAsAdmin();

        $file = UploadedFile::fake()->image('tinymce.jpg', 200, 200);

        $response = $this->postJson('/admin/uploader/tinymce', [
            'file' => $file,
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'file_name',
            'file_path',
            'location',
        ]);
    }

    #[Test]
    #[TestDox('TinyMCE 上传时文件必填')]
    public function test_tinymce_requires_file(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/uploader/tinymce', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }
}
