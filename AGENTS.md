# AGENTS.md — 项目 AI 协作规则（跨工具通用层）

> **本文件是多 AI 工具的通用规则入口**：Cursor、OpenAI Codex、GitHub Copilot、Gemini CLI、Windsurf、OpenCode、Claude Code（经 `CLAUDE.md` 的 `@import` 引入）等工具均会自动读取。
>
> **单一事实源说明**：本项目 AI 配置的权威源位于 `.ai/`（`skills/` 技能、`agents/` 编码角色定义），规则以根目录本文件（AGENTS.md）为准；`.reasonix/`、`.claude/`、`.github/`、`.agents/` 均为工具链接层，不持有内容，由 `./sync_ai_config.sh` 生成。修改技能/角色只改 `.ai/` 下文件；规则直接改本文件。
>
> **规则分层说明**：本文件规则分为三层——
> - **通用强制**：所有项目必须遵守（PHP 8.4/Laravel 13 编码规范、目录结构、命名规则、安全规范、git commit）
> - **技术栈固定**：本模板技术选型固定不变（Laravel + Dcat Admin + POST JSON 参数 + `success()` 响应 + 五层架构 + 国际化方案），所有项目统一使用，不做技术选型替换
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

### 规则 0：尊重使用者的手动修改与删除（最高优先级，凌驾于所有其他规则之上）

**使用者手动修改、删除的代码，使用者手动删除的文件，绝对不要修改回来或恢复。**

- 使用者手动修改过的代码行/代码块，即使 AI 认为"不规范""可以优化""有更好写法"，也不得改回或重写
- 使用者手动删除的文件，不得重新创建；使用者手动删除的代码，不得重新添加
- 如不确定某段代码/某个文件是使用者手动处理的还是 AI 生成的，先询问使用者，不得擅自决定
- 本规则优先于"外科手术式修改""简单优先""编码规范"等所有其他规则

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

### 规则 4：不熟悉的框架/库 API 必须先读源码确认，禁止凭印象写代码

**任何不熟悉的框架/库 API（包括但不限于 Dcat Admin、Laravel 框架方法、第三方 Composer 包），在写代码前必须先 Read 对应源码确认方法签名、参数、返回值和实际行为，禁止凭印象、猜测或"应该是这样"写代码。**

- 不确定某个方法是否存在、参数是什么、返回什么 → 先 `Read` vendor 源码或框架源码确认，再写代码
- 不确定某个字段/组件的用法（如 Dcat Admin 的 `when()` 级联、`label()` 颜色、Form `action()`、Show 字段、saving 回调生命周期）→ 先读对应类的源码和 Blade 模板，再写代码
- 子 Agent 执行编码时同样遵守此规则，不得跳过源码确认直接写代码
- 本条规则是避免"写完才发现 API 不存在/用法错误/行为与预期不符"这类错误的根本手段

### 规则 5：主会话派发编码任务前必须验证方案中关键 API 的正确性

**主会话在向子 Agent 派发编码任务前，对方案中涉及的关键、不熟悉或有疑问的 API，必须先读源码验证正确性，不得把未经确认的错误方案传给子 Agent。**

- 派发前自查：方案中每个关键 API 调用是否已确认存在且用法正确？
- 未确认的 API → 主会话先读源码确认，修正方案后再派发
- 子 Agent 执行中发现方案有误时，应立即停止并反馈，不得自行猜测修正

### 规则 6：任务完成验证必须包含实际运行验证，不能只靠语法检查

**任务完成后的验证不能只停留在 `php -l` 语法检查层面。关键页面、关键接口必须实际访问/调用验证（确认 HTTP 200 状态码和核心功能正常），运行时错误必须在交付前暴露和修复。**

- `php -l` 通过 ≠ 功能正常，仅说明语法无误
- 涉及后台页面的任务 → 必须实际访问对应 URL，确认页面正常渲染、无 500 错误、核心交互可用
- 涉及 API 接口的任务 → 必须实际调用接口，确认返回结构正确、无异常
- 涉及数据库变更的任务 → 必须实际执行迁移并确认表结构/字段符合预期
- 验证发现的运行时错误必须修复后重新验证，不得带着已知错误交付

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
| API null 默认值 | API 返回数据中所有 null 值由 return_data() 经 api_normalize_data() 递归转成空字符串；Resource 层不需要再手动 ?? ''，数字字段（id/age 等）本就不会为 null |
| API 响应格式 | 成功（code=1）返回 `{code, msg, data}` 三字段；失败（code=0）返回 `{code, msg, errors, data, error_flag}` 五字段（errors 字段级错误明细空为 {}，error_flag 特殊场景英文标识如 no_login 空为 ""）。由 return_data() 按 code 自动区分，Controller/Resource 不要手动拼这层结构 |
| API Swagger 文档规范 | 所有 API 接口必须有完整 Swagger 注解：summary 一句话摘要、description 接口详细说明、parameters/requestBody 每个参数必须有 description、responses data 每个返回字段必须有 description；有枚举值的字段在 description 里列出所有取值和对应中文（如 type：1=系统消息，2=订单消息）。禁止只写 example 不写说明 |
