<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\System;

use App\Models\System\Area;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(Area::class)]
#[TestDox('Area 测试')]
class AreaTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestDox('测试可填充属性')]
    public function test_fillable_attributes()
    {
        $area = new Area;
        $fillable = $area->getFillable();

        $this->assertEqualsCanonicalizing([
            'parent_id', 'name', 'child_ids', 'area_code', 'lat', 'lng', 'city_code', 'order',
        ], $fillable);
    }

    #[Test]
    #[TestDox('测试属性类型转换')]
    public function test_attribute_casts()
    {
        $area = new Area;
        $casts = $area->getCasts();
        $dd = [
            'id' => 'integer',
            'parent_id' => 'integer',
            'name' => 'string',
            'area_code' => 'integer',
            'lat' => 'float',
            'lng' => 'float',
            'city_code' => 'string',
            'child_ids' => 'string',
            'order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
        sort($casts);
        sort($dd);
        $this->assertSame($dd, $casts);
    }

    #[Test]
    #[TestDox('测试父级关系')]
    public function test_parent_relation()
    {
        $parent = Area::factory()->create();
        $child = Area::factory()->child($parent->id)->create();

        $this->assertInstanceOf(Area::class, $child->parent);
        $this->assertEquals($parent->id, $child->parent->id);
    }

    #[Test]
    #[TestDox('测试子级关系')]
    public function test_children_relation()
    {
        $parent = Area::factory()->create();
        $child1 = Area::factory()->child($parent->id)->create();
        $child2 = Area::factory()->child($parent->id)->create();

        $this->assertInstanceOf(Collection::class, $parent->children);
        $this->assertCount(2, $parent->children);
        $this->assertTrue($parent->children->contains($child1));
        $this->assertTrue($parent->children->contains($child2));
    }

    #[Test]
    #[TestDox('测试获取子级ID列表')]
    public function test_get_children_ids()
    {
        $parent = Area::factory()->create();
        $child1 = Area::factory()->child($parent->id)->create();
        $child2 = Area::factory()->child($parent->id)->create();

        $childrenIds = $parent->getChildrenIds();

        $this->assertIsArray($childrenIds);
        $this->assertCount(2, $childrenIds);
        $this->assertContains($child1->id, $childrenIds);
        $this->assertContains($child2->id, $childrenIds);
    }

    #[Test]
    #[TestDox('测试静态获取子级ID列表')]
    public function test_get_child_ids_static()
    {
        $parent = Area::factory()->create();
        $child1 = Area::factory()->child($parent->id)->create();
        $child2 = Area::factory()->child($parent->id)->create();

        $childIds = Area::getChildIds($parent->id);

        $this->assertIsString($childIds);
        // 对ID进行排序后再比较，不依赖插入顺序
        $expectedIds = [$child1->id, $child2->id];
        sort($expectedIds);
        $actualIds = explode(',', $childIds);
        sort($actualIds);
        $this->assertEquals(implode(',', $expectedIds), implode(',', $actualIds));
    }

    #[Test]
    #[TestDox('测试获取地区列表')]
    public function test_get_areas()
    {
        $parent = Area::factory()->create();
        $child1 = Area::factory()->child($parent->id)->create();
        $child2 = Area::factory()->child($parent->id)->create();
        $unrelated = Area::factory()->create(); // 无关联的地区

        // 测试获取指定父地区的子地区
        $areas = Area::getAreas($parent->id);
        $this->assertInstanceOf(Collection::class, $areas);
        $this->assertCount(2, $areas);
        $this->assertTrue($areas->contains($child1));
        $this->assertTrue($areas->contains($child2));
        $this->assertFalse($areas->contains($unrelated));

        // 测试获取顶级地区
        $topAreas = Area::getAreas(null);
        $this->assertInstanceOf(Collection::class, $topAreas);
        $this->assertCount(2, $topAreas); // $parent和$unrelated都是顶级地区
        $this->assertTrue($topAreas->contains($parent));
        $this->assertTrue($topAreas->contains($unrelated));

        // 测试自定义字段
        $customAreas = Area::getAreas($parent->id, ['id', 'name', 'order']);
        $this->assertInstanceOf(Collection::class, $customAreas);
        $this->assertCount(2, $customAreas);

        // 检查返回的模型是否只包含指定的字段
        $firstArea = $customAreas->first();
        $attributes = array_keys($firstArea->getAttributes());
        $this->assertEqualsCanonicalizing(['id', 'name', 'order'], $attributes);
    }

    #[Test]
    #[TestDox('测试根据ID获取地区名称')]
    public function test_get_name_by_id()
    {
        $area = Area::factory()->create(['name' => '测试地区']);

        $name = Area::getNameById($area->id);
        $this->assertEquals('测试地区', $name);

        // 测试不存在的ID
        $nonExistentName = Area::getNameById(999999);
        $this->assertNull($nonExistentName);
    }

    #[Test]
    #[TestDox('测试获取地区树结构')]
    public function test_get_tree_for_xm_select()
    {
        // 创建测试数据
        $level1 = Area::factory()->create(['name' => '一级地区']);
        $level2_1 = Area::factory()->child($level1->id)->create(['name' => '二级地区1']);
        $level2_2 = Area::factory()->child($level1->id)->create(['name' => '二级地区2']);
        $level3 = Area::factory()->child($level2_1->id)->create(['name' => '三级地区']);

        // 测试默认参数
        $tree = Area::getTreeForXmSelect(null);
        $this->assertIsArray($tree);
        $this->assertCount(1, $tree);
        $this->assertEquals('一级地区', $tree[0]['name']);
        $this->assertEquals($level1->id, $tree[0]['value']);
        $this->assertArrayHasKey('children', $tree[0]);
        $this->assertCount(2, $tree[0]['children']);

        // 检查是否包含两个二级地区，不依赖顺序
        $childNames = collect($tree[0]['children'])->pluck('name')->all();
        $this->assertContains('二级地区1', $childNames);
        $this->assertContains('二级地区2', $childNames);

        // 找到二级地区1并检查其子地区
        $level2_1_node = collect($tree[0]['children'])->first(function ($node) {
            return $node['name'] === '二级地区1';
        });
        $this->assertNotNull($level2_1_node);
        $this->assertArrayHasKey('children', $level2_1_node);
        $this->assertCount(1, $level2_1_node['children']);
        $this->assertEquals('三级地区', $level2_1_node['children'][0]['name']);

        // 测试selectedValues选项 - 选择第一个二级地区
        $selectedTree = Area::getTreeForXmSelect(null, ['selectedValues' => [$level2_1->id]]);
        $this->assertIsArray($selectedTree);
        $this->assertCount(1, $selectedTree);
        $this->assertFalse($selectedTree[0]['selected']);

        // 查找第一个二级地区的节点
        $level2_1_node = collect($selectedTree[0]['children'])->first(function ($node) use ($level2_1) {
            return $node['value'] === $level2_1->id;
        });
        $this->assertNotNull($level2_1_node);
        $this->assertTrue($level2_1_node['selected']);

        // 测试selectedValues选项 - 选择第二个二级地区
        $selectedTree2 = Area::getTreeForXmSelect(null, ['selectedValues' => [$level2_2->id]]);
        $this->assertIsArray($selectedTree2);
        $this->assertCount(1, $selectedTree2);
        $this->assertFalse($selectedTree2[0]['selected']);

        // 查找第二个二级地区的节点
        $level2_2_node = collect($selectedTree2[0]['children'])->first(function ($node) use ($level2_2) {
            return $node['value'] === $level2_2->id;
        });
        $this->assertNotNull($level2_2_node);
        $this->assertTrue($level2_2_node['selected']);
    }

    #[Test]
    #[TestDox('测试软删除地区')]
    public function test_soft_delete()
    {
        $area = Area::factory()->create();

        // 测试删除
        $area->delete();
        $this->assertSoftDeleted($area);

        // 测试恢复
        $area->restore();
        $this->assertNotSoftDeleted($area);

        // 测试强制删除
        $area->forceDelete();
        $this->assertModelMissing($area);
    }

    #[Test]
    #[TestDox('测试在保存时更新父级子级ID')]
    public function test_update_parent_child_ids_on_save()
    {
        $parent = Area::factory()->create(['child_ids' => '']);
        $child1 = Area::factory()->child($parent->id)->create();

        // 刷新父地区模型
        $parent->refresh();
        $this->assertEquals($child1->id, $parent->child_ids);

        // 添加第二个子地区
        $child2 = Area::factory()->child($parent->id)->create();
        $parent->refresh();

        // 对ID进行排序后再比较，不依赖插入顺序
        $expectedIds = [$child1->id, $child2->id];
        sort($expectedIds);
        $actualIds = explode(',', $parent->child_ids);
        sort($actualIds);

        $this->assertEquals(implode(',', $expectedIds), implode(',', $actualIds));

        // 强制删除一个子地区
        $child1->forceDelete();
        $parent->refresh();
        $this->assertEquals($child2->id, $parent->child_ids);
    }
}
