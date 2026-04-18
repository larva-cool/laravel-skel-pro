<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\System;

use App\Models\System\Attachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 附件模型测试
 */
class AttachmentTest extends TestCase
{
    /**
     * 测试模型基本配置
     */
    #[Test]
    #[TestDox('测试模型基本配置')]
    public function test_model_basic_configuration(): void
    {
        $attachment = new Attachment;

        // 测试表名
        $this->assertEquals('attachments', $attachment->getTable());

        // 测试可填充字段
        $expectedFillable = [
            'user_id', 'storage', 'origin_name', 'file_name', 'file_path', 'mime_type', 'file_size', 'file_ext',
        ];
        $this->assertEquals($expectedFillable, $attachment->getFillable());
    }

    /**
     * 测试字段类型转换
     */
    #[Test]
    #[TestDox('测试字段类型转换')]
    public function test_field_casts(): void
    {
        $attachment = new Attachment;
        $casts = $attachment->getCasts();

        $this->assertEquals('integer', $casts['id']);
        $this->assertEquals('integer', $casts['user_id']);
        $this->assertEquals('string', $casts['storage']);
        $this->assertEquals('string', $casts['origin_name']);
        $this->assertEquals('string', $casts['file_name']);
        $this->assertEquals('string', $casts['file_path']);
        $this->assertEquals('string', $casts['mime_type']);
        $this->assertEquals('integer', $casts['file_size']);
        $this->assertEquals('string', $casts['file_ext']);
        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
    }

    /**
     * 测试 fileUrl 访问器 - 当 file_path 是有效的 URL 时
     */
    #[Test]
    #[TestDox('测试 fileUrl 访问器 - 当 file_path 是有效的 URL 时')]
    public function test_file_url_accessor_with_valid_url(): void
    {
        // 模拟 URL::isValidUrl 方法
        URL::shouldReceive('isValidUrl')->with('https://example.com/test.jpg')->andReturn(true);

        // 创建 Attachment 实例
        $attachment = new Attachment;
        $attachment->file_path = 'https://example.com/test.jpg';

        // 测试 fileUrl 访问器
        $this->assertEquals('https://example.com/test.jpg', $attachment->file_url);
    }

    /**
     * 测试 fileUrl 访问器 - 当 file_path 不是有效的 URL 时
     */
    #[Test]
    #[TestDox('测试 fileUrl 访问器 - 当 file_path 不是有效的 URL 时')]
    public function test_file_url_accessor_with_invalid_url(): void
    {
        // 模拟 URL::isValidUrl 方法
        URL::shouldReceive('isValidUrl')->with('uploads/test.jpg')->andReturn(false);

        // 模拟 Storage::url 方法
        Storage::shouldReceive('url')->with('uploads/test.jpg')->andReturn('http://localhost/storage/uploads/test.jpg');

        // 创建 Attachment 实例
        $attachment = new Attachment;
        $attachment->file_path = 'uploads/test.jpg';

        // 测试 fileUrl 访问器
        $this->assertEquals('http://localhost/storage/uploads/test.jpg', $attachment->file_url);
    }

    /**
     * 测试 user 关联关系
     */
    #[Test]
    #[TestDox('测试 user 关联关系')]
    public function test_user_relation(): void
    {
        $attachment = new Attachment;
        $relation = $attachment->user();

        // 测试关联类型
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\BelongsTo', $relation);

        // 测试关联的模型
        $this->assertEquals('App\Models\User', get_class($relation->getRelated()));
    }
}
