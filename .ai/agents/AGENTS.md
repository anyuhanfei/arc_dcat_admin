# AGENTS — Agent 定义与协作流程

> 本文件是角色路由与协作流程的权威源；角色选择规则与并行边界与根目录 `AGENTS.md` 第三章保持同步。

> 各 Agent 角色定义详见独立文件：
> - [agent-api.md](agent-api.md) — API 开发 Agent（Controller + Service + Repository + Resource + Request）
> - [agent-admin.md](agent-admin.md) — 后台管理 Agent（Controller + Repository + Grid/Form/Show）
> - [agent-database.md](agent-database.md) — 数据库迁移/填充 Agent（Migration + Seeder + Model）
> - [agent-thirdparty.md](agent-thirdparty.md) — 三方接口对接 Agent（app/Integrations/ 下的三方服务封装）
> - [agent-default.md](agent-default.md) — 兜底 Agent（不属于上述类的其他编码任务）

---

## 选择 Agent 的规则

在派发任务前，按以下优先级判断使用哪个 Agent：

| 优先级 | 任务涉及目录 / 关键词 | 派发 Agent |
|--------|----------------------|-----------|
| 1 | `app/Api/` / `routes/api.php` | [agent-api](agent-api.md) |
| 2 | `app/Admin/` / `app/Admin/routes.php` | [agent-admin](agent-admin.md) |
| 3 | `database/`、`app/Models/`、表结构/字段/索引变更、迁移、Seeder、Model | [agent-database](agent-database.md) |
| 4 | `app/Integrations/` / 三方接口对接（短信/支付/在线合同/OAuth 等） | [agent-thirdparty](agent-thirdparty.md) |
| 5 | 项目配置修改（`.env`、`config/` 下文件）/ Artisan 命令编写（`app/Console/Commands/`）/ Composer 包安装与配置 / 前端资源调整（Vite、CSS、JS）/ 其他 / 不确定 | [agent-default](agent-default.md) |

**判断方法**：先按优先级 1→2→3→4→5 逐个匹配。只要任务涉及某一 Agent 的管辖目录，就派发给该 Agent。如果同时涉及多个（如新建模块需要 API + 数据库 + 后台），则按「并行边界」规则分波派发。

### 并行边界

- **跨模块可并行**：不同模块（如用户模块、文章模块）的独立子任务可同波并行派发
- **同模块内严格串行**：按 database → api → admin 顺序分波，后一阶段依赖前一阶段产物（API 依赖 Model 存在，Admin 依赖 Model/API 完成）
- **分波 = 执行批次**：按依赖关系把任务拆成若干波，每波内可含多个模块的并行子任务，波与波之间串行等待确认

---

## 协作流程

主会话收到需求后：
1. 读取根目录 **AGENTS.md** 确认核心规则
2. 按上表选择对应的 Agent
3. 通过 `task()` 工具派发 Agent，打包最小上下文
4. Agent 独立执行编码，返回结构化摘要（变更文件 + 验证结果）
5. 主会话向用户展示摘要

### 典型调度路径

| 任务类型 | 派发顺序 |
|---------|---------|
| 新建完整模块（数据库 + API + 后台） | agent-database → agent-api → agent-admin |
| 仅 API 接口 | agent-api |
| 仅后台管理 | agent-admin |
| 仅数据库变更 | agent-database |
| 三方接口对接 | agent-thirdparty |
| 项目配置 / 其他 | agent-default |

### 信息不足时的处理

- **不阻塞**：缺少信息时，先完成其他能完成的步骤
- **标记**：用 `[ ]` 列出待补充项及原因
- **汇总**：任务结束时统一列出缺失项，询问用户
- **补充后**：重新执行未完成的步骤

### 错误处理

- 某个 Agent 执行失败 → 将错误写入中间文件，回退到主会话重新规划或询问用户
