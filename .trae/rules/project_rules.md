# Trae AI 项目规则文件

## 项目概述
本项目基于 Laravel 12.x 构建，采用 DDD 架构并遵循 RESTful API 设计规范。项目结构清晰，模块化程度高，符合 Laravel 最佳实践。

## 技术栈
- **框架**：Laravel 12.x
- **PHP 版本**：8.2+
- **后台**：Filament 5.x
- **前台前端**：Tailwind CSS（默认 CSS 框架）
- **数据库**：支持多种数据库（MySQL、MongoDB）
- **缓存**：Redis
- **队列**：Laravel Horizon
- **测试**：PHPUnit 12.x
- **代码质量**：PHP_CodeSniffer

## 编码时需遵循的重要规则
- 始终完成需求文档中的所有任务

## 编码标准和规范

### 1. PHP 编码标准
- 遵循 PSR-12 编码标准
- 为所有方法使用类型提示和返回类型
- 使用 PHPDoc 维护适当的方法和类文档
- 建议方法最大长度为 20 行
- 建议类最大长度为 200 行

### 2. Laravel 最佳实践
- 使用 Laravel 内置的安全功能（CSRF、XSS 保护）
- 使用 Laravel 内置的表单请求进行验证
- 使用 Laravel 的查询构建器或 Eloquent ORM 进行数据库操作
- 利用 Laravel 内置的缓存机制
- 遵循 RESTFul 规范设计 API 端点

### 3. 数据库
- 对所有数据库更改使用迁移
- 编写有意义的迁移名称
- 使用种子器生成测试数据
- 对频繁查询的列创建索引

### 4. 安全指南
- 将敏感数据存储在 `.env` 文件中
- 使用 Laravel 的认证系统
- 实现适当的输入验证
- 使用预编译语句进行查询
- 启用 CORS 保护
- 对 API 实现速率限制
- 使用 Laravel 的 CSP（内容安全策略）
- 定期进行安全更新和依赖检查

### 5. 前端开发
- 使用 Blade 模板引擎
- 避免在视图中混合 PHP 和 HTML
- 使用 Tailwind CSS 类进行样式设计
- 将 JavaScript 放在单独的文件中
- 尽量减少内联脚本
- 使用 Vite 进行资产编译
- 确保所有脚本块包含 CSP

### 6. 测试
- 为所有新功能编写单元测试
- 保持至少 70% 的代码覆盖率
- 测试所有 API 端点
- 使用工厂生成测试数据
- 为关键路径编写功能测试
- 对模型的测试尽量使用`RefreshDatabase` 特性，确保每次测试运行时数据库状态都是干净的。
- 所有测试方法命名均使用 `snake_case` 格式
- 所有测试统一采用 PHPUnit 12 引入的新属性语法进行配置与标注。
  - 始终使用`Tests\TestCase` 作为测试基类
  - `#[Test]` 用于标记测试方法，确保引入`PHPUnit\Framework\Attributes\Test` 类
  - `#[CoversClass(ClassName::class)]` 用于指定测试的类，确保引入`PHPUnit\Framework\Attributes\CoversClass` 类
  - `#[DataProvider('dataProviderMethod')]` 用于指定数据提供方法，确保引入`PHPUnit\Framework\Attributes\DataProvider` 类
  - `#[Depends('testMethod')]` 用于指定依赖的测试方法，确保引入`PHPUnit\Framework\Attributes\Depends` 类
  - `#[Group('groupName')]` 用于将测试分组，确保引入`PHPUnit\Framework\Attributes\Group` 类
  - `#[TestDox('Test Description')]` 用于为测试方法添加描述, 确保引入`PHPUnit\Framework\Attributes\TestDox` 类，所有测试方法都应包含此属性。
- 测试应针对 `.env.testing` 中定义的现有数据库进行。如果该文件不存在，则测试失败。

### 7. Git 工作流程
- 使用功能分支进行开发
- 编写有意义的提交消息
- 保持提交原子性和针对性
- 拉取请求必须通过 CI/CD 检查
- 定期与主分支进行变基操作

### 8. 性能指南
- 使用预加载以防止 N+1 查询
- 在适当的地方实现缓存
- 使用队列处理长时间运行的任务
- 优化数据库查询
- 对大型数据集使用分页加载
- 避免在视图中执行复杂的逻辑

### 9. 文档
- 记录所有 API 端点
- 保持 `README.md` 更新
- 记录复杂的业务逻辑
- 包含设置说明
- 记录环境要求

### 10. 错误处理
- 适当地使用 try-catch 块
- 正确记录错误
- 返回适当的 HTTP 状态码
- 实现适当的异常处理
- 需要时使用自定义异常类

## 项目结构
- `app/` - 应用程序核心代码
- `config/` - 配置文件
- `database/` - 迁移和种子器
- `resources/` - 视图和资产
- `routes/` - 路由定义
- `tests/` - 测试文件
- `storage/` - 日志和上传文件
- `packages/` - 自定义包和模块

## 开发设置
1. 将 `.env.develop` 复制为 `.env`
2. 安装依赖：`composer install`
3. 生成密钥：`php artisan key:generate`
4. 运行迁移：`php artisan migrate`
5. 安装前端依赖：`npm install`
6. 编译资产：`npm run dev`

## 部署指南
- 使用 CI/CD 管道（Jenkins）
- 部署前运行所有测试
- 检查安全漏洞
- 迁移数据库前备份数据库
- 使用适当的部署环境变量
- 遵循零停机部署实践

## 监控和维护
- 使用 Laravel Horizon 进行队列监控
- 定期审查和清理日志
- 进行数据库优化和维护
- 定期更新依赖
- 进行性能监控和优化

请记住遵循这些指南，以保持项目代码的质量和一致性。如有任何疑问或需要澄清，请咨询团队负责人或资深开发人员。
