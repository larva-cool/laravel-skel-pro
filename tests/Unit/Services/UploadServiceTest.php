<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\UploadService;
use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * UploadService 测试
 */
#[TestDox('UploadService 测试')]
class UploadServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试 setUp
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * 测试 getInstance 方法
     */
    #[Test]
    #[TestDox('测试 getInstance 方法')]
    public function test_get_instance()
    {
        // 模拟 Storage 门面
        $filesystemMock = $this->createMock(FilesystemAdapter::class);
        Storage::shouldReceive('disk')
            ->with('local')
            ->andReturn($filesystemMock);

        // 测试 getInstance 方法
        $fileService = UploadService::getInstance();
        $this->assertInstanceOf(UploadService::class, $fileService);
    }

    /**
     * 测试 url 方法
     */
    #[Test]
    #[TestDox('测试 url 方法')]
    public function test_url()
    {
        // 模拟 Storage 门面
        $filesystemMock = $this->createMock(FilesystemAdapter::class);
        $filesystemMock->method('url')
            ->with('test.jpg')
            ->willReturn('http://example.com/storage/test.jpg');
        Storage::shouldReceive('disk')
            ->with('local')
            ->andReturn($filesystemMock);

        // 测试 url 方法 - 相对路径
        $fileService = new UploadService;
        $result = $fileService->url('test.jpg');
        $this->assertIsString($result);

        // 测试 url 方法 - 绝对路径
        $result = $fileService->url('http://example.com/test.jpg');
        $this->assertEquals('http://example.com/test.jpg', $result);
    }

    /**
     * 测试 relativePath 方法
     */
    #[Test]
    #[TestDox('测试 relativePath 方法')]
    public function test_relative_path()
    {
        // 测试 relativePath 方法
        $fileService = new UploadService;
        $result = $fileService->relativePath('http://example.com/storage/test.jpg');
        $this->assertIsString($result);
    }

    /**
     * 测试 destroy 方法
     */
    #[Test]
    #[TestDox('测试 destroy 方法')]
    public function test_destroy()
    {
        // 模拟 Storage 门面
        $filesystemMock = $this->createMock(FilesystemAdapter::class);
        $filesystemMock->method('exists')
            ->with('/test.jpg')
            ->willReturn(true);
        $filesystemMock->method('delete')
            ->with('/test.jpg')
            ->willReturn(true);
        Storage::shouldReceive('disk')
            ->with('local')
            ->andReturn($filesystemMock);

        // 测试 destroy 方法
        $fileService = new UploadService;
        $fileService->destroy('http://example.com/test.jpg');
        $this->assertTrue(true); // 只要不抛出异常就算通过
    }

    /**
     * 测试 temporaryUrl 方法
     */
    #[Test]
    #[TestDox('测试 temporaryUrl 方法')]
    public function test_temporary_url()
    {
        // 模拟 Storage 门面
        $filesystemMock = $this->createMock(FilesystemAdapter::class);
        $filesystemMock->method('temporaryUrl')
            ->with('test.jpg', $this->anything())
            ->willReturn('http://example.com/temporary/test.jpg');
        Storage::shouldReceive('disk')
            ->with('local')
            ->andReturn($filesystemMock);

        // 测试 temporaryUrl 方法
        $fileService = new UploadService;
        $expiration = Carbon::now()->addHour();
        $result = $fileService->temporaryUrl('test.jpg', $expiration);
        $this->assertIsString($result);
    }

    /**
     * 测试 temporaryUploadUrl 方法
     */
    #[Test]
    #[TestDox('测试 temporaryUploadUrl 方法')]
    public function test_temporary_upload_url()
    {
        // 模拟 Storage 门面
        $filesystemMock = $this->createMock(FilesystemAdapter::class);
        $filesystemMock->method('temporaryUploadUrl')
            ->with('test.jpg', $this->anything(), [])
            ->willReturn([
                'url' => 'http://example.com/upload',
                'headers' => [],
            ]);
        Storage::shouldReceive('disk')
            ->with('local')
            ->andReturn($filesystemMock);

        // 测试 temporaryUploadUrl 方法
        $fileService = new UploadService;
        $expiration = Carbon::now()->addHour();
        $result = $fileService->temporaryUploadUrl('test.jpg', $expiration, []);
        $this->assertIsArray($result);
    }

    /**
     * 测试 path 方法
     */
    #[Test]
    #[TestDox('测试 path 方法')]
    public function test_path()
    {
        // 模拟 Storage 门面
        $filesystemMock = $this->createMock(FilesystemAdapter::class);
        $expectedPath = storage_path('app/test.jpg');
        $filesystemMock->method('path')
            ->with('test.jpg')
            ->willReturn($expectedPath);
        Storage::shouldReceive('disk')
            ->with('local')
            ->andReturn($filesystemMock);

        // 测试 path 方法
        $fileService = new UploadService;
        $result = $fileService->path('test.jpg');
        $this->assertEquals($expectedPath, $result);
    }

    /**
     * 测试 uploadFile 方法
     */
    #[Test]
    #[TestDox('测试 uploadFile 方法')]
    public function test_upload_file()
    {
        // 创建模拟上传文件
        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getClientOriginalName')
            ->willReturn('test.jpg');
        $uploadedFile->method('getSize')
            ->willReturn(1024);
        $uploadedFile->method('getClientOriginalExtension')
            ->willReturn('jpg');
        $uploadedFile->method('getClientMimeType')
            ->willReturn('image/jpeg');

        // 模拟 Storage 门面
        $filesystemMock = $this->createMock(FilesystemAdapter::class);
        $filesystemMock->method('exists')
            ->willReturn(false);
        $filesystemMock->method('putFileAs')
            ->willReturn('uploads/2024/01/26/test.jpg');
        Storage::shouldReceive('disk')
            ->with('local')
            ->andReturn($filesystemMock);

        // 测试 uploadFile 方法
        $fileService = new UploadService;
        $result = $fileService->uploadFile($uploadedFile);
        $this->assertIsArray($result);
        $this->assertEquals('local', $result['storage']);
        $this->assertEquals('test.jpg', $result['origin_name']);
        $this->assertEquals('uploads/2024/01/26/test.jpg', $result['file_path']);
        $this->assertEquals(1024, $result['file_size']);
        $this->assertEquals('jpg', $result['file_ext']);
        $this->assertEquals('image/jpeg', $result['mime_type']);
    }

    /**
     * 测试 uploadFile 方法 - 文件已存在
     */
    #[Test]
    #[TestDox('测试 uploadFile 方法 - 文件已存在')]
    public function test_upload_file_exists()
    {
        // 创建模拟上传文件
        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getClientOriginalName')
            ->willReturn('test.jpg');
        $uploadedFile->method('getSize')
            ->willReturn(1024);
        $uploadedFile->method('getClientOriginalExtension')
            ->willReturn('jpg');
        $uploadedFile->method('getClientMimeType')
            ->willReturn('image/jpeg');

        // 模拟 Storage 门面
        $filesystemMock = $this->createMock(FilesystemAdapter::class);
        $filesystemMock->method('exists')
            ->willReturn(true);
        $filesystemMock->method('putFileAs')
            ->willReturn('uploads/2024/01/26/test.jpg');
        Storage::shouldReceive('disk')
            ->with('local')
            ->andReturn($filesystemMock);

        // 测试 uploadFile 方法
        $fileService = new UploadService;
        $result = $fileService->uploadFile($uploadedFile);
        $this->assertIsArray($result);
    }

    /**
     * 测试 uploadFile 方法 - 上传失败
     */
    #[Test]
    #[TestDox('测试 uploadFile 方法 - 上传失败')]
    public function test_upload_file_failed()
    {
        // 创建模拟上传文件
        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getClientOriginalName')
            ->willReturn('test.jpg');
        $uploadedFile->method('getSize')
            ->willReturn(1024);
        $uploadedFile->method('getClientOriginalExtension')
            ->willReturn('jpg');
        $uploadedFile->method('getClientMimeType')
            ->willReturn('image/jpeg');

        // 模拟 Storage 门面
        $filesystemMock = $this->createMock(FilesystemAdapter::class);
        $filesystemMock->method('exists')
            ->willReturn(false);
        $filesystemMock->method('putFileAs')
            ->willReturn(false);
        Storage::shouldReceive('disk')
            ->with('local')
            ->andReturn($filesystemMock);

        // 测试 uploadFile 方法
        $fileService = new UploadService;
        $result = $fileService->uploadFile($uploadedFile);
        $this->assertFalse($result);
    }
}
