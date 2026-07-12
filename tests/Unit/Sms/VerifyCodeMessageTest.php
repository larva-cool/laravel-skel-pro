<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Sms;

use App\Services\SettingManagerService;
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
     * 设置 mock 的 SettingsManager
     */
    protected function mockSettings(string $aliyunTemplateId = 'SMS_157965369', string $volcengineTemplateId = 'ST_84db0ca7'): void
    {
        $settingManagerMock = $this->createMock(SettingManagerService::class);
        $settingManagerMock->method('get')
            ->willReturnMap([
                ['sms.aliyun_template_id', null, $aliyunTemplateId],
                ['sms.template_id', null, $volcengineTemplateId],
            ]);

        $this->app->instance(SettingManagerService::class, $settingManagerMock);
    }

    /**
     * 测试 getTemplate 方法
     */
    #[Test]
    #[TestDox('测试 getTemplate 方法')]
    public function test_get_template(): void
    {
        $this->mockSettings();

        // 创建 VerifyCodeMessage 实例
        $message = new VerifyCodeMessage;

        // 使用反射设置受保护的属性
        $reflection = new \ReflectionClass($message);
        $codeProperty = $reflection->getProperty('code');
        $codeProperty->setAccessible(true);
        $codeProperty->setValue($message, 123456);

        $sceneProperty = $reflection->getProperty('scene');
        $sceneProperty->setAccessible(true);
        $sceneProperty->setValue($message, 'default');

        // 测试 aliyun 网关 - 使用配置值
        $aliyunGateway = $this->createMock(GatewayInterface::class);
        $aliyunGateway->method('getName')->willReturn('aliyun');

        $template = $message->getTemplate($aliyunGateway);
        $this->assertEquals('SMS_157965369', $template);

        // 测试 volcengine 网关 - 使用配置值
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
