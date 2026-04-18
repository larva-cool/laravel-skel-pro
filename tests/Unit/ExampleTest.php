<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('Example 单元测试')]
class ExampleTest extends TestCase
{
    #[Test]
    #[TestDox('测试 true 等于 true')]
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }
}
