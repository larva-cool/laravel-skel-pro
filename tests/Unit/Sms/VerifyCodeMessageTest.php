<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Sms;

use App\Sms\VerifyCodeMessage;
use Overtrue\EasySms\Contracts\GatewayInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 短信验证码消息测试
 */
#[CoversClass(VerifyCodeMessage::class)]
class VerifyCodeMessageTest extends TestCase
{
    /**
     * 测试 getTemplate 方法
     */
    #[Test]
    #[TestDox('测试 getTemplate 方法')]
    public function test_get_template(): void
    {
        // 创建 VerifyCodeMessage 实例
        $message = new VerifyCodeMessage;

        // 使用反射设置受保护的属性
        $reflection = new \ReflectionClass($message);
        $codeProperty = $reflection->getProperty('code');
        $codeProperty->setAccessible(true);
        $codeProperty->setValue($message, 123456);

        $sceneProperty = $reflection->getProperty('scene');
        $sceneProperty->setAccessible(true);
        $sceneProperty->setValue($message, 'register');

        // 测试 aliyun 网关 - 注册场景
        $aliyunGateway = $this->createMock(GatewayInterface::class);
        $aliyunGateway->method('getName')->willReturn('aliyun');

        $template = $message->getTemplate($aliyunGateway);
        $this->assertEquals('SMS_157965369', $template);

        // 测试 aliyun 网关 - 默认场景
        $sceneProperty->setValue($message, 'unknown');
        $template = $message->getTemplate($aliyunGateway);
        $this->assertEquals('SMS_176526437', $template);

        // 测试 volcengine 网关
        $volcengineGateway = $this->createMock(GatewayInterface::class);
        $volcengineGateway->method('getName')->willReturn('volcengine');

        $template = $message->getTemplate($volcengineGateway);
        $this->assertEquals('ST_84db0ca7', $template);
    }

    /**
     * 测试 getData 方法
     */
    #[Test]
    #[TestDox('测试 getData 方法')]
    public function test_get_data(): void
    {
        // 创建 VerifyCodeMessage 实例
        $message = new VerifyCodeMessage;

        // 使用反射设置受保护的属性
        $reflection = new \ReflectionClass($message);
        $codeProperty = $reflection->getProperty('code');
        $codeProperty->setAccessible(true);
        $codeProperty->setValue($message, 123456);

        // 测试 qcloud 网关
        $qcloudGateway = $this->createMock(GatewayInterface::class);
        $qcloudGateway->method('getName')->willReturn('qcloud');

        $data = $message->getData($qcloudGateway);
        $this->assertEquals([123456], $data);

        // 测试 aliyun 网关
        $aliyunGateway = $this->createMock(GatewayInterface::class);
        $aliyunGateway->method('getName')->willReturn('aliyun');

        $data = $message->getData($aliyunGateway);
        $this->assertEquals(['code' => 123456], $data);

        // 测试 volcengine 网关
        $volcengineGateway = $this->createMock(GatewayInterface::class);
        $volcengineGateway->method('getName')->willReturn('volcengine');

        $data = $message->getData($volcengineGateway);
        $this->assertEquals(['code' => 123456], $data);

        // 测试未知网关
        $unknownGateway = $this->createMock(GatewayInterface::class);
        $unknownGateway->method('getName')->willReturn('unknown');

        $data = $message->getData($unknownGateway);
        $this->assertEquals([], $data);
    }

    /**
     * 测试 getContent 方法
     */
    #[Test]
    #[TestDox('测试 getContent 方法')]
    public function test_get_content(): void
    {
        // 创建 VerifyCodeMessage 实例
        $message = new VerifyCodeMessage;

        // 使用反射设置受保护的属性
        $reflection = new \ReflectionClass($message);
        $codeProperty = $reflection->getProperty('code');
        $codeProperty->setAccessible(true);
        $codeProperty->setValue($message, 123456);

        // 测试 qcloud 网关
        $qcloudGateway = $this->createMock(GatewayInterface::class);
        $qcloudGateway->method('getName')->willReturn('qcloud');

        $content = $message->getContent($qcloudGateway);
        $this->assertEquals('您的验证码为：123456，该验证码5分钟内有效，请勿泄漏于他人！', $content);

        // 测试 aliyun 网关
        $aliyunGateway = $this->createMock(GatewayInterface::class);
        $aliyunGateway->method('getName')->willReturn('aliyun');

        $content = $message->getContent($aliyunGateway);
        $this->assertEquals('验证码：123456，如非本人操作，请忽略此短信。', $content);

        // 测试 volcengine 网关
        $volcengineGateway = $this->createMock(GatewayInterface::class);
        $volcengineGateway->method('getName')->willReturn('volcengine');

        $content = $message->getContent($volcengineGateway);
        $this->assertEquals('您的验证码是123456，有效期为10分钟，请尽快验证。', $content);

        // 测试未知网关
        $unknownGateway = $this->createMock(GatewayInterface::class);
        $unknownGateway->method('getName')->willReturn('unknown');

        $content = $message->getContent($unknownGateway);
        $this->assertEquals('', $content);
    }

    /**
     * 测试 getGateways 方法
     */
    #[Test]
    #[TestDox('测试 getGateways 方法')]
    public function test_get_gateways(): void
    {
        // 创建 VerifyCodeMessage 实例
        $message = new VerifyCodeMessage;

        // 使用反射获取受保护的属性
        $reflection = new \ReflectionClass($message);
        $gatewaysProperty = $reflection->getProperty('gateways');
        $gatewaysProperty->setAccessible(true);
        $gateways = $gatewaysProperty->getValue($message);

        $this->assertEquals(['volcengine'], $gateways);
    }
}
