<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\InsufficientPointsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * 积分不足异常测试
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
#[CoversClass(InsufficientPointsException::class)]
class InsufficientPointsExceptionTest extends TestCase
{
    /**
     * 测试默认构造函数
     */
    #[Test]
    public function test_default_constructor()
    {
        $exception = new InsufficientPointsException;

        $this->assertInstanceOf(InsufficientPointsException::class, $exception);
        $this->assertEquals('Insufficient points', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * 测试自定义消息
     */
    #[Test]
    public function test_custom_message()
    {
        $customMessage = '积分不足，无法完成操作';
        $exception = new InsufficientPointsException($customMessage);

        $this->assertEquals($customMessage, $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
    }

    /**
     * 测试自定义消息和代码
     */
    #[Test]
    public function test_custom_message_and_code()
    {
        $customMessage = '积分不足，无法完成操作';
        $customCode = 403;
        $exception = new InsufficientPointsException($customMessage, $customCode);

        $this->assertEquals($customMessage, $exception->getMessage());
        $this->assertEquals($customCode, $exception->getCode());
    }

    /**
     * 测试自定义消息、代码和前一个异常
     */
    #[Test]
    public function test_custom_message_code_and_previous()
    {
        $customMessage = '积分不足，无法完成操作';
        $customCode = 403;
        $previousException = new \Exception('前一个异常');
        $exception = new InsufficientPointsException($customMessage, $customCode, $previousException);

        $this->assertEquals($customMessage, $exception->getMessage());
        $this->assertEquals($customCode, $exception->getCode());
        $this->assertEquals($previousException, $exception->getPrevious());
        $this->assertEquals('前一个异常', $exception->getPrevious()->getMessage());
    }

    /**
     * 测试异常继承关系
     */
    #[Test]
    public function test_exception_inheritance()
    {
        $exception = new InsufficientPointsException;

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
