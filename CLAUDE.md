# CLAUDE.md — Claude Code 项目规则入口

本文件是 Claude Code 的项目规则入口。为保持单一事实源，规则本体统一放在根目录 `AGENTS.md`（跨工具通用层），此处通过 `@import` 引入，避免两处内容漂移。

- 编码规则与强制规则：见 `AGENTS.md`
- 编码角色规范：`.ai/agents/agent-*.md`
- 技能库：`.claude/skills/`（符号链接自 `.ai/skills/`）

@import AGENTS.md
