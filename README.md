# Laravel API 模板

[![Tests](https://github.com/larva-cool/laravel-skel/actions/workflows/tests.yml/badge.svg)](https://github.com/larva-cool/laravel-skel/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/larva/laravel-skel)](https://packagist.org/packages/larva/laravel-skel)
[![Latest Stable Version](https://img.shields.io/packagist/v/larva/laravel-skel)](https://packagist.org/packages/larva/laravel-skel)
![badge](https://cnb.cool/larva-cool/laravel-skel/-/badge/git/latest/code/vscode-started)
![badge](https://cnb.cool/larva-cool/laravel-skel/-/badge/git/latest/ci/pipeline-as-code)

这是一个基于 Laravel 12.x 的 API 模板，采用 DDD 结构，内置 Sanctum 授权机制和用户系统，适合快速构建后端 API 服务。

## 系统要求

- PHP >= 8.2
- MySQL >= 8.0
- Redis >= 6.0
- Composer >= 2.0

## 特点

### 架构设计
- 采用 DDD（领域驱动设计）架构
- 遵循 Laravel 最佳实践
- 高度完善的控制器、模型、模块模板
- 内置模型通用高阶 Traits 封装
- 自动注册 Policies

### 功能特性
- 完整的用户认证系统（基于 Laravel Sanctum）
- 强大的用户系统（包含用户组、用户资料、积分系统等）
- 社交功能支持（关注、点赞、评论）
- 文件管理系统
- 验证系统（邮件验证码、手机验证码）
- 多语言支持（zh_CN、en）
- 后台管理基础框架

### 开发工具
- Laravel Telescope（调试和监控）
- Laravel Pulse（性能监控）
- Laravel Pint（代码风格检查）
- PHPUnit（单元测试）
- Laravel Sail（Docker 开发环境）

## 安装

### 1. 创建项目

```bash
composer create-project larva/laravel-skel:dev-master -vv
```

### 2. 环境配置

```bash
# 创建配置文件
cp .env.develop .env

# 生成应用密钥
php artisan key:generate

# 配置数据库等相关信息
vim .env
```

### 3. 安装依赖与初始化

```bash
# 安装 Composer 依赖
composer install

# 安装 NPM 依赖（如果需要前端资源）
npm install

# 运行数据库迁移和填充数据
php artisan migrate --seed

# 创建存储软链接
php artisan storage:link
```

## 目录结构

```
app/
├── Events/          # 事件类
├── Exceptions/      # 异常处理类
├── Http/           
│   ├── Controllers/ # 控制器
│   ├── Middleware/  # 中间件
│   ├── Requests/    # 表单请求验证
│   └── Resources/   # API 资源
├── Models/          # 数据模型
│   └── Traits/      # 模型 Traits
├── Policies/        # 授权策略
├── Providers/       # 服务提供者
├── Services/        # 服务层
└── Support/         # 辅助功能
```

## 核心功能

### 用户系统
- 用户注册、登录、找回密码
- 用户组管理
- 用户资料管理
- 积分系统
- 社交功能（关注、点赞、评论）

### 认证授权
- 基于 Sanctum 的 API 认证
- 完整的授权策略（Policies）
- 角色权限管理

### 系统功能
- 文件上传和管理
- 验证码系统（邮件、短信）
- 系统设置
- 多语言支持

## 开发工具使用

### 代码规范检查
```bash
./vendor/bin/pint --test
```

### 运行测试
```bash
./vendor/bin/phpunit
```

### 使用 Sail（Docker）
```bash
# 启动开发环境
./vendor/bin/sail up -d

# 运行命令（例如：artisan）
./vendor/bin/sail artisan
```

### Docker 部署
```bash
# 构建 Docker 镜像
docker build --build-arg ENV=develop -t laravel_skel .

# 运行容器
docker run -p 8787:8787 -d laravel_skel
```

## 监控与调试

### Telescope
- 访问路径：`/telescope`
- 用途：查看请求、命令、队列等调试信息
- 仅在开发环境中启用

### Pulse
- 访问路径：`/pulse`
- 用途：实时监控应用性能、服务器状态
- 可配置在生产环境使用

## 贡献

欢迎提交 Issue 和 Pull Request。

## 许可证

本项目基于 MIT 协议开源。
