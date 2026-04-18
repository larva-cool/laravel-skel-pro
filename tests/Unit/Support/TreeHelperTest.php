<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\TreeHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(TreeHelper::class)]
#[TestDox('测试 TreeHelper 类的构造函数')]
class TreeHelperTest extends TestCase
{
    #[Test]
    #[TestDox('测试构造函数初始化时处理数组数据')]
    public function test_constructor_initializes_correctly_with_array_data()
    {
        // 测试数组数据
        $data = [
            ['id' => 1, 'parent_id' => 0, 'name' => 'Root'],
            ['id' => 2, 'parent_id' => 1, 'name' => 'Child 1'],
            ['id' => 3, 'parent_id' => 2, 'name' => 'Grandchild 1'],
        ];
        $treeHelper = new TreeHelper($data);
        $this->assertInstanceOf(TreeHelper::class, $treeHelper);

        // 测试对象数据
        $objectData = collect($data);
        $treeHelperFromObject = new TreeHelper($objectData);
        $this->assertInstanceOf(TreeHelper::class, $treeHelperFromObject);
    }

    #[Test]
    #[TestDox('测试 getDescendant 方法获取节点的子孙')]
    public function test_get_descendant()
    {
        $data = [
            ['id' => 1, 'parent_id' => 0, 'name' => 'Root'],
            ['id' => 2, 'parent_id' => 1, 'name' => 'Child 1'],
            ['id' => 3, 'parent_id' => 2, 'name' => 'Grandchild 1'],
            ['id' => 4, 'parent_id' => 1, 'name' => 'Child 2'],
            ['id' => 5, 'parent_id' => 4, 'name' => 'Grandchild 2'],
        ];
        $treeHelper = new TreeHelper($data);

        // 测试获取单个节点的子孙
        $descendants = $treeHelper->getDescendant([1]);
        $this->assertCount(4, $descendants);
        $this->assertContains(['id' => 2, 'parent_id' => 1, 'name' => 'Child 1'], $descendants);
        $this->assertContains(['id' => 3, 'parent_id' => 2, 'name' => 'Grandchild 1'], $descendants);
        $this->assertContains(['id' => 4, 'parent_id' => 1, 'name' => 'Child 2'], $descendants);
        $this->assertContains(['id' => 5, 'parent_id' => 4, 'name' => 'Grandchild 2'], $descendants);

        // 测试获取多个节点的子孙
        $descendants = $treeHelper->getDescendant([2, 4]);
        $this->assertCount(2, $descendants);
        $this->assertContains(['id' => 3, 'parent_id' => 2, 'name' => 'Grandchild 1'], $descendants);
        $this->assertContains(['id' => 5, 'parent_id' => 4, 'name' => 'Grandchild 2'], $descendants);

        // 测试包含自身
        $descendantsWithSelf = $treeHelper->getDescendant([2], true);
        $this->assertCount(2, $descendantsWithSelf);
        $this->assertContains(['id' => 2, 'parent_id' => 1, 'name' => 'Child 1'], $descendantsWithSelf);
        $this->assertContains(['id' => 3, 'parent_id' => 2, 'name' => 'Grandchild 1'], $descendantsWithSelf);

        // 测试不存在的节点
        $nonExistent = $treeHelper->getDescendant([999]);
        $this->assertEmpty($nonExistent);
    }

    #[Test]
    #[TestDox('测试 getTree 方法获取树结构时排除祖先节点')]
    public function test_get_tree_without_ancestors()
    {
        $data = [
            ['id' => 1, 'parent_id' => 0, 'name' => 'Root'],
            ['id' => 2, 'parent_id' => 1, 'name' => 'Child 1'],
            ['id' => 3, 'parent_id' => 2, 'name' => 'Grandchild 1'],
        ];
        $treeHelper = new TreeHelper($data);

        $tree = $treeHelper->getTree([2], TreeHelper::EXCLUDE_ANCESTORS);
        $this->assertCount(1, $tree);
        $this->assertEquals(2, $tree[0]['id']);
        $this->assertCount(1, $tree[0]['children']);
        $this->assertEquals(3, $tree[0]['children'][0]['id']);
    }

    #[Test]
    #[TestDox('测试 getTree 方法获取树结构时包含祖先节点')]
    public function test_get_tree_with_ancestors()
    {
        $data = [
            ['id' => 1, 'parent_id' => 0, 'name' => 'Root'],
            ['id' => 2, 'parent_id' => 1, 'name' => 'Child 1'],
            ['id' => 3, 'parent_id' => 2, 'name' => 'Grandchild 1'],
            ['id' => 4, 'parent_id' => 1, 'name' => 'Child 2'],
        ];
        $treeHelper = new TreeHelper($data);

        // 测试单个节点
        $tree = $treeHelper->getTree([3], TreeHelper::INCLUDE_ANCESTORS);
        $this->assertCount(1, $tree);
        $this->assertEquals(1, $tree[0]['id']);
        $this->assertCount(1, $tree[0]['children']);
        $this->assertEquals(2, $tree[0]['children'][0]['id']);
        $this->assertCount(1, $tree[0]['children'][0]['children']);
        $this->assertEquals(3, $tree[0]['children'][0]['children'][0]['id']);

        // 测试多个节点
        $tree = $treeHelper->getTree([3, 4], TreeHelper::INCLUDE_ANCESTORS);
        $this->assertCount(1, $tree);
        $this->assertEquals(1, $tree[0]['id']);
        $this->assertCount(2, $tree[0]['children']);
        $this->assertEquals(2, $tree[0]['children'][0]['id']);
        $this->assertEquals(4, $tree[0]['children'][1]['id']);
    }

    #[Test]
    #[TestDox('测试 arrayValues 方法对树形结构数组进行重新索引')]
    public function test_array_values()
    {
        // 测试简单数组 - 注意：arrayValues 方法对非树形结构的简单数组不进行重新索引
        $array = [1 => 'a', 2 => 'b'];
        $result = TreeHelper::arrayValues($array);
        $this->assertEquals($array, $result);

        // 测试树形结构的简单数组
        $simpleTree = [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
        ];
        $result = TreeHelper::arrayValues($simpleTree);
        $this->assertEquals($simpleTree, $result);

        // 测试树形结构
        $tree = [
            1 => [
                'id' => 1,
                'name' => 'Root',
                'children' => [
                    2 => ['id' => 2, 'name' => 'Child'],
                ],
            ],
        ];
        $result = TreeHelper::arrayValues($tree);
        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertCount(1, $result[0]['children']);
        $this->assertEquals(2, $result[0]['children'][0]['id']);
    }

    #[Test]
    #[TestDox('测试 TreeHelper 类的自定义父 ID 字段')]
    public function test_custom_parent_id_field()
    {
        $data = [
            ['id' => 1, 'pid' => 0, 'name' => 'Root'],
            ['id' => 2, 'pid' => 1, 'name' => 'Child 1'],
        ];
        $treeHelper = new TreeHelper($data, 'pid');

        $tree = $treeHelper->getTree([2], TreeHelper::INCLUDE_ANCESTORS);
        $this->assertCount(1, $tree);
        $this->assertEquals(1, $tree[0]['id']);
        $this->assertCount(1, $tree[0]['children']);
        $this->assertEquals(2, $tree[0]['children'][0]['id']);
    }
}
