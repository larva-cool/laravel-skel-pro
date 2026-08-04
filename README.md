# Laravel Skel Pro

基于 Laravel 13 的后台管理系统骨架，采用 DDD 架构并遵循 RESTful API 设计规范。

- 前端项目：[laravel-skel-admin](https://github.com/larva-cool/laravel-skel-pro)

## 技术栈

| 分层 | 技术 | 版本 |
|------|------|------|
| PHP | ^8.3 |
| 框架 | Laravel | 13.x |
| 认证 | Laravel Sanctum | 4.x |
| 权限 | spatie/laravel-permission | 8.x |
| 队列 | Laravel Horizon | 5.x |
| 实时通信 | Laravel Reverb / Laravel Echo | 1.x / 2.x |
| 应用服务器 | Laravel Octane | 2.x |
| 监控 | Laravel Pulse / Telescope | 1.x / 5.x |
| 全文搜索 | Laravel Scout | 11.x |
| 缓存 | Redis (predis) |
| 前端 | Tailwind CSS v4 + Vite 8.x |
| 测试 | PHPUnit | 12.x |
| 代码风格 | Laravel Pint | 1.x |

## 目录结构

```
app/
├── Enums/                  # 枚举类（AdminStatus、MenuType、UserStatus 等）
│   └── Traits/HasLabel.php
├── Events/                 # 事件（Admin/LoginSucceeded、User/LoginSucceeded）
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # 后台控制器（AuthController 等）
│   │   └── Api/            # API 控制器
│   ├── Requests/           # 表单请求验证
│   └── Resources/          # Eloquent API Resources
├── Models/
│   ├── Admin/              # 后台模型（Admin、AdminMenu）
│   ├── System/             # 系统模型（Area、Setting、MailCode、PhoneCode）
│   ├── User/               # 前台用户模型（LoginHistory）
│   ├── Sanctum/            # Sanctum 个人访问令牌
│   └── Traits/             # 模型 Trait
├── Providers/              # 服务提供者（App、Horizon、Telescope）
└── Services/               # 业务服务

config/                     # 配置文件
database/
├── factories/              # 模型工厂
├── migrations/             # 数据库迁移
└── seeders/                # 数据填充（AdminSeeder、AdminMenuSeeder）
routes/
├── web.php                 # Web 路由
├── admin.php               # 后台路由（前缀 /admin）
├── api_v1.php              # API v1 路由
├── api_v2.php              # API v2 路由
├── channels.php            # 广播频道
└── console.php             # 定时任务
lang/                       # 语言文件（zh_CN、en）
bootstrap/helpers.php       # 全局辅助函数
```

## 环境要求

- PHP >= 8.3（推荐 8.4+）
- PHP 扩展：`pdo`、`mbstring`、`openssl`、`redis`、`bcmath`、`ctype`、`json`、`fileinfo`、`tokenizer`、`xml`
- Composer >= 2.x
- Node.js >= 20.x + pnpm
- MySQL 8.0+ / PostgreSQL / SQLite
- Redis（推荐，用于缓存、队列、会话）

## 安装

### 方式一：一键安装（推荐）

```bash
composer run setup
```

该命令会依次执行：`composer install` → 复制 `.env` → `key:generate` → `migrate --force` → `storage:link` → `npm install` → `npm run build`。

### 方式二：手动安装

```bash
# 1. 安装依赖
composer install

# 2. 复制环境配置
cp .env.example .env

# 3. 生成密钥
php artisan key:generate

# 4. 配置 .env 中的数据库连接，然后执行迁移
php artisan migrate

# 5. 创建存储链接
php artisan storage:link

# 6. 安装前端依赖并编译
npm install
npm run build
```

### 本地开发

推荐使用 [Laravel Herd](https://herd.laravel.com/) 提供本地服务，访问地址为 `https://laravel-skel-pro.test`（项目目录 kebab-case 名 + `.test`）。

启动完整开发环境（包含 server、queue、pail 日志、Vite）：

```bash
composer run dev
```

## 可用命令

| 命令 | 说明 |
|------|------|
| `composer run setup` | 一键安装项目 |
| `composer run dev` | 启动开发环境（server/queue/logs/vite） |
| `composer run test` | 运行全部测试 |
| `composer run check-style` | 检查代码风格 |
| `composer run fix-style` | 修复代码风格 |
| `php artisan route:list` | 查看路由列表 |
| `php artisan route:list --path=admin` | 查看后台路由 |

## API 路由

### 认证接口（`/admin/auth`）

| 方法 | 路径 | 说明 | 认证 |
|------|------|------|------|
| POST | `/admin/auth/login` | 管理员登录 | 否 |
| POST | `/admin/auth/logout` | 退出登录 | 是 |
| GET | `/admin/auth/info` | 获取当前管理员信息 | 是 |

请求/响应示例：

**登录请求：**
```json
POST /admin/auth/login
{
  "account": "admin",
  "password": "123456"
}
```

**成功响应：**
```json
{
  "code": 200,
  "message": "登录成功",
  "data": {
    "token": "1|laravel_sanctum_token_xxx",
    "refreshToken": "1|laravel_sanctum_token_xxx"
  }
}
```

**验证失败响应（HTTP 422）：**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "password": ["用户名或密码错误。"]
  }
}
```

**未认证响应（HTTP 401）：**
```json
{
  "message": "登录已过期，请重新登录"
}
```

### 认证机制

后台管理员使用 **Sanctum Token** 认证，前端需在请求头携带：

```
Authorization: Bearer {token}
Accept: application/json
```

默认管理员账号：`admin` / `123456`（由 `AdminSeeder` 创建）。

### 统一响应格式

所有接口统一返回 JSON 格式：

```json
{
  "code": 200,       // 业务状态码：200 成功，400 业务错误，401 未认证
  "message": "...", // 提示消息
  "data": {}        // 业务数据
}
```

HTTP 状态码遵循 RESTful 规范：2xx 成功、4xx 客户端错误、5xx 服务器错误。表单验证错误返回 422，未认证返回 401。

## 认证与权限

- **双端认证模型**：
  - 前台用户（`User` 模型）：session + SPA 认证（guard: `web`）
  - 后台管理员（`Admin` 模型）：Sanctum Token 认证（guard: `admin`）
- **权限系统**：spatie/laravel-permission v8，支持角色（Role）和权限（Permission）
- **中间件别名**：`role`、`permission`、`role_or_permission`、`ability`、`abilities`
- **CSRF 豁免**：`/admin/*` 和 `/api/*` 路径不做 CSRF 校验
- **Sanctum Token 有效期**：默认 6 个月（262800 分钟）

## 数据库

迁移执行后会创建以下核心表：

| 表 | 说明 |
|----|------|
| `admins` | 后台管理员 |
| `admin_menus` | 后台菜单/按钮（树结构） |
| `users` | 前台用户 |
| `roles` / `permissions` | 角色/权限（spatie-permission） |
| `settings` | 系统设置 |
| `areas` | 地区数据 |
| `personal_access_tokens` | Sanctum Token |
| `sessions` / `cache` / `jobs` | 会话/缓存/队列 |
| `mail_codes` / `phone_codes` | 邮件/手机验证码 |
| `login_histories` | 登录历史 |
| `pulse_*` / `telescope_*` | 监控数据 |

初始数据由 `AdminSeeder` 和 `AdminMenuSeeder` 填充，包含超级管理员角色和完整的后台菜单结构。

## 全局辅助函数

定义在 `bootstrap/helpers.php` 中：

| 函数 | 说明 |
|------|------|
| `cpu_count(): int` | 获取 CPU 核心数 |
| `per_page(Request $request, int $limit = 15): int` | 获取分页大小（限制 1~100） |
| `mobile_replace(?string $value): string` | 手机号脱敏 |
| `settings(string $key, $default)` | 快捷读取系统设置 |
| `validation_exception($field, $message)` | 快速抛出表单验证异常 |

## 编码规范

- 遵循 PSR-12，使用 PHP 8 特性（构造器属性提升、命名参数、枚举、match 表达式）
- 所有 PHP 文件强制 `declare(strict_types=1)` 和文件头 License 注释
- **修改 PHP 文件后必须运行格式化**：
  ```bash
  vendor/bin/pint --dirty --format agent
  ```
- API 统一使用 Eloquent API Resources 和版本控制
- 使用 `php artisan make:` 命令创建文件，传递 `--no-interaction` 参数
- 数据库操作始终通过迁移（migration），字段变更使用索引优化
- 控制器建议最长 200 行，方法建议最长 20 行

## 测试

使用 PHPUnit 12，采用新属性语法（`#[Test]`、`#[CoversClass]`、`#[Group]`、`#[TestDox]`）。测试使用内存 SQLite（`:memory:`）。

```bash
# 运行全部测试
php artisan test --compact

# 运行单个文件
php artisan test --compact tests/Feature/Admin/AuthControllerTest.php

# 按方法名过滤
php artisan test --compact --filter=login_with_valid_credentials

# 运行测试并生成覆盖率
php artisan test --coverage
```

测试命名使用 `snake_case`，所有测试基类为 `Tests\TestCase`，Feature 测试使用 `RefreshDatabase` trait。

## 监控与调试

| 工具 | 用途 | 访问路径 |
|------|------|----------|
| Laravel Pulse | 应用性能监控 | `/pulse` |
| Laravel Telescope | 调试辅助（仅 local 环境） | `/telescope` |
| Laravel Horizon | 队列监控面板 | `/horizon` |
| Laravel Pail | 实时日志查看 | `php artisan pail` |

## 定时任务

在 `routes/console.php` 中定义：

- `auth:clear-resets` — 每日 01:00 清理过期密码重置令牌
- `model:prune` — 每日 01:05 清理已标记删除的模型

本地测试调度器：

```bash
php artisan schedule:work
```

生产环境使用 Cron：

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## 队列

默认使用 `database` 驱动，生产环境推荐切换到 Redis。通过 Horizon 管理队列进程：

```bash
php artisan horizon          # 启动 Horizon
php artisan horizon:pause    # 暂停
php artisan horizon:continue # 继续
```

## 前端构建

```bash
npm run dev    # 开发模式（热更新）
npm run build  # 生产构建
```

前端使用 Tailwind CSS v4（通过 `@tailwindcss/vite` 插件集成），入口文件为 `resources/css/app.css` 和 `resources/js/app.js`。

## 配置说明

关键环境变量（`.env`）：

| 变量 | 默认值 | 说明 |
|------|--------|------|
| `APP_NAME` | `Laravel` | 应用名称 |
| `APP_ENV` | `local` | 运行环境 |
| `APP_DEBUG` | `true` | 调试模式 |
| `APP_URL` | `http://localhost` | 应用 URL |
| `APP_LOCALE` | `zh_CN` | 默认语言 |
| `APP_TIMEZONE` | `Asia/Shanghai` | 默认时区 |
| `DB_CONNECTION` | `sqlite` | 数据库驱动 |
| `CACHE_STORE` | `database` | 缓存驱动（推荐 redis） |
| `QUEUE_CONNECTION` | `database` | 队列驱动（推荐 redis） |
| `SESSION_DRIVER` | `database` | Session 驱动（推荐 redis/redis） |
| `REDIS_CLIENT` | `phpredis` | Redis 客户端（可选 predis） |
| `BROADCAST_CONNECTION` | `log` | 广播驱动（生产用 reverb） |

## 安全

- 所有输入通过 Form Request 验证
- 使用 Eloquent ORM 预处理语句防止 SQL 注入
- 内置 CSRF 保护、XSS 防护
- Sanctum Token 认证
- 密码使用 bcrypt 加密（BCRYPT_ROUNDS=12）
- Telescope 在生产环境默认禁用
- 异常消息不暴露敏感字段

## 持续集成

项目配置了 GitHub Actions（`.github/workflows/tests.yml`）：
- 触发条件：push 到主分支、PR、每日定时执行
- 测试矩阵：PHP 8.3 / 8.4 / 8.5
- 运行全部测试并生成覆盖率报告

## License

MIT
