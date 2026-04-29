---
name: Laravel Admin CRUD Generator
description: 根据项目后台架构规范，快速生成完整的后台管理模块，包括模型、迁移、控制器、请求类、资源类、路由和菜单。
---

# Laravel Admin CRUD Generator

## Description
根据项目后台架构规范，快速生成完整的后台管理模块，包括模型、迁移、控制器、请求类、资源类、路由和菜单。

## When to use
- 需要为新的数据表创建后台管理功能时
- 需要生成完整的 CRUD 操作界面时
- 需要按照项目架构规范创建后台模块时
- 需要确保生成的代码符合项目编码标准时

## Instructions

### 1. 项目架构分析
- 项目使用 Laravel 12.x 框架
- 后台控制器继承自 `AbstractController`
- 使用 RESTful 资源路由
- 使用 Form Request 进行表单验证
- 使用 API Resource 进行数据转换
- 使用 Eloquent 模型和关联关系

### 2. 生成文件清单
对于给定的模型名称（如 `Product`），将生成以下文件：
- 模型：`app/Models/Product.php`
- 迁移：`database/migrations/YYYY_MM_DD_HHMMSS_create_products_table.php`
- 控制器：`app/Http/Controllers/Admin/ProductController.php`
- Store 请求：`app/Http/Requests/Admin/Product/StoreProductRequest.php`
- Update 请求：`app/Http/Requests/Admin/Product/UpdateProductRequest.php`
- 资源类：`app/Http/Resources/Admin/ProductResource.php`
- 路由配置（在 `routes/admin.php` 中添加）
- 菜单数据（可选）

### 3. 代码规范
- 遵循 PSR-12 编码标准
- 使用严格类型声明 `declare(strict_types=1);`
- 添加完整的 PHPDoc 注释
- 使用正确的命名空间和类名
- 控制器使用 `auth:admin` 中间件保护

### 4. 功能特性
生成的后台模块包含以下功能：
- 列表展示（支持分页、搜索、排序）
- 创建新记录
- 编辑记录
- 删除记录
- 状态切换（可选）
- 软删除支持（可选）

## Output Format

### 模型模板
```php
<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * [模型名称]
 *
 * @property int $id ID
 * @property string $name 名称
 * @property int $status 状态
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 更新时间
 * @property \Illuminate\Support\Carbon|null $deleted_at 删除时间
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class [ModelName] extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '[table_name]';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'status' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
```

### 控制器模板
```php
<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\[ModelName]\Store[ModelName]Request;
use App\Http\Requests\Admin\[ModelName]\Update[ModelName]Request;
use App\Http\Resources\Admin\[ModelName]Resource;
use App\Models\[ModelName];
use Illuminate\Http\Request;

/**
 * [模型名称]管理
 */
class [ModelName]Controller extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $query = [ModelName]::query();

            if ($request->filled('keyword')) {
                $query->where('name', 'like', '%'.$request->keyword.'%');
            }

            if ($request->filled('field') && $request->filled('order')) {
                $query->orderBy($request->field, $request->order);
            }

            $items = $query->orderByDesc('id')->paginate(per_page($request, 15));

            return [ModelName]Resource::collection($items);
        }

        return view('admin.[model_name].index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.[model_name].create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Store[ModelName]Request $request)
    {
        [ModelName]::create($request->validated());

        return $this->success(trans('system.create_success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit([ModelName] $[model_name])
    {
        return view('admin.[model_name].edit', [
            'item' => $[model_name],
            'update_url' => route('admin.[model_names].update', $[model_name]),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Update[ModelName]Request $request, [ModelName] $[model_name])
    {
        $[model_name]->update($request->validated());

        return $this->success(trans('system.update_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy([ModelName] $[model_name])
    {
        $[model_name]->delete();

        return $this->success(trans('system.delete_success'));
    }
}
```

### Store Request 模板
```php
<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Admin\[ModelName];

use Illuminate\Foundation\Http\FormRequest;

/**
 * 创建[模型名称]请求
 */
class Store[ModelName]Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'status' => 'required|integer',
        ];
    }
}
```

### Update Request 模板
```php
<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Admin\[ModelName];

use Illuminate\Foundation\Http\FormRequest;

/**
 * 更新[模型名称]请求
 */
class Update[ModelName]Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'status' => 'required|integer',
        ];
    }
}
```

### Resource 模板
```php
<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\[ModelName];
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * [模型名称]资源
 *
 * @mixin [ModelName]
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class [ModelName]Resource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'edit_url' => route('admin.[model_names].edit', $this->id),
            'update_url' => route('admin.[model_names].update', $this->id),
            'delete_url' => route('admin.[model_names].destroy', $this->id),
        ];
    }
}
```

### 迁移模板
```php
<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('[table_name]', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('名称');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('[table_name]');
    }
};
```

### 路由配置
在 `routes/admin.php` 中添加：
```php
// [模型名称]管理
Route::resource('[model_names]', \App\Http\Controllers\Admin\[ModelName]Controller::class, ['names' => '[model_names]']);
```

## Examples

### 示例：生成产品管理模块
假设要生成一个 `Product` 产品管理模块，包含以下字段：
- id (主键)
- name (产品名称)
- price (价格)
- stock (库存)
- status (状态)
- description (描述)
- created_at, updated_at, deleted_at

按照上述模板，将生成完整的产品管理后台功能。

### 生成步骤
1. 根据用户提供的字段信息，生成数据库迁移文件
2. 生成模型文件，包含 fillable 和 casts 属性
3. 生成 Store 和 Update 请求验证类
4. 生成 API Resource 资源类
5. 生成后台控制器，包含完整的 CRUD 方法
6. 在路由文件中添加资源路由
7. （可选）生成相应的视图文件
