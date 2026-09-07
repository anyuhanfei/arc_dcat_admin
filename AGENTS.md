# AGENTS.md — 项目 AI 协作规则（跨工具通用层）

> **本文件是多 AI 工具的通用规则入口**：Cursor、OpenAI Codex、GitHub Copilot、Gemini CLI、Windsurf、OpenCode、Claude Code（经 `CLAUDE.md` 的 `@import` 引入）等工具均会自动读取。
>
> **单一事实源说明**：本项目 AI 配置的权威源位于 `.ai/`（`skills/` 技能、`agents/` 编码角色定义），规则以根目录本文件（AGENTS.md）为准；`.reasonix/`、`.claude/`、`.github/`、`.agents/` 均为工具链接层，不持有内容，由 `./sync_ai_config.sh` 生成。修改技能/角色只改 `.ai/` 下文件；规则直接改本文件。
>
> **规则分层说明**：本文件规则分为四层——
> - **通用强制**：所有基于本模板的项目必须遵守（PHP 8.4/Laravel 13 编码规范、目录结构、命名规则、安全规范、git commit）
> - **技术栈固定**：本模板技术选型固定不变（Laravel + Dcat Admin + POST JSON 参数 + `success()` 响应 + 五层架构 + 国际化方案），所有定制项目统一使用，不做技术选型替换
> - **业务规则扩展**：定制项目在模板规则基础上添加业务规则（订单流程、状态机、权限模型、行业规范），在项目 AGENTS.md 中扩展，详见第六章
> - **工具专用**：Reasonix 相关（`task()` 派发、`ask` 提问、profile 选择），其他 AI 工具用自身对应能力

---

## 一、核心思维模式

### 1. 编码前思考

在写任何代码之前，先回答三个问题：

- **做什么**：这个需求要解决什么核心问题？
- **怎么做**：涉及到哪些文件？修改方案是什么？
- **影响面**：改了哪里？会影响到什么？

形成清晰方案后再动手。复杂任务应先拆解步骤，再逐步执行。

### 2. 简单优先

- 能用 if-else 解决的，不用设计模式
- 能用一个方法写完的，不拆成三个类
- 能不新增文件的，尽量复用现有结构
- **不做超前抽象** — 只有看到两次重复才考虑复用

### 3. 外科手术式修改

- 改什么文件就只改什么文件，不顺手"优化"无关代码
- 不改缩进、不改换行、不改注释格式
- 一个改动一个目的，不把多个无关修改混在一次提交里
- 修改前先读取文件确认当前内容，避免臆断

### 4. 目标驱动执行

- 遇到信息不足 → 标记待补充，继续做能做的部分
- 遇到次要问题 → 记录但不阻塞主线
- 先跑通核心流程，再优化细节
- 每一步都有明确完成标准

---

## 二、强制规则

### 规则 1：需求必须经过追问确认

**对于任何新需求、计划或设计方案，边界模糊或有多个可选方案时，必须先进行追问式确认，逐一检视设计树的每个分支，直到与用户达成共识，才能进入编码阶段。**

- 每次只问一个问题，附带你的推荐答案
- 能通过代码库回答的问题，先探索再问
- 达成共识后，按规则 3 派发子 Agent 执行编码

> 工具适配：Reasonix 中通过 `ask` 工具提问；其他工具用自身的提问/多轮确认能力，同样遵守"每次只问一个问题并附推荐答案"。

**以下情况可以跳过追问，直接执行**：
- 明确的单文件修改（如"给 User 模型加一个字段"）
- 不改接口参数和返回结构的调整
- 用户已经给出了非常具体的指令且无歧义
- 修复明确报错信息的 Bug

**跳过追问不改变规则 3——任何代码创建/修改仍必须由子 Agent 执行。**

如不确定是否需要追问 → 先追问。

### 规则 2：对话中生成的文档与临时文件统一保存在项目 `tmp/` 目录

**所有在对话中生成的项目相关文件（包括但不限于：功能文档、SQL 更新文件、交接文档、任务拆解文件、数据结构文档、验证脚本、调试脚本、中间产物等），必须保存到项目根目录的 `tmp/` 下。**

- 临时文件（验证脚本、调试脚本等）**不得写入系统临时目录（如 `/tmp`）、桌面或其他位置**，一律放项目 `tmp/` 下；使用完成后可酌情删除
- 本规则仅指对话中生成的文档/中间产物/临时文件，代码文件不受此限
- `tmp/` 目录已在 `.gitignore`（`/tmp`），不会污染仓库
- 文件名加时间戳便于追溯，如 `handoff_20260522.md`、`verify_xxx_20260825.php`

### 规则 3：编码任务派发子 Agent 执行

**主会话（编排层）不做任何代码编写或文件修改操作。所有涉及创建/修改代码文件的任务，必须派发给专门的编码 Agent/子 Agent 执行。**

> 工具适配：Reasonix 中通过 `task()` 工具派发（profile 选择 api / admin / database / thirdparty / default）；支持子 Agent 的工具（如 Claude Code 的 subagent、OpenAI Codex 的 agents 等）同样遵循"主会话编排、子 Agent 编码"的分工；若工具不支持子 Agent，则由主会话直接编码，但仍须遵守本文件其余全部规则。

```
用户提问
  │
  ▼
主会话（编排层）
  ├ 理解需求 → 拆解任务
  ├ 按类型选择 Agent（api / admin / database / thirdparty / default）
  ├ task() → Agent 独立执行编码  ← 真正的编码在这里
  ├ 收集结构化摘要（变更文件 + 验证结果）
  └ 向用户展示摘要
```

**选择 Agent 的规则**：详见第三章「任务分派与编码角色」，按优先级 api → admin → database → thirdparty → default 依次匹配。

**主会话可以做的事**：
- 读文件类操作（搜索、浏览代码库，如 grep / glob），用于理解需求和上下文
- 问答（解释概念、讨论方案），不做代码修改
- 拆解任务（分析依赖、分波编排）
- 运行非编码类命令：`php -l`、`composer`、`git`、`php artisan`（route:list/migrate 等非生成类命令，不含 make:*）

**主会话绝对不做**：
- 写文件类操作（新建 / 修改 / 删除代码文件）
- 所有代码写操作由派发的子 Agent 完成

> 例外：按规则 2 在 `tmp/` 下写中间文档（任务拆解、错误记录、交接文档）属文档操作，不违反本规则。

---

## 三、任务分派与编码角色

> 本章节与 `.ai/agents/AGENTS.md` 保持同步（角色选择规则与并行边界两边同源），是子 Agent / 编码角色的选择路由；协作流程细节见 `.ai/agents/AGENTS.md`。

### 角色与选择规则

在派发任务前，按以下优先级判断使用哪个编码角色：

| 优先级 | 任务涉及目录 / 关键词 | 编码角色（规范文件） |
|--------|----------------------|-----------|
| 1 | `app/Api/` / `routes/api.php` | API 开发（`.ai/agents/agent-api.md`） |
| 2 | `app/Admin/` / `app/Admin/routes.php` | 后台管理（`.ai/agents/agent-admin.md`） |
| 3 | `database/`、`app/Models/`、表结构/字段/索引变更、迁移、Seeder、Model | 数据库（`.ai/agents/agent-database.md`） |
| 4 | `app/Integrations/` / 三方接口对接（短信/支付/在线合同/OAuth 等） | 三方对接（`.ai/agents/agent-thirdparty.md`） |
| 5 | 项目配置修改（`.env`、`config/` 下文件）/ Artisan 命令编写（`app/Console/Commands/`）/ Composer 包安装与配置 / 前端资源调整（Vite、CSS、JS）/ 其他 / 不确定 | 兜底（`.ai/agents/agent-default.md`） |

**判断方法**：先按优先级 1→2→3→4→5 逐个匹配。只要任务涉及某一角色的管辖目录，就派发给该角色。如果同时涉及多个（如新建模块需要 API + 数据库 + 后台），则按「并行边界」规则分波派发。

### 并行边界

- **跨模块可并行**：不同模块（如用户模块、文章模块）的独立子任务可同波并行派发
- **同模块内严格串行**：按 database → api → admin 顺序分波，后一阶段依赖前一阶段产物（API 依赖 Model 存在，Admin 依赖 Model/API 完成）
- **分波 = 执行批次**：按依赖关系把任务拆成若干波，每波内可含多个模块的并行子任务，波与波之间串行等待确认

### 协作流程

完整协作流程（派发、Checkpoint、信息不足处理、错误处理）见 `.ai/agents/AGENTS.md`，此处不再重复。

### 各编码角色规范文件

详细编码规范与代码模板由以下文件承载（Reasonix 场景由子 Agent 读取；其他工具可通过这些路径自行阅读）。**这些文件是编码角色规范 + 代码模板文档，供编码子任务读取，不是工具原生的 agent 定义**（Reasonix 以 profile 引用，其他工具当作子任务规范阅读即可）：

- `.ai/agents/agent-api.md` — API 开发（Controller/Service/Repository/Resource/Request 五层 + Swagger + Sanctum）
- `.ai/agents/agent-admin.md` — Dcat-Admin 后台 CRUD（Grid/Form/Show + 字段组件 + 国际化规则）
- `.ai/agents/agent-database.md` — 数据库迁移/填充（Migration/Seeder/Model + 字段规范）
- `.ai/agents/agent-thirdparty.md` — 三方接口对接（app/Integrations/ 目录/命名/HTTP 规范）
- `.ai/agents/agent-default.md` — 兜底（配置修改/Artisan 命令/Composer/前端/通用函数）

### 技能库

项目技能权威源位于 `.ai/skills/`（已按 Agent Skills 开放标准格式编写，含 `name`/`description` frontmatter），并已映射到 `.claude/skills/`、`.github/skills/`、`.agents/skills/`、`.reasonix/skills/` 四个工具发现路径。**新增技能：写入 `.ai/skills/<name>/SKILL.md` 后运行 `./sync_ai_config.sh` 同步**。

| 技能 | 用途 | 使用时机 |
|------|------|---------|
| `arc-coding-skill` | 编码前的索引页 — 根据场景查阅对应 Agent 的代码模板和规范 | 任何代码创建/修改前 |
| `grill-me` | 追问式需求确认（对应规则 1） | 新需求/设计方案边界模糊时 |
| `subagent-driven-development` | 子 Agent 分波编排开发（对应规则 3） | 所有编码任务的派发执行 |
| `verification-before-completion` | 完成前验证命令清单 | 标记任务完成前 |
| `systematic-debugging` | Bug 根因分析与系统性修复 | 遇到 Bug 时 |
| `writing-plans` | 将任务拆解为可验证的小任务清单 | 复杂任务开始前 |
| `handoff` | 会话压缩为交接文档（存 `tmp/`） | 结束长会话前 |
| `writing-skills` | 用 TDD 方式编写新的 Skill 文档 | 需要新增/重写技能时 |

---

## 四、项目基础信息

| 属性 | 值 |
|------|-----|
| PHP | ^8.4 |
| 后端框架 | Laravel ^13.8 + Dcat-Admin 2.0.x-dev |
| 数据库 | MySQL |
| 回复语言 | **中文** |

---

## 五、项目编码规范

### 目录结构

```
app/
├── Api/                          # API 层（五层架构）
│   ├── Controllers/{模块}/{功能}Controller.php   # 控制器：收参→验证→调 Service→返回
│   ├── Services/{模块}/{功能}Service.php           # 业务层：业务逻辑，调 Repository
│   ├── Repositories/{模块}/{功能}Repository.php    # 数据层：本表 CRUD + 复杂查询
│   ├── Resources/{模块}/{功能}Resource.php         # 输出层：字段转换、隐藏内部字段
│   └── Requests/{模块}/{方法名}Request.php          # 验证层：参数验证（每个方法一个文件）
├── Admin/                        # 后台管理层
│   ├── Controllers/{模块}/{功能}Controller.php     # 后台控制器（Grid/Form/Show）
│   ├── Repositories/{模块}/{功能}.php              # 后台 Repository
│   ├── Form/Field/                                # 自定义表单字段（如 MediaSelector）
│   └── Actions/{模块}/{动作名}.php                 # 自定义动作按钮（RowAction/BatchAction）
├── Models/{模块}/{模型名}.php                      # 模型（API+Admin 共用）
├── Enums/{枚举名}.php                              # PHP enum 类（状态/类型等固定枚举）
├── Actions/{模块}/{动作名}.php                     # 独立业务 Action 类（状态机流转等，前后台共用）
├── Events/{事件名}.php                             # 事件类（事件解耦）
├── Listeners/{监听者名}.php                        # 事件监听者（事件解耦）
├── Jobs/{任务名}.php                               # 队列任务（耗时操作）
├── Services/{服务名}Service.php                    # 跨模块服务（如 MessageService 消息中心）
├── Integrations/{三方名}/                          # 三方接口对接（短信/支付/OAuth 等）
├── Messages/Templates/{模板名}.php                 # 消息内容模板类
├── Console/Commands/{命令名}.php                   # Artisan 自定义命令
└── Admin/routes.php                                # 后台路由
routes/
├── api.php                                     # API 路由
└── web.php                                     # 前端路由
database/
├── migrations/                                 # 数据库迁移
└── seeders/                                    # 数据填充
config/                                         # 配置文件
resources/lang/{locale}/{模块}.php               # 国际化语言文件（zh_CN/en 双语同步）
tmp/                                            # 对话中生成的临时文件（已 gitignore）
```

> 其余目录（`bootstrap/`、`public/`、`storage/`、`vendor/`、`tests/` 等）按 Laravel 框架标准目录使用，不另行约定。

### 命名规则

| 文件类型 | 规则 | 示例 |
|---------|------|------|
| API Controller | `{模块}/{功能}Controller.php` | `Sys/AgreementsController.php` |
| API Service | `{模块}/{功能}Service.php`（API 层业务服务，仅 API 调用） | `Sys/AgreementsService.php` |
| API Repository | `{模块}/{功能}Repository.php` | `Sys/AgreementsRepository.php` |
| API Resource | `{模块}/{功能}Resource.php` | `Sys/AgreementsResource.php` |
| API Request | `{模块}/{方法名}Request.php` | `Sys/AgreementsShowRequest.php` |
| Model | `{模块}/{表名PascalCase}.php` | `Sys/SysAgreements.php` |
| Enum | `{枚举名}.php`（PascalCase） | `MessageType.php`、`MessageSelectType.php` |
| Action（独立业务类） | `{模块}/{动作名}Action.php` | `Feedback/HandleFeedbackAction.php` |
| Event | `{事件名}Event.php` | `OrderPaidEvent.php` |
| Listener | `{动作名}Listener.php` | `SendOrderPaidMessageListener.php` |
| Job | `{任务名}Job.php` | `SendSmsJob.php` |
| Admin Controller | `{模块}/{功能}Controller.php` | `Sys/SysAgreementsController.php` |
| Admin Action（按钮） | `{模块}/{动作名}.php` | `Feedback/StartHandle.php` |
| 迁移文件 | `{时间戳}_create_{表名}_table.php` | `2025_01_01_000001_create_sys_agreements_table.php` |
| 填充文件 | `{表名PascalCase}Seeder.php` | `SysAgreementsSeeder.php` |

### git commit 规范

- 提交信息格式：`feat(模块): 中文描述`（type 用 feat/fix/docs/refactor/chore 等）
- 一次提交只做一个目的，不混入无关修改
- 示例：`feat(sys): 新增协议管理 API`

### PHP 8.4 / Laravel 13 编码约束

| 约束 | 说明 |
|------|------|
| 全量类型声明 | 所有方法参数、返回值、类属性必须声明类型；闭包参数和返回值也声明类型 |
| 禁用已弃用函数 | 不使用 `utf8_encode()` / `utf8_decode()`（PHP 8.4 弃用）等已弃用函数；用 `mb_convert_encoding()` 替代 |
| 内置数组函数 | 查找用 `array_find()` / `array_find_key()`，判断用 `array_any()` / `array_all()`（PHP 8.4 内置），不写 foreach 手动查找 |
| readonly DTO | 请求/响应数据传输对象、值对象用 `readonly class`，属性构造后不可变 |
| 固定枚举用 enum 类 | 状态/类型等固定枚举用 PHP enum 类（`app/Enums/`，int 或 string backed），Model `$casts` 声明 Enum::class；禁止使用 const + STATUS_MAP 常量数组方式 |
| 属性钩子 | Model 访问器/修改器必须用 PHP 8.4 属性钩子，不写 Laravel `Attribute::make()` 或旧的 `getXxxAttribute()` / `setXxxAttribute()`。示例：消息 `content` 保存时自动生成 `content_summary`（去 HTML 标签截取 100 字） |
| 批量写入用 upsert | 批量创建/更新关联记录（如标记已读、批量导入）用 `Model::upsert($data, $uniqueBy, $update)`，禁止循环 firstOrCreate/create；唯一键由迁移联合索引保证 |
| 关联查询用 whereRelation | 关联条件查询用 `Model::whereRelation('relation', 'field', $value)`（Laravel 10+ 内置），比 `whereHas` 更简洁；嵌套条件用闭包 |
| 耗时 IO 不阻塞 | 外部 HTTP 调用、文件处理、大计算等耗时操作必须用 Laravel Queue（Job 放 `app/Jobs/`），不同步阻塞请求；PHP 8.4 async/await 可用于轻量并发 |
| 配置不硬编码 | 第三方密钥、环境相关值读 `config()`，config 读 `env()`，禁止在代码里写死密钥/URL/开关 |
| 功能开关用 Pennant | 灰度发布、AB 测试、功能开关用 Laravel Pennant，不写 `if (env('XXX'))` 硬编码判断 |
| 健康检查用 Health | 如需健康检查端点，用 Laravel 内置 Health 组件（`php artisan health:install`），不手写 |
| 队列任务规范 | 耗时任务（短信、邮件、文件处理、第三方回调）必须走 Queue；Job 类放 `app/Jobs/`，失败重试次数在 Job 类声明 `$tries` |

---

## 六、定制项目业务规则扩展指南

本模板作为基础框架，后续基于它开发定制项目（商城、App、小程序等）时，**技术栈和代码结构完全固定**（Laravel + Dcat Admin + POST JSON + `success()` 响应 + 五层架构），定制的是**业务逻辑**。在**定制项目根目录**创建 `AGENTS.md`，按以下结构扩展：

### 扩展方式

1. **引入模板规则**：用 `@import` 引入本模板的 AGENTS.md（技术栈固定，全部继承）
2. **添加项目业务规则**：订单流程、状态机、权限模型、行业特定规范等
3. **添加项目特定约定**：如 API 方法判定规则、模块目录扩展、第三方对接规范等

### 扩展示例（商城项目）

```markdown
# 商城项目 AGENTS.md

@import /path/to/arc_dcat_admin/AGENTS.md

## 项目业务规则

### 订单规则
- 主订单/子订单区分：订单列表"全部"等标签页展示主订单，详情页为主订单详情；"租赁中"标签页下子标签页展示子订单，详情页为子订单详情
- 订单状态机：待支付 → 已支付 → 已发货 → 已完成 / 已取消 / 退款中

### API 方法判定规则
- 带 Swagger 格式（@OA\Post/@OA\Response 等 OpenApi 注解）的方法才是正在使用的方法
- 仅 @ApiSummary 旧格式或无注释的是废弃方法，修改时不要理会

### 模块目录扩展
- app/Shop/ — 商城核心模块（商品、订单、购物车）
- app/Marketing/ — 营销模块（优惠券、活动）
- app/Integration/ — 第三方对接（支付、物流）
```

### 可扩展项清单

| 可扩展项 | 说明 | 示例 |
|---------|------|------|
| 业务规则 | 项目特定的业务流程和约束 | 商城主订单/子订单区分、订单状态流转 |
| 状态机 | 业务实体的状态流转规则 | 商品上下架、订单支付/发货/完成 |
| 权限模型 | 项目特定的权限和角色 | 商城商家权限、运营权限 |
| 模块目录 | 项目特定的模块目录扩展 | `app/Shop/`、`app/Marketing/`、`app/Integration/` |
| 第三方对接 | 项目特定的第三方服务 | 支付、物流、短信、OAuth |
| 项目特定约定 | 项目特定的编码约定 | API 方法的 Swagger 判定规则、字段命名约定 |

### 固定项（所有定制项目统一，不可变更）

| 固定项 | 内容 |
|--------|------|
| 技术栈 | Laravel + Dcat Admin + PHP 8.4 + MySQL |
| 接口格式 | POST JSON 统一参数 + `success()` 响应格式 |
| 架构层级 | Controller / Service / Repository / Resource / Request 五层 |
| 目录结构 | 见第五章完整目录结构（app/Api 五层 + app/Admin + app/Models + app/Enums + app/Actions + app/Events + app/Jobs + app/Integrations 等） |
| 命名规则 | 见第五章命名规则表 |
| 编码规范 | PHP 8.4 / Laravel 13 编码约束（见第五章） |
| git commit | `feat(模块): 中文描述` 格式 |

> 固定项确保所有基于本模板的项目代码风格一致，降低跨项目维护成本。业务定制通过"扩展"实现，不通过"替换技术栈"实现。
