<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\InsufficientCoinsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 测试金币不足异常
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
#[CoversClass(InsufficientCoinsException::class)]
class InsufficientCoinsExceptionTest extends TestCase
{
    /**
     * 测试默认构造函数
     */
    #[Test]
    #[TestDox('测试默认构造函数')]
    public function test_default_constructor()
    {
        $exception = new InsufficientCoinsException;

        $this->assertEquals('Insufficient coins', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * 测试自定义消息
     */
    #[Test]
    #[TestDox('测试自定义消息')]
    public function test_custom_message()
    {
        $customMessage = '金币不足，当前可用金币: 0';
        $exception = new InsufficientCoinsException($customMessage);

        $this->assertEquals($customMessage, $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * 测试自定义消息和代码
     */
    #[Test]
    #[TestDox('测试自定义消息和代码')]
    public function test_custom_message_and_code()
    {
        $customMessage = '金币不足，当前可用金币: 0';
        $customCode = 403;
        $exception = new InsufficientCoinsException($customMessage, $customCode);

        $this->assertEquals($customMessage, $exception->getMessage());
        $this->assertEquals($customCode, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * 测试自定义消息、代码和前一个异常
     */
    #[Test]
    #[TestDox('测试自定义消息、代码和前一个异常')]
    public function test_custom_message_code_and_previous()
    {
        $customMessage = '金币不足，当前可用金币: 0';
        $customCode = 403;
        $previousException = new \Exception('原始异常');
        $exception = new InsufficientCoinsException($customMessage, $customCode, $previousException);

        $this->assertEquals($customMessage, $exception->getMessage());
        $this->assertEquals($customCode, $exception->getCode());
        $this->assertEquals($previousException, $exception->getPrevious());
        $this->assertEquals('原始异常', $exception->getPrevious()->getMessage());
    }

    /**
     * 测试异常继承
     */
    #[Test]
    #[TestDox('测试异常继承')]
    public function test_exception_inheritance()
    {
        $exception = new InsufficientCoinsException;

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(InsufficientCoinsException::class, $exception);
    }
}
