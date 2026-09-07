---
name: arc-coding-skill
description: 编码前的索引页 — 根据场景查阅对应 Agent 的代码模板和规范
---
# arc-coding-skill — 编码技能索引

> 本文件是**子 Agent 编码时的执行流程速查与生成后检查清单**，与 `task()` prompt 中直接注入的 AGENTS.md + agent-*.md 模板互补：prompt 注入规则模板，子 Agent 另读本文件获取流程速查。

---

## 前置必读

| 场景 | 必读文档 | 说明 |
|------|---------|------|
| 所有编码任务 | [AGENTS.md](../../../AGENTS.md) | 核心思维模式 + 3 条强制规则 |
| API 接口 | [agent-api.md](../../agents/agent-api.md) | Controller/Service/Repository/Resource/Request 五层模板 |
| 后台管理 | [agent-admin.md](../../agents/agent-admin.md) | Dcat-Admin CRUD 模板 |
| 数据库迁移/填充 | [agent-database.md](../../agents/agent-database.md) | Migration/Seeder/Model 模板 |
| 三方接口对接 | [agent-thirdparty.md](../../agents/agent-thirdparty.md) | 三方对接目录/命名/HTTP 客户端规范 |
| 其他 | [agent-default.md](../../agents/agent-default.md) | 兜底编码规范 |

---

## 执行流程速查

| 任务类型 | 执行步骤 |
|---------|---------|
| **创建数据模型** | ① 创建迁移文件 → ② 创建 Model → ③ 关联定义 → ④ 如需填充加 Seeder |
| **生成 API 接口** | ① 确认 Model 存在 → ② 生成 Request → ③ 生成 Repository → ④ 生成 Service → ⑤ 生成 Controller → ⑥ 注册路由 |
| **生成后台 CRUD** | ① 确认 Model 存在 → ② 生成 Repository → ③ 生成 Controller（含 Grid/Form/Show） → ④ 注册路由 → ⑤ 提示菜单注册 |

---

## 生成后检查清单

- [ ] 任务是否派发给了正确的 Agent？（api / admin / database / thirdparty / default）
- [ ] 是否已阅读对应 Agent 的完整模板和规范？
- [ ] 修改前是否已读取目标文件的当前内容？
- [ ] 是否遵循了"外科手术式修改"原则（不改无关代码）？
- [ ] 验证已按 verification-before-completion 执行（php -l / route:list 等）
