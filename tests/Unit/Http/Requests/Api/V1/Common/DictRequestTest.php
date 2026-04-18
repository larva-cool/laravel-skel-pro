<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Api\V1\Common;

use App\Http\Requests\Api\V1\Common\DictRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 字典请求验证测试
 */
#[CoversClass(DictRequest::class)]
class DictRequestTest extends TestCase
{
    /**
     * 测试验证规则
     */
    #[Test]
    #[TestDox('测试验证规则')]
    public function test_rules()
    {
        $request = new DictRequest;
        $rules = $request->rules();

        // 验证规则数组结构
        $this->assertIsArray($rules);
        $this->assertArrayHasKey('type', $rules);

        // 验证 type 字段的规则
        $typeRules = $rules['type'];
        $this->assertIsArray($typeRules);
        $this->assertContains('required', $typeRules);
        $this->assertContains('string', $typeRules);
    }

    /**
     * 测试规则包含 exists 验证
     */
    #[Test]
    #[TestDox('测试规则包含 exists 验证')]
    public function test_rules_contain_exists_validation()
    {
        $request = new DictRequest;
        $rules = $request->rules();

        // 验证 type 字段的规则包含 exists 验证
        $typeRules = $rules['type'];
        $this->assertIsArray($typeRules);

        // 检查是否有 Rule::exists 实例
        $hasExistsRule = false;
        foreach ($typeRules as $rule) {
            if (is_object($rule) && method_exists($rule, '__toString')) {
                $ruleString = (string) $rule;
                if (str_contains($ruleString, 'exists:dicts,code')) {
                    $hasExistsRule = true;
                    break;
                }
            }
        }

        $this->assertTrue($hasExistsRule, 'Rules should contain exists validation for dicts.code');
    }
}
