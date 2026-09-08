# Agent：API 开发 Agent

**负责 API 接口代码的完整生成。**

> **技术栈固定**：本规范的五层架构、POST JSON、success() 响应为模板固定标准。

## 前置必读

- **AGENTS.md「规则 3」** — 所有编码任务派发子 Agent 执行
- **[agent-admin.md](agent-admin.md)** — 后台管理规范（API 与 Admin 共用 Model 的理解）
- **[agent-database.md](agent-database.md)** — 数据库规范（Model 缺失时调此 Agent）

### API 编码规则（本 Agent 专属）

- **所有 API 参数以 POST JSON 传递** — 禁止路由参数绑定，统一 `$request->input('key')`
- **API 严格五层** — Controller（收参）→ Service（业务）→ Repository（数据）→ Resource（输出）+ Request（验证）；调用链 Controller→Service→Repository→Model，输出经 Resource 转换
- **Controller 层不使用 try-catch** — 业务异常通过 `throwBusinessException()` 抛出 `BusinessException`，由 `bootstrap/app.php` 异常处理器统一转换为 `error()` JSON 响应；Controller 层 try-catch 会捕获 BusinessException 导致接口无法正常返回错误。非业务异常（第三方接口超时、数据库连接失败等）推荐在 Service 层或更里层捕获，转换为 BusinessException 后抛出
- **Service 层无直接调用 Model 的权限** — 对数据进行操作或查询必须通过 Repository 层转发；Service 层调用其他 Service、工具类、三方对接类（`app/Integrations/`）、跨模块共享服务（`app/Services/`，如 MessageService）等不受限制
- **Repository 做数据层查询** — 单表 CRUD + 多表联合/UNION 查询，不做业务判断
- **验证器使用 Laravel FormRequest** — 每个方法一个文件
- **返回禁止直接暴露 Model** — 必须用 API Resource 或在 Service 手动组装数组，禁止 `return $model` / `return $collection`（防止暴露内部字段、敏感信息）

## 职责

- 根据需求生成 API 接口的完整五层代码
- 自动判断是否需要分页、关联查询、事务
- 路由注册到 `routes/api.php`

## 生成的文件

| 层 | 路径 | 说明 |
|---|------|------|
| Controller | `app/Api/Controllers/{模块}/{功能}Controller.php` | 收参→验证→调 Service→返回 |
| Service | `app/Api/Services/{模块}/{功能}Service.php` | 业务逻辑，调 Repository |
| Repository | `app/Api/Repositories/{模块}/{功能}Repository.php` | 本表 CRUD |
| Resource | `app/Api/Resources/{模块}/{功能}Resource.php` | 输出字段转换（隐藏内部字段、格式化） |
| Request | `app/Api/Requests/{模块}/{方法名}Request.php` | 参数验证（每个方法一个文件） |
| Route | `routes/api.php` | 注册 POST 路由 |

## 代码模板

### Controller 模板

```php
<?php

namespace App\Api\Controllers\{模块};

class {功能}Controller extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected {功能}Service $service,
    ) {}

    #[
        OA\Post(
            path: '/api/{模块小写}/{功能小写}/{方法名}',
            summary: '{接口说明}',
            tags: ['{标签名}'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'key', type: 'integer', description: '参数说明'),
                    ]
                )
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: '成功',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 1),
                            new OA\Property(property: 'msg', type: 'string', example: 'success'),
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                ]
                            ),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function {方法名}({方法名}Request $request): JsonResponse
    {
        // 从 request->input() 获取参数（POST JSON body）
        $params = $request->input('key');

        // 需登录接口获取当前用户：$request->user() 或 auth('sanctum')->user()
        $user = $request->user();

        $result = $this->service->{逻辑方法}($params);

        return success('success', $result);
    }
}
```

### Service 模板

```php
<?php

namespace App\Api\Services\{模块};

use App\Api\Repositories\{模块}\{功能}Repository;

class {功能}Service
{
    public function __construct(
        protected {功能}Repository $repository,
    ) {}

    public function {逻辑方法}($params): array
    {
        // 业务逻辑，只调 Repository
        return $this->repository->{数据方法}($params);
    }
}
```

### Service 模板（分页查询）

```php
<?php

namespace App\Api\Services\{模块};

use App\Api\Repositories\{模块}\{功能}Repository;

class {功能}Service
{
    public function __construct(
        protected {功能}Repository $repository,
    ) {}

    /**
     * 分页列表
     *
     * @param int $page     页码
     * @param int $pagesize 每页条数
     */
    public function paginatedList(int $page, int $pagesize): array
    {
        $paginator = $this->repository->paginate($page, $pagesize);

        return format_paginated_datas($paginator, ['*'], fn ($item) => [
            'id'    => $item->id,
            'title' => $item->title,
        ]);
    }
}
```

### Repository 模板

```php
<?php

namespace App\Api\Repositories\{模块};

use App\Models\{模块}\{模型名};
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class {功能}Repository
{
    public function {数据方法}($params): Collection|array
    {
        // 数据层查询：单表 CRUD + 多表联合/UNION，不做业务判断
        return {模型名}::select('id', 'title')->get();
    }

    /**
     * 分页查询
     */
    public function paginate(int $page, int $pagesize): LengthAwarePaginator
    {
        return {模型名}::select('id', 'title', 'content')
            ->orderBy('id', 'desc')
            ->paginate($pagesize, ['*'], 'page', $page);
    }
}
```

### Resource 模板

```php
<?php

namespace App\Api\Resources\{模块};

use Illuminate\Http\Resources\Json\JsonResource;

class {功能}Resource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'status'      => $this->status, // 状态/枚举返回原始 int，label 由前端映射
            'created_at'  => $this->created_at?->format('Y-m-d H:i:s'),
            // 只暴露业务需要的字段，隐藏 is_show、sort、deleted_at 等内部字段
        ];
    }
}
```

> 列表返回用 `{功能}Resource::collection($collection)`；单条返回用 `new {功能}Resource($model)`。Controller 用 `success('success', new {功能}Resource($result))` 返回。

### 事务模板（多步写入）

```php
// Repository 方法内：DB::transaction 包裹多步写入，返回结果
use Illuminate\Support\Facades\DB; // 文件顶部导入

public function {事务方法}($params): array
{
    return DB::transaction(function () use ($params) {
        $row = {模型名}::create($params);                        // 第一步写入
        {模型名}::where('id', $row->id)->update(['status' => 1]); // 后续写入

        return $row->toArray();
    });
}
```

> 事务内业务异常用 `throwBusinessException()` 抛出后事务自动回滚，无需手动 `rollback()`。

### Request 模板

```php
<?php

namespace App\Api\Requests\{模块};

use Illuminate\Foundation\Http\FormRequest;

class {方法名}Request extends FormRequest
{
    public function authorize(): bool
    {
        // 当前项目未接入 Sanctum，暂保持返回 true；接入后启用：
        // return auth('sanctum')->check();
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => 'required|integer|exists:{表名},id',
        ];
    }

    public function messages(): array
    {
        return [
            'key.required' => '参数名 不能为空',
            'key.exists'   => '数据不存在',
        ];
    }
}
```

### 路由模板

```php
// routes/api.php

// 路由注册路径不带 /api 前缀：前缀由 bootstrap/app.php 的 withRouting(api: ...) 自动添加
// 注意：Swagger 的 OA path 注解（含 /api 的完整 URL 路径）与路由注册口径不同，两者并存是正确做法

// ========== 公开接口（无需登录） ==========
Route::group([], function () {
    Route::post('{模块小写}/{功能小写}/{方法名}', [{功能}Controller::class, '{方法名}']);
});

// ========== 需登录接口（Sanctum 鉴权） ==========
Route::middleware('auth:sanctum')->group(function () {
    Route::post('{模块小写}/{功能小写}/{方法名}', [{功能}Controller::class, '{方法名}']);
});
```

> 路由必须按"公开 / 鉴权"分区注册，禁止零散单条路由混写；新增接口时归入对应分组。

## 注意事项

| 事项 | 说明 |
|------|------|
| 响应方法 | `success($msg, $data)` code=1；`error($msg, $error)` code=0 |
| 返回格式 | `success('success', $data)` → `{code:1, msg, data}`；`error('err', NO_LOGIN)` → `{code:0, msg, data:[], error:'no_login'}` |
| 状态/枚举字段 | 接口返回原始值（int），不返回 label 文本；label 由前端按映射表展示。如业务确需 label，在 Service 用对应 Enum 类的 `label()` 方法或 `Enum::cases()` 映射，禁止在 Controller/Service 硬编码映射数组 |
| 错误码常量 | 错误码常量（如 `NO_LOGIN`）定义在 `app/helpers.php`，使用前先 grep 确认是否已存在，禁止重复定义 |
| 分页格式 | Service 中 `format_paginated_datas($paginator, ['*'], fn($item) => [...])` → Controller 中 `success('success', $result)`；分页/查询返回字段中的 status 直接返回原始值（见「状态/枚举字段」） |
| 输出转换 | 单条用 `new {功能}Resource($model)`，列表用 `{功能}Resource::collection($collection)`；禁止直接 return Model/Collection；Resource 只暴露业务字段，隐藏 is_show/sort/deleted_at 等内部字段 |
| 参数来源 | 统一从 `$request->input('key')` 获取，禁止路由参数绑定 |
| 验证失败 | FormRequest 验证失败自动返回 422 + JSON 错误信息，Controller 无需处理 |
| 业务异常 | `throwBusinessException($msg, $error)` 抛出（全局函数），由 Laravel 异常处理器统一处理 |
| Service 层无直接调 Model 权限 | 数据操作/查询必须通过 Repository 层转发；调用其他 Service/工具类/三方封装/跨模块服务不受限 |
| Repository 不做业务判断 | 做数据层查询（单表 CRUD + 多表联合/UNION 查询），不写业务逻辑 |
| 鉴权 | 当前项目未接入 Sanctum，`authorize()` 暂返回 `true`；接入后改为 `return auth('sanctum')->check();` |
| 401 处理 | Sanctum 未认证自动返回 401 JSON，Controller 无需处理 |
| 路由版本 | 路由注册路径不带 `/api` 前缀（由 `bootstrap/app.php` 的 `withRouting(api: ...)` 自动添加）；Swagger OA `path` 注解仍写含 `/api` 的完整路径，两者并存 |
| Model 缺失 | 先创建迁移文件和 Model，参照 `app/Models/Sys/SysAgreements.php` 风格 |

## 安全与边界规范

| 规范 | 说明 |
|------|------|
| 越权校验（条件式） | 涉及**用户私有数据**的接口（表含 `user_id` 且数据属于特定用户，如反馈/订单/个人消息）必须校验资源归属：Repository 查询强制带 `user_id` 条件（如 `findByIdAndUser($id, $userId)`），禁止只按 id 查询；**公开资源**（文章/商品/公告等无归属的数据）不强制校验 |
| throttle 速率限制 | 写入类接口（提交/创建/修改）默认加 `throttle` 中间件（如 `->middleware('throttle:10,1')` 每分钟 10 次），防止刷接口；公开接口更需限流 |
| 事务边界 | 多步写入（关联表、文件记录+业务数据、状态流转+日志）必须用 `DB::transaction()` 包裹，失败自动回滚；单表写入不需要 |
| 事件解耦 | 提交/状态变更后需触发通知（站内信/邮件/短信）或后续动作时，用 Laravel Event/Listener 解耦，不写在 Controller/Service 主流程里；Event 类放 `app/Events/`，Listener 放 `app/Listeners/` |
| XSS 防护 | 用户输入的富文本字段（content/reply 等）在 API 输出时需转义或净化；纯文本字段 Laravel 自动转义；富文本字段如需保留 HTML，用 HTML Purifier 等净化库过滤危险标签 |

## 系统通知中心规范

消息、站内信等跨模块自动生成的通知，统一通过 **MessageService** 入口发送，禁止各触发点各自实现消息生成逻辑：

- **统一入口**：`app/Services/MessageService.php`，提供 `send(int $userId, MessageType $type, array $data)`、`sendToAll(MessageType $type, array $data)` 方法
- **触发点直接调用**：用户操作（订单支付）、管理员操作（审核通过）、队列任务（超时取消）、定时任务（到期提醒）等触发点直接调用 `MessageService::send()`，不各自写消息创建逻辑
- **消息内容用模板类**：消息标题/内容模板放 `app/Messages/Templates/`（或数据库模板表），触发点只传业务数据（订单号、金额等），由模板类组装标题和内容，禁止在触发点硬编码消息文案
- **消息类型用 enum**：`app/Enums/MessageType.php` 定义系统/订单/营销等类型，扩展新类型只需加 enum case + 模板类
- **单用户消息自动创建关联记录**：`send()` 方法内用事务同时创建消息 + 消息用户关联记录
- **全部用户消息不创建关联记录**：`sendToAll()` 只创建消息（select_type=all），用户阅读时才创建关联（见 agent-database.md「延迟创建关联记录」）

## 计数类接口规范

未读数、待处理数等计数类接口，按以下规范：

- **单独接口**：计数类接口单独提供（如 `/api/messages/unread-count`），不混在列表接口返回里（列表接口数据量大，计数单独查询更高效）
- **未读数计算**：未读 = 有关联记录且 is_read=0 的消息数 + 全部用户消息中 created_at > 用户 messages_read_at 的消息数（用子查询或 UNION COUNT）
- **标记全部已读**：更新用户表 `messages_read_at` 时间戳（比批量创建关联记录高效），不批量创建关联记录
- **计数缓存**：高频访问的计数（如未读数）可用 Redis 缓存，写入时主动失效；低频计数直接查库
- **返回格式**：`success('success', ['unread_count' => 5])`

## Sanctum 接入步骤

> 当前项目未安装 laravel/sanctum（`composer.json` 无该包，`config/auth.php` 只有 web guard），接入前 `authorize()` 暂保持返回 `true`。

1. `composer require laravel/sanctum`
2. `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`
3. 运行迁移（生成 `personal_access_tokens` 表）
4. `config/auth.php` 的 guards 增加 `'sanctum' => ['driver' => 'sanctum', 'provider' => 'users']`
5. API 路由加 `auth:sanctum` 中间件（见「路由模板」）

## Swagger API 文档

每个 API Controller 方法必须包含 `#[OA\Post]` 属性注解，定义请求路径、参数、响应结构。

### 文档说明

- 基础 API 信息（title、version、server）定义在 `app/Api/Controllers/Sys/AgreementsController.php` 的 `#[OA\Info]` 和 `#[OA\Server]` 属性中
- 文档访问地址：`http://your-domain/api/documentation`（Swagger UI）；原始 JSON：`http://your-domain/api/documentation/docs`
- 生成命令：`php artisan l5-swagger:generate`，生成文件在 `storage/api-docs/api-docs.json`

### 编写规范

- 每个方法必须标注 `#[OA\Post]`（所有 API 使用 POST 方法）
- `path` 属性使用完整路径（含 `/api` 前缀），如 `/api/sys/agreements/list`
- `tags` 按模块分组，如 `['协议管理']`、`['用户管理']`
- 请求参数使用 `OA\RequestBody` + `OA\JsonContent` 定义（POST JSON body）
- 不使用 `OA\Parameter(in: 'query')`（项目所有参数为 JSON body）
- 响应结构使用 `new OA\JsonContent` + `new OA\Property` 定义
- 列表返回使用 `type: 'array'` + `items: new OA\Items(properties: [...])`
- 不包含 `operationId`、`security` 等非必需字段
