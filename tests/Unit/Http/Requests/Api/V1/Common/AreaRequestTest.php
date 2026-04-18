<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Api\V1\Common;

use App\Http\Requests\Api\V1\Common\AreaRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 地区请求验证测试
 */
#[CoversClass(AreaRequest::class)]
class AreaRequestTest extends TestCase
{
    /**
     * 测试验证规则
     */
    #[Test]
    #[TestDox('测试验证规则')]
    public function test_rules()
    {
        $request = new AreaRequest;
        $rules = $request->rules();

        // 验证规则数组结构
        $this->assertIsArray($rules);
        $this->assertArrayHasKey('id', $rules);

        // 验证 id 字段的规则
        $idRules = $rules['id'];
        $this->assertIsArray($idRules);
        $this->assertContains('nullable', $idRules);
        $this->assertContains('integer', $idRules);
    }

    /**
     * 测试规则包含 exists 验证
     */
    #[Test]
    #[TestDox('测试规则包含 exists 验证')]
    public function test_rules_contain_exists_validation()
    {
        $request = new AreaRequest;
        $rules = $request->rules();

        // 验证 id 字段的规则包含 exists 验证
        $idRules = $rules['id'];
        $this->assertIsArray($idRules);

        // 检查是否有 Rule::exists 实例
        $hasExistsRule = false;
        foreach ($idRules as $rule) {
            if (is_object($rule) && method_exists($rule, '__toString')) {
                $ruleString = (string) $rule;
                if (str_contains($ruleString, 'exists:areas,id')) {
                    $hasExistsRule = true;
                    break;
                }
            }
        }

        $this->assertTrue($hasExistsRule, 'Rules should contain exists validation for areas.id');
    }
}
