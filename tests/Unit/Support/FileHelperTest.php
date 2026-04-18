<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\FileHelper;
use Illuminate\Foundation\Testing\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(FileHelper::class)]
#[TestDox('测试 FileHelper 类的方法')]
class FileHelperTest extends TestCase
{
    #[Test]
    #[TestDox('测试 get 方法读取文件内容')]
    public function test_get_method_success()
    {
        // 创建临时文件
        $testContent = '测试内容';
        $testFilePath = FileHelper::write(storage_path('framework/testing/test-file.txt'), $testContent);

        // 测试get方法
        $content = FileHelper::get($testFilePath);

        // 断言
        $this->assertEquals($testContent, $content);

        // 清理
        @unlink($testFilePath);
    }

    #[Test]
    #[TestDox('测试 get 方法读取不存在的文件时返回 false')]
    public function test_get_method_file_not_exists()
    {
        // 测试不存在的文件
        $content = FileHelper::get(storage_path('framework/testing/non-existent-file.txt'));

        // 断言
        $this->assertFalse($content);
    }

    #[Test]
    #[TestDox('测试 write 方法成功写入文件')]
    public function test_write_method_success()
    {
        // 准备测试数据
        $testFilePath = storage_path('framework/testing/test-write-file.txt');
        $testContent = '写入测试内容';

        // 测试write方法
        $result = FileHelper::write($testFilePath, $testContent);

        // 断言
        $this->assertNotFalse($result);
        $this->assertEquals($testContent, file_get_contents($testFilePath));

        // 清理
        @unlink($testFilePath);
    }

    #[Test]
    #[TestDox('测试 json 方法成功读取 JSON 文件')]
    public function test_json_method_success()
    {
        // 准备测试数据
        $testFilePath = storage_path('framework/testing/test-json-file.json');
        $testData = ['name' => '测试', 'value' => 123];
        file_put_contents($testFilePath, json_encode($testData));

        // 测试json方法
        $data = FileHelper::json($testFilePath);

        // 断言
        $this->assertEquals($testData, $data);

        // 清理
        @unlink($testFilePath);
    }

    #[Test]
    #[TestDox('测试 json 方法读取不存在的 JSON 文件时返回空数组')]
    public function test_json_method_file_not_exists()
    {
        // 测试不存在的文件
        $data = FileHelper::json(storage_path('framework/testing/non-existent-json-file.json'));

        // 断言
        $this->assertEquals([], $data);
    }

    #[Test]
    #[TestDox('测试 json 方法读取无效的 JSON 文件时返回空数组')]
    public function test_json_method_invalid_json()
    {
        // 准备测试数据
        $testFilePath = storage_path('framework/testing/test-invalid-json-file.json');
        file_put_contents($testFilePath, '无效的JSON格式');

        // 测试json方法
        $data = FileHelper::json($testFilePath);

        // 断言
        $this->assertEquals([], $data);

        // 清理
        @unlink($testFilePath);
    }

    #[Test]
    #[TestDox('测试 writeJson 方法成功写入 JSON 文件')]
    public function test_write_json_method_success()
    {
        // 准备测试数据
        $testFilePath = storage_path('framework/testing/test-write-json-file.json');
        $testData = ['name' => '测试', 'value' => 123];

        // 测试writeJson方法
        $result = FileHelper::writeJson($testFilePath, $testData);

        // 断言
        $this->assertNotFalse($result);
        $this->assertEquals($testData, json_decode(file_get_contents($testFilePath), true));

        // 清理
        @unlink($testFilePath);
    }

    #[Test]
    #[TestDox('测试 makeDirectory 方法成功创建目录')]
    public function test_make_directory_method_success()
    {
        // 准备测试数据
        $testDirPath = storage_path('framework/testing/test-directory');

        // 确保目录不存在
        if (is_dir($testDirPath)) {
            rmdir($testDirPath);
        }

        // 测试makeDirectory方法，使用recursive=true
        $result = FileHelper::makeDirectory($testDirPath, 0755, true);

        // 断言
        $this->assertTrue($result);
        $this->assertDirectoryExists($testDirPath);

        // 清理
        if (is_dir($testDirPath)) {
            rmdir($testDirPath);
        }
    }

    #[Test]
    #[TestDox('测试 makeDirectory 方法在目录已存在时返回 true')]
    public function test_make_directory_method_directory_exists()
    {
        // 准备测试数据
        $testDirPath = storage_path('framework/testing/test-directory');

        // 确保目录存在
        if (! is_dir($testDirPath)) {
            mkdir($testDirPath, 0755, true);
        }

        // 测试makeDirectory方法
        $result = FileHelper::makeDirectory($testDirPath);

        // 断言
        $this->assertTrue($result);

        // 清理
        if (is_dir($testDirPath)) {
            rmdir($testDirPath);
        }
    }

    #[Test]
    #[TestDox('测试 generateDirectoryPath 方法生成目录路径')]
    public function test_generate_directory_path_method()
    {
        // 测试不同ID的情况
        $testCases = [
            [123, 'avatar', 'avatar/000/0001'],
            [123456789, 'images', 'images/123/4567'],
            [999999999, 'files', 'files/999/9999'],
        ];

        foreach ($testCases as [$id, $directory, $expected]) {
            $result = FileHelper::generateDirectoryPath($id, $directory);
            $this->assertEquals($expected, $result);
        }
    }
}
