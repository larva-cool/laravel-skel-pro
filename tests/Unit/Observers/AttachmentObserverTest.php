<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Observers;

use App\Models\System\Attachment;
use App\Observers\AttachmentObserver;
use App\Services\UploadService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 附件模型观察者测试
 */
class AttachmentObserverTest extends TestCase
{
    /**
     * 测试 created 方法
     */
    #[Test]
    #[TestDox('测试 created 方法')]
    public function test_created_method(): void
    {
        // 创建 Attachment 实例
        $attachment = new Attachment;

        // 创建观察者实例
        $observer = new AttachmentObserver;

        // 调用 created 方法
        $observer->created($attachment);

        // 由于方法为空，只需确保调用不会报错
        $this->assertTrue(true);
    }

    /**
     * 测试 updated 方法
     */
    #[Test]
    #[TestDox('测试 updated 方法')]
    public function test_updated_method(): void
    {
        // 创建 Attachment 实例
        $attachment = new Attachment;

        // 创建观察者实例
        $observer = new AttachmentObserver;

        // 调用 updated 方法
        $observer->updated($attachment);

        // 由于方法为空，只需确保调用不会报错
        $this->assertTrue(true);
    }

    /**
     * 测试 deleted 方法
     */
    #[Test]
    #[TestDox('测试 deleted 方法')]
    public function test_deleted_method(): void
    {
        // 创建 Attachment 实例
        $attachment = new Attachment;
        $attachment->file_path = 'test/file/path.jpg';

        // 模拟 UploadService
        $this->mock(UploadService::class, function ($mock) {
            $mock->shouldReceive('make')->andReturnSelf();
            $mock->shouldReceive('destroy')->with('test/file/path.jpg')->once();
        });

        // 创建观察者实例
        $observer = new AttachmentObserver;

        // 调用 deleted 方法
        $observer->deleted($attachment);
    }
}
