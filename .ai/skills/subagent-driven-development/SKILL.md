---
name: subagent-driven-development
description: 编排子 Agent 分波执行复杂任务，主会话保持轻量
---

# subagent-driven-development — 子 Agent 编排开发

> **工具专用**：本技能基于 Reasonix 的 `task()` 工具设计（profile 选择、write_paths、独立上下文）。其他 AI 工具（Claude Code、OpenAI Codex 等）用自身的子 Agent / subagent 能力，遵循相同的"主会话编排、子 Agent 编码"分工即可，不需读本技能。
>
> 将复杂任务拆解为多波（waves），主会话仅做编排协调，子 Agent 在独立上下文中执行具体编码，返回摘要后被释放。防止主会话上下文膨胀。

---

## 核心理念

主会话（Orchestrator）不写代码，只做拆解、派发、汇总（约束详见 AGENTS.md 规则 3）；本技能补充编排的操作细节。

### 补充规则

- **无文件冲突**：同一波内，不能有两个子 Agent 修改同一个文件。拆解时需按文件归属分配。
- **状态存于文件**：子 Agent 间的中间结果通过 `tmp/` 下的文件传递，不通过对话历史。
- **Checkpoint 机制**：每波完成后，Orchestrator 汇总摘要展示给用户，用户确认后才进入下一波。

---

## 编排流程

### 1. 拆解任务

接到需求后，Orchestrator 先分析任务依赖关系，分波编排：

| 波次 | 子任务 | 文件归属 | 依赖 |
|------|--------|---------|------|
| 1 | 数据库迁移 + Model | `database/migrations/`, `app/Models/` | — |
| 2 | API 接口（含路由注册） | `app/Api/`, `routes/api.php` | 依赖波 1（API 需 Model 已存在） |
| 3 | Admin CRUD（含后台路由） | `app/Admin/`, `app/Admin/routes.php` | 依赖波 1、2 |

> **同模块内严格串行**：按 database → api → admin 分波，后一阶段依赖前一阶段产物；**跨模块（如用户模块、文章模块）之间可并行**。与 AGENTS.md「并行边界」一致。
> **文件冲突检测**：两个子任务修改同一文件→标记为串行或拆分为不同波。

### 2. 派发子 Agent

对每个可独立执行的子任务，通过 `task()` 工具派发，必传：

- **prompt**：任务描述 / 最小上下文 / 涉及文件 / 完成标准 / 必须遵守的规则文件路径
- **description**：子任务名
- **write_paths**：声明写哪些文件，便于并行不冲突

按任务类型用 **profile** 指定子 Agent（api / admin / database / thirdparty / default）。

**子 Agent prompt 规范**：

```
任务：<子任务名>
上下文：<与当前任务相关的最小上下文，不传完整会话>
涉及文件：<文件路径列表>
完成标准：<明确可验证的标准>

必须遵守的规则（先读取对应规则文件，再开始编码）：
- 规则文件：AGENTS.md
- Agent 规范：.ai/agents/<对应 agent>.md
- 编码模板：.ai/skills/arc-coding-skill/SKILL.md

限制：
- 只修改指定文件，不越界
- 先读对应文件，确保代码符合规则后再动手
- 完成后返回结构化摘要（修改了哪些文件、做了什么变更、验证结果、是否有遗留问题）
- 不做代码审查、不重构无关代码
```

**规则注入速查表**：角色选择见 AGENTS.md 第三章 / `.ai/agents/AGENTS.md`；确定角色后，prompt 统一注入：

| 子任务类型 | 必须注入的规则文件 |
|-----------|-------------------|
| **数据库迁移 / Model** | `AGENTS.md` + `.ai/agents/agent-database.md` + `.ai/skills/arc-coding-skill/SKILL.md` |
| **API 接口** | `AGENTS.md` + `.ai/agents/agent-api.md` + `.ai/skills/arc-coding-skill/SKILL.md` |
| **后台 CRUD** | `AGENTS.md` + `.ai/agents/agent-admin.md` + `.ai/skills/arc-coding-skill/SKILL.md` |
| **三方接口对接** | `AGENTS.md` + `.ai/agents/agent-thirdparty.md` + `.ai/skills/arc-coding-skill/SKILL.md` |
| **其他** | `AGENTS.md` + `.ai/agents/agent-default.md` + `.ai/skills/arc-coding-skill/SKILL.md` |

> **原理**：子 Agent 在独立会话中运行，不共享主会话的规则上下文。每条 prompt 必须携带子任务所需的 Agent 引用，子 Agent 通过 `read_file` 读取后按规则执行，确保输出与主会话标准一致。

### 3. 收集结果

子 Agent 返回后，Orchestrator 提取其**结构化摘要**（仅关键信息），不保留对话细节。摘要格式：

```markdown
## [子任务名] 完成

### 变更文件
- `path/to/file1.php` — 做了什么变更
- `path/to/file2.php` — 做了什么变更

### 验证
- php -l 语法检查：通过
- php artisan route:list：通过

### 遗留问题
- 无 / <问题列表>
```

### 4. Checkpoint

每波全部完成后，Orchestrator 向用户展示该波的汇总摘要，等待确认后才进入下一波。

---

## 编排模式

### 模式 A：平行波（独立无依赖）

跨模块的独立子任务可同波并行（见 AGENTS.md「并行边界」）。

### 模式 B：流水线波（有先后依赖）

任务有明确的先后依赖，前一波的输出是后一波的输入。

```
波 1: [A] 数据库设计 + 迁移 + Model
波 2: [B] API 接口          ← 依赖波 1 的 Model
波 3: [C] Admin CRUD        ← 依赖波 2 的 API
```

### 模式 C：构建-验证分离

审查环节用内置 review skill（主会话调用），builder 与 reviewer 串行分波。

---

## 子 Agent 权限层级

| 模式 | 派发方式 | 工具范围 | 适用场景 |
|------|---------|---------|---------|
| **read-only** | `read_only_task`，或 `task()` 的 tools 白名单仅含只读工具 | `read_file`, `grep`, `glob`, `ls`, `explore` | 代码调研、审查 |
| **safe-edit**（默认） | `task()` + `write_paths` + tools 白名单 | 读写工具 + `bash`（仅限 `php -l` 等验证） | 日常编码实现 |
| **full** | `task()` 不设 tools 白名单 | 全部工具 | 需要完整操作的任务 |

---

## 适用场景

- 涉及多模块/多层的全栈功能开发
- 复杂重构（多文件独立变更）
- 前后端并行开发
- 大功能需要分阶段交付
- 任何担心主会话上下文膨胀的场景
