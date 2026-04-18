<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\ImageHelper;
use Exception;
use Illuminate\Foundation\Testing\TestCase;
use Intervention\Image\ImageManager;
use Intervention\Image\Laravel\Facades\Image;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(ImageHelper::class)]
#[TestDox('图片助手测试')]
class ImageHelperTest extends TestCase
{
    #[Test]
    #[TestDox('测试基本图片压缩功能')]
    public function test_basic_image_compression()
    {
        // 创建测试图片
        $testImagePath = storage_path('framework/testing/test-image.jpg');
        $this->createTestImage($testImagePath);
        $originalSize = filesize($testImagePath);
        // 压缩图片
        $compressedPath = ImageHelper::compress($testImagePath);

        // 验证压缩后的图片存在
        $this->assertFileExists($compressedPath);

        // 验证压缩后的文件大小小于原始文件
        $compressedSize = filesize($compressedPath);
        $this->assertLessThanOrEqual($originalSize, $compressedSize);

        // 清理测试文件
        @unlink($testImagePath);
        @unlink($compressedPath);
    }

    #[Test]
    #[TestDox('测试按宽度调整图片大小')]
    public function test_resize_by_width()
    {
        // 创建测试图片
        $testImagePath = storage_path('framework/testing/test-image-resize-width.jpg');
        $outputPath = storage_path('framework/testing/test-image-resized-width.jpg');
        $this->createTestImage($testImagePath, 1000, 800);

        // 按宽度缩放
        $resizedPath = ImageHelper::resize(
            $testImagePath,
            width: 500,
            outputPath: $outputPath
        );

        // 验证输出路径正确
        $this->assertEquals($outputPath, $resizedPath);

        // 验证文件存在
        $this->assertFileExists($resizedPath);

        // 验证尺寸正确（宽度500，高度400）
        $image = Image::read($resizedPath);
        $this->assertEquals(500, $image->width());
        $this->assertEquals(400, $image->height());

        // 清理测试文件
        @unlink($testImagePath);
        @unlink($outputPath);
    }

    #[Test]
    #[TestDox('测试按高度调整图片大小')]
    public function test_resize_by_height()
    {
        // 创建测试图片
        $testImagePath = storage_path('framework/testing/test-image-resize-height.jpg');
        $outputPath = storage_path('framework/testing/test-image-resized-height.jpg');
        $this->createTestImage($testImagePath, 1000, 800);

        // 按高度缩放
        $resizedPath = ImageHelper::resize(
            $testImagePath,
            height: 400,
            outputPath: $outputPath
        );

        // 验证输出路径正确
        $this->assertEquals($outputPath, $resizedPath);

        // 验证文件存在
        $this->assertFileExists($resizedPath);

        // 验证尺寸正确（宽度500，高度400）
        $image = Image::read($resizedPath);
        $this->assertEquals(500, $image->width());
        $this->assertEquals(400, $image->height());

        // 清理测试文件
        @unlink($testImagePath);
        @unlink($outputPath);
    }

    #[Test]
    #[TestDox('测试按比例调整图片大小')]
    public function test_resize_by_ratio()
    {
        // 创建测试图片
        $testImagePath = storage_path('framework/testing/test-image-resize-ratio.jpg');
        $outputPath = storage_path('framework/testing/test-image-resized-ratio.jpg');
        $this->createTestImage($testImagePath, 1000, 800);

        // 按比例缩放
        $resizedPath = ImageHelper::resize(
            $testImagePath,
            ratio: 0.5,
            outputPath: $outputPath
        );

        // 验证输出路径正确
        $this->assertEquals($outputPath, $resizedPath);

        // 验证文件存在
        $this->assertFileExists($resizedPath);

        // 验证尺寸正确（宽度500，高度400）
        $image = Image::read($resizedPath);
        $this->assertEquals(500, $image->width());
        $this->assertEquals(400, $image->height());

        // 清理测试文件
        @unlink($testImagePath);
        @unlink($outputPath);
    }

    #[Test]
    #[TestDox('测试同时指定宽度和高度调整图片大小')]
    public function test_resize_with_both_dimensions()
    {
        // 创建测试图片
        $testImagePath = storage_path('framework/testing/test-image-resize-both.jpg');
        $outputPath = storage_path('framework/testing/test-image-resized-both.jpg');
        $this->createTestImage($testImagePath, 1000, 800);

        // 同时提供宽度和高度
        $resizedPath = ImageHelper::resize(
            $testImagePath,
            width: 600,
            height: 500,
            outputPath: $outputPath
        );

        // 验证输出路径正确
        $this->assertEquals($outputPath, $resizedPath);

        // 验证文件存在
        $this->assertFileExists($resizedPath);

        // 验证尺寸正确（应该按较小比例缩放，宽度600，高度480）
        $image = Image::read($resizedPath);
        $this->assertEquals(600, $image->width());
        $this->assertEquals(480, $image->height());

        // 清理测试文件
        @unlink($testImagePath);
        @unlink($outputPath);
    }

    #[Test]
    #[TestDox('测试无效的调整大小参数')]
    public function test_invalid_resize_parameters()
    {
        // 创建测试图片
        $testImagePath = storage_path('framework/testing/test-image-invalid-params.jpg');
        $this->createTestImage($testImagePath);

        // 测试缺少所有参数
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('必须提供宽度、高度或缩放比例中的至少一个');
        ImageHelper::resize($testImagePath);

        // 测试无效宽度
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('宽度必须大于0');
        ImageHelper::resize($testImagePath, width: -100);

        // 测试无效高度
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('高度必须大于0');
        ImageHelper::resize($testImagePath, height: -100);

        // 测试无效比例
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('缩放比例必须在0-1之间');
        ImageHelper::resize($testImagePath, ratio: 1.5);

        // 测试无效比例（负数）
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('缩放比例必须在0-1之间');
        ImageHelper::resize($testImagePath, ratio: -0.5);

        // 清理测试文件
        @unlink($testImagePath);
    }

    #[Test]
    #[TestDox('测试调整不存在文件大小时抛出异常')]
    public function test_non_existent_file_exception_for_resize()
    {
        $nonExistentPath = storage_path('framework/testing/non-existent-image.jpg');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("图片文件不存在: $nonExistentPath");

        ImageHelper::resize($nonExistentPath, width: 500);
    }

    #[Test]
    #[TestDox('测试自定义压缩质量')]
    public function test_custom_quality()
    {
        // 创建测试图片
        $testImagePath = storage_path('framework/testing/test-image-custom.jpg');
        $outputPath = storage_path('framework/testing/test-image-compressed.jpg');
        $this->createTestImage($testImagePath);

        // 自定义参数压缩图片
        $compressedPath = ImageHelper::compress(
            $testImagePath,
            quality: 60,
            outputPath: $outputPath
        );

        // 验证输出路径正确
        $this->assertEquals($outputPath, $compressedPath);

        // 验证文件存在
        $this->assertFileExists($compressedPath);

        // 清理测试文件
        @unlink($testImagePath);
        @unlink($outputPath);
    }

    #[Test]
    #[TestDox('测试无效的压缩质量参数抛出异常')]
    public function test_invalid_quality_exception()
    {
        // 创建测试图片
        $testImagePath = storage_path('framework/testing/test-image-invalid.jpg');
        $this->createTestImage($testImagePath);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('压缩质量必须在0-100之间');

        ImageHelper::compress($testImagePath, quality: 150);

        // 清理测试文件
        @unlink($testImagePath);
    }

    #[Test]
    #[TestDox('测试基本绘制功能')]
    public function test_draw_basic()
    {
        // 创建背景图片
        $backgroundPath = storage_path('framework/testing/test-background.jpg');
        $this->createTestImage($backgroundPath);

        // 测试基本绘制（仅背景）
        $config = ['background' => $backgroundPath];
        $resultPath = ImageHelper::draw($config);

        // 验证输出图片存在
        $this->assertFileExists($resultPath);

        // 清理测试文件
        @unlink($backgroundPath);
        @unlink($resultPath);
    }

    #[Test]
    #[TestDox('测试背景+叠加图片绘制')]
    public function test_draw_with_places()
    {
        // 创建背景图片和叠加图片
        $backgroundPath = storage_path('framework/testing/test-background.jpg');
        $overlayPath = storage_path('framework/testing/test-overlay.jpg');
        $this->createTestImage($backgroundPath, 800, 600);
        $this->createTestImage($overlayPath, 200, 200);

        // 测试背景+叠加图片
        $config = [
            'background' => $backgroundPath,
            'places' => [
                [
                    'image' => $overlayPath,
                    'x' => 100,
                    'y' => 100,
                    'opacity' => 80,
                ],
            ],
        ];
        $resultPath = ImageHelper::draw($config);

        // 验证输出图片存在
        $this->assertFileExists($resultPath);

        // 清理测试文件
        @unlink($backgroundPath);
        @unlink($overlayPath);
        @unlink($resultPath);
    }

    #[Test]
    #[TestDox('测试背景+文本绘制')]
    public function test_draw_with_texts()
    {
        // 创建背景图片
        $backgroundPath = storage_path('framework/testing/test-background.jpg');
        $this->createTestImage($backgroundPath);

        // 测试背景+文本
        $config = [
            'background' => $backgroundPath,
            'texts' => [
                [
                    'content' => '测试文本',
                    'x' => 100,
                    'y' => 100,
                    'size' => 24,
                    'color' => '#000000',
                    'align' => 'left',
                    'valign' => 'top',
                ],
            ],
        ];
        $resultPath = ImageHelper::draw($config);

        // 验证输出图片存在
        $this->assertFileExists($resultPath);

        // 清理测试文件
        @unlink($backgroundPath);
        @unlink($resultPath);
    }

    #[Test]
    #[TestDox('测试背景+叠加图片+文本绘制')]
    public function test_draw_with_places_and_texts()
    {
        // 创建背景图片和叠加图片
        $backgroundPath = storage_path('framework/testing/test-background.jpg');
        $overlayPath = storage_path('framework/testing/test-overlay.jpg');
        $this->createTestImage($backgroundPath, 800, 600);
        $this->createTestImage($overlayPath, 200, 200);

        // 测试背景+叠加图片+文本
        $config = [
            'background' => $backgroundPath,
            'places' => [
                [
                    'image' => $overlayPath,
                    'x' => 100,
                    'y' => 100,
                ],
            ],
            'texts' => [
                [
                    'content' => '组合测试',
                    'x' => 400,
                    'y' => 300,
                    'size' => 30,
                    'color' => '#FF0000',
                    'align' => 'center',
                    'valign' => 'middle',
                ],
            ],
        ];
        $resultPath = ImageHelper::draw($config);

        // 验证输出图片存在
        $this->assertFileExists($resultPath);

        // 清理测试文件
        @unlink($backgroundPath);
        @unlink($overlayPath);
        @unlink($resultPath);
    }

    #[Test]
    #[TestDox('测试不同编码格式')]
    public function test_draw_with_different_encodes()
    {
        // 创建背景图片
        $backgroundPath = storage_path('framework/testing/test-background.jpg');
        $this->createTestImage($backgroundPath);

        // 测试不同编码格式
        $encodes = ['jpg', 'png', 'gif', 'webp'];
        foreach ($encodes as $encode) {
            $config = [
                'background' => $backgroundPath,
                'encode' => $encode,
            ];
            $resultPath = ImageHelper::draw($config);

            // 验证输出图片存在且扩展名正确
            $this->assertFileExists($resultPath);
            $this->assertEquals($encode, strtolower(pathinfo($resultPath, PATHINFO_EXTENSION)));

            // 清理测试文件
            @unlink($resultPath);
        }

        // 清理背景图片
        @unlink($backgroundPath);
    }

    #[Test]
    #[TestDox('测试自定义输出路径')]
    public function test_draw_with_custom_output_path()
    {
        // 创建背景图片
        $backgroundPath = storage_path('framework/testing/test-background.jpg');
        $outputDir = storage_path('framework/testing/test-custom-output-dir');
        $this->createTestImage($backgroundPath);

        // 确保输出目录存在
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // 测试自定义输出路径
        $config = ['background' => $backgroundPath];
        $resultPath = ImageHelper::draw($config, $outputDir);

        // 验证输出图片存在且路径正确
        $this->assertFileExists($resultPath);
        $this->assertStringStartsWith($outputDir, $resultPath);

        // 清理测试文件
        @unlink($backgroundPath);
        @unlink($resultPath);
        // 如果目录为空，删除目录
        if (is_dir($outputDir) && count(scandir($outputDir)) === 2) {
            @rmdir($outputDir);
        }
    }

    #[Test]
    #[TestDox('测试不存在的背景图片抛出异常')]
    public function test_draw_non_existent_background()
    {
        $nonExistentPath = storage_path('framework/testing/non-existent-background.jpg');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('图片文件不存在: '.$nonExistentPath);

        $config = ['background' => $nonExistentPath];
        ImageHelper::draw($config);
    }

    /**
     * 创建测试图片
     */
    private function createTestImage(string $path, int $width = 1000, int $height = 800): void
    {
        $image = ImageManager::gd()->create($width, $height);
        $image->save($path, 100);
    }
}
