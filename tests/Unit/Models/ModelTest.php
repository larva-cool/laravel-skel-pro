<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Model;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * Model 基类测试
 */
#[TestDox('Model 基类测试')]
class ModelTest extends TestCase
{
    /**
     * 测试模型实例化
     */
    #[Test]
    #[TestDox('测试模型实例化')]
    public function test_model_instantiation()
    {
        $model = new class extends Model
        {
            protected $table = 'test_table';
        };

        $this->assertInstanceOf(Model::class, $model);
    }

    /**
     * 测试模型是否使用了正确的 traits
     */
    #[Test]
    #[TestDox('测试模型是否使用了正确的 traits')]
    public function test_model_uses_correct_traits()
    {
        $model = new class extends Model
        {
            protected $table = 'test_table';
        };

        $usedTraits = array_keys(class_uses_recursive(get_class($model)));

        $this->assertContains('App\Models\Traits\DateTimeFormatter', $usedTraits);
    }
}
