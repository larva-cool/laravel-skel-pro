<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\User;

use App\Models\User\Nickname;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 昵称模型测试
 */
class NicknameTest extends TestCase
{
    /**
     * 测试模型基本配置
     */
    #[Test]
    #[TestDox('测试模型基本配置')]
    public function test_model_basic_configuration(): void
    {
        $nickname = new Nickname;

        // 测试表名
        $this->assertEquals('nicknames', $nickname->getTable());

        // 测试可填充字段
        $this->assertEquals(['nickname'], $nickname->getFillable());

        // 测试 created_at 字段被禁用
        $this->assertNull($nickname::CREATED_AT);
    }
}
