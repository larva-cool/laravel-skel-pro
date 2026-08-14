# 后台 API 响应约定（前端对接文档）

## 1. 接口基础信息

| 项 | 说明 |
|---|---|
| 接口前缀 | `/admin` |
| 数据格式 | `application/json` |
| 认证方式 | Cookie + Session（基于 Sanctum stateful API） |
| CSRF 防护 | 请求需携带 `X-XSRF-TOKEN` Header（从 Cookie 中读取 `XSRF-TOKEN`） |

> 登录前需先调用 `/sanctum/csrf-cookie` 获取 CSRF Cookie。

---

## 2. 统一响应结构

所有接口的响应体均采用以下统一结构：

```json
{
  "code": 200,
  "message": "success",
  "data": {}
}
```

| 字段 | 类型 | 说明 |
|---|---|---|
| `code` | `number` | 业务状态码，`200` 表示成功，非 `200` 表示业务错误 |
| `message` | `string` | 响应消息，成功或错误的描述文本 |
| `data` | `any` | 响应数据，可能是对象、数组、`null` |

### 2.1 成功响应

```json
{
  "code": 200,
  "message": "操作成功",
  "data": { ... }
}
```

- HTTP 状态码：`200`
- `code` 字段值：`200`
- `data` 为具体业务数据

### 2.2 业务错误响应

```json
{
  "code": 400,
  "message": "错误描述信息",
  "data": null
}
```

- HTTP 状态码：**始终为 `200`**（业务错误通过 body 中的 `code` 字段区分）
- `code` 为具体业务错误码（如 `400`、`401`、`403`、`404`、`422` 等）
- `message` 为错误描述，可直接展示给用户
- `data` 通常为 `null`，部分场景会携带附加数据

### 2.3 业务状态码约定

| code | 含义 | 触发场景 |
|---|---|---|
| `200` | 成功 | 正常响应 |
| `400` | 请求参数错误 | 通用业务错误 |
| `401` | 未登录 | 登录态失效或未登录 |
| `403` | 无权限 | 权限不足、禁止操作（如删除自己、删除超级管理员） |
| `404` | 资源不存在 | 请求的资源 ID 不存在 |
| `422` | 数据验证失败 | 表单字段校验不通过，`data` 字段携带字段错误详情 |

---

## 3. 验证错误（422）响应

当表单验证失败时，响应结构如下：

```json
{
  "code": 422,
  "message": "The username field is required. (and 2 more errors)",
  "data": {
    "errors": {
      "username": ["用户名为必填项"],
      "password": ["密码至少需要 8 个字符"]
    }
  }
}
```

- `data.errors` 为对象，key 为字段名，value 为错误消息数组
- 每个字段可能有多个错误消息，取第一条展示即可

---

## 4. 分页响应

列表类接口返回分页数据，结构如下：

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "data": [
      { "id": 1, "username": "admin", ... }
    ],
    "links": {
      "first": "https://xxx/admin/admins?page=1",
      "last": "https://xxx/admin/admins?page=10",
      "prev": null,
      "next": "https://xxx/admin/admins?page=2"
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 10,
      "path": "https://xxx/admin/admins",
      "per_page": 15,
      "to": 15,
      "total": 150
    }
  }
}
```

### 4.1 分页请求参数

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `page` | `number` | `1` | 当前页码 |
| `per_page` | `number` | `15` | 每页条数，范围 `1 ~ 100` |

### 4.2 分页响应关键字段

| 路径 | 类型 | 说明 |
|---|---|---|
| `data.data` | `array` | 当前页数据列表 |
| `data.meta.current_page` | `number` | 当前页码 |
| `data.meta.last_page` | `number` | 总页数 |
| `data.meta.per_page` | `number` | 每页条数 |
| `data.meta.total` | `number` | 总记录数 |
| `data.links.prev` | `string \| null` | 上一页链接 |
| `data.links.next` | `string \| null` | 下一页链接 |

---

## 5. 资源数据格式约定

### 5.1 时间字段

所有时间字段均为 ISO 8601 格式的字符串（使用 `toDateTimeString()`）：

```json
{
  "created_at": "2024-01-15 10:30:00",
  "updated_at": "2024-01-16 14:20:00",
  "last_login_at": "2024-01-20 09:00:00"
}
```

> 格式：`YYYY-MM-DD HH:mm:ss`，时区由服务器配置决定。

### 5.2 空值处理

- 可选字段为 `null` 时，**原样返回 `null`**（不会返回空字符串）
- 时间字段为空时返回 `null`
- 关联数据未加载时不返回该字段（使用 `whenLoaded` 条件输出）

### 5.3 树形结构

菜单等树形数据使用 `children` 字段嵌套：

```json
{
  "id": 1,
  "name": "dashboard",
  "title": "仪表盘",
  "children": [
    { "id": 2, "name": "analysis", "title": "分析页", "children": [] }
  ]
}
```

---

## 6. 典型接口示例

### 6.1 登录成功

**POST** `/admin/auth/login`

```json
{
  "code": 200,
  "message": "登录成功",
  "data": null
}
```

### 6.2 获取当前登录用户信息

**GET** `/admin/auth/info`

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "user_id": 1,
    "user_name": "admin",
    "email": "admin@example.com",
    "avatar": "",
    "roles": ["super_admin"],
    "buttons": ["admin.list", "admin.create", "..."]
  }
}
```

| 字段 | 类型 | 说明 |
|---|---|---|
| `user_id` | `number` | 用户 ID |
| `user_name` | `string` | 用户名 |
| `email` | `string` | 邮箱 |
| `avatar` | `string` | 头像地址（暂为空字符串） |
| `roles` | `string[]` | 角色标识数组 |
| `buttons` | `string[]` | 全部权限标识数组（含通过角色继承的） |

### 6.3 管理员列表（分页）

**GET** `/admin/admins?page=1&per_page=15`

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "data": [
      {
        "id": 1,
        "username": "admin",
        "email": "admin@example.com",
        "phone": "13800138000",
        "name": "超级管理员",
        "status": 1,
        "login_count": 120,
        "last_login_ip": "127.0.0.1",
        "last_login_at": "2024-01-20 09:00:00",
        "last_active_at": "2024-01-20 10:30:00",
        "roles": ["super_admin"],
        "created_at": "2024-01-01 00:00:00",
        "updated_at": "2024-01-20 09:00:00"
      }
    ],
    "links": { ... },
    "meta": { "current_page": 1, "last_page": 2, "per_page": 15, "total": 23, ... }
  }
}
```

### 6.4 创建管理员成功

**POST** `/admin/admins`

```json
{
  "code": 200,
  "message": "创建成功",
  "data": {
    "id": 2,
    "username": "editor",
    "email": "editor@example.com",
    "phone": "",
    "name": "编辑员",
    "status": 1,
    "login_count": 0,
    "last_login_ip": null,
    "last_login_at": null,
    "last_active_at": null,
    "roles": ["editor"],
    "created_at": "2024-01-20 12:00:00",
    "updated_at": "2024-01-20 12:00:00"
  }
}
```

### 6.5 删除成功

**DELETE** `/admin/admins/2`

```json
{
  "code": 200,
  "message": "删除成功",
  "data": null
}
```

### 6.6 业务错误（禁止删除自己）

**DELETE** `/admin/admins/1`

```json
{
  "code": 403,
  "message": "不能删除自己",
  "data": null
}
```

---

## 7. 前端请求封装建议

### 7.1 响应拦截器

```ts
// 伪代码示例
responseInterceptor(response) {
  const { code, message, data } = response.data

  if (code === 200) {
    return data  // 直接返回 data，调用方无需再解包
  }

  // 401 未登录：跳转登录页
  if (code === 401) {
    router.push('/login')
    return Promise.reject(new Error(message))
  }

  // 422 验证错误：返回错误详情供表单绑定
  if (code === 422) {
    return Promise.reject({
      message,
      errors: data?.errors || {}
    })
  }

  // 其他错误：弹出提示
  Message.error(message)
  return Promise.reject(new Error(message))
}
```

### 7.2 请求封装关键点

1. **始终携带 CSRF Token**：从 Cookie `XSRF-TOKEN` 读取，放入 `X-XSRF-TOKEN` Header
2. **Credentials 模式**：`withCredentials: true`（携带 Cookie）
3. **统一错误处理**：非 200 的业务码统一走拦截器弹错
4. **分页数据解构**：列表接口调用后解构为 `{ list, pagination }` 便于组件使用

---

## 8. 枚举约定

### 8.1 管理员状态 (status)

| 值 | 说明 |
|---|---|
| `1` | 正常（启用） |
| `0` | 禁用 |

### 8.2 菜单类型 (type)

| 值 | 说明 |
|---|---|
| `M` | 目录 |
| `C` | 菜单 |
| `F` | 按钮/权限 |

### 8.3 布尔字段

菜单等模块的布尔字段使用 `0` / `1` 表示：

| 字段 | 说明 |
|---|---|
| `is_enable` | 是否启用 |
| `is_hide` | 是否隐藏菜单 |
| `is_hide_tab` | 是否隐藏标签页 |
| `is_iframe` | 是否 iframe 嵌入 |
| `keep_alive` | 是否缓存 |
| `is_full_page` | 是否整页打开 |
| `fixed_tab` | 是否固定标签页 |
| `show_badge` | 是否显示徽标 |
| `show_text_badge` | 是否显示文字徽标 |

---

## 9. 异常与错误补充

### 9.1 HTTP 层错误

除了业务层的 `code`，还可能遇到 HTTP 级别的异常：

| HTTP 状态码 | 场景 | 处理建议 |
|---|---|---|
| `419` | CSRF Token 过期或缺失 | 刷新页面重新获取 CSRF Cookie |
| `429` | 请求过于频繁 | 展示「操作过于频繁，请稍后再试」 |
| `500` | 服务器内部错误 | 展示「服务器开小差了」，不展示具体错误 |
| `503` | 服务不可用 | 展示「服务维护中」 |

> HTTP 层错误不走统一响应格式，`response.data` 可能是 HTML 或 Laravel 标准错误 JSON。

### 9.2 网络错误

网络断开、超时等情况走请求库的 `catch` 分支，需单独处理。
