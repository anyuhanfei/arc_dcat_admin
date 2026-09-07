#!/usr/bin/env bash
# sync_ai_config.sh — 将 .ai/ 权威源同步为多工具兼容层
#
# 目录架构（单一事实源）：
#   .ai/skills/<name>/SKILL.md   ← 技能权威源（Agent Skills 开放标准格式）
#   .ai/agents/*.md              ← 编码角色定义权威源（路由表 + 5 个角色）
#
# 各工具目录只放符号链接（或 --copy 的副本），不持有内容：
#   .claude/skills/<name>/SKILL.md   Claude Code 技能发现路径
#   .github/skills/<name>/SKILL.md   GitHub Copilot 技能发现路径
#   .agents/skills/<name>/SKILL.md   OpenAI Codex 技能发现路径
#   .reasonix/skills/<name>.md       Reasonix 技能（扁平命名，链接指向 <name>/SKILL.md）
#   .reasonix/agents/<file>.md       Reasonix 角色定义
#
# 用法：
#   ./sync_ai_config.sh          # 默认：建立符号链接（推荐，改 .ai 一处全部生效）
#   ./sync_ai_config.sh --copy   # 改为复制（用于不支持符号链接的工具/平台，改 .ai 后需重跑）
#
# 说明：本脚本会清理目标目录下所有指向 .ai/ 权威源的符号链接（本脚本管理的），然后重建；
#       不删除用户手动放置的非 .ai/ 链接/文件。不修改 .ai/ 下任何权威源文件。
set -euo pipefail
cd "$(dirname "$0")"

SKILL_SRC=".ai/skills"
AGENT_SRC=".ai/agents"
MODE="link"
[ "${1:-}" = "--copy" ] && MODE="copy"

if [ ! -d "$SKILL_SRC" ] || [ ! -d "$AGENT_SRC" ]; then
  echo "错误：找不到权威源 $SKILL_SRC / $AGENT_SRC" >&2
  exit 1
fi

# 0) 清理旧链接：删除目标目录下所有指向 .ai/ 权威源的符号链接（本脚本管理的），
#    防止删除 .ai/ 下技能/角色后残留断链。不删除用户手动放置的非 .ai/ 链接/文件。
for td in .claude/skills .github/skills .agents/skills .reasonix/skills .reasonix/agents .reasonix/rules; do
  [ -d "$td" ] || continue
  find "$td" -type l -lname '*/.ai/*' -delete 2>/dev/null || true
  find "$td" -mindepth 1 -maxdepth 1 -type d -empty -delete 2>/dev/null || true
done

# 1) 技能 → 各工具技能发现路径
mapped=0
for d in "$SKILL_SRC"/*/; do
  [ -d "$d" ] || continue
  n="$(basename "$d")"
  # 标准目录结构工具（Claude / Copilot / Codex）
  for td in .claude/skills .github/skills .agents/skills; do
    target="$td/$n/SKILL.md"
    mkdir -p "$(dirname "$target")"
    if [ "$MODE" = "copy" ]; then
      cp "$d/SKILL.md" "$target"
    else
      ln -sfn "../../../.ai/skills/$n/SKILL.md" "$target"
    fi
    mapped=$((mapped + 1))
  done
  # Reasonix：扁平 <name>.md，保留原文件名
  target=".reasonix/skills/$n.md"
  if [ "$MODE" = "copy" ]; then
    cp "$d/SKILL.md" "$target"
  else
    ln -sfn "../../.ai/skills/$n/SKILL.md" "$target"
  fi
  mapped=$((mapped + 1))
done

# 2) 角色定义 → .reasonix/agents（Reasonix 工具目录）
for f in "$AGENT_SRC"/*.md; do
  fn="$(basename "$f")"
  target=".reasonix/agents/$fn"
  if [ "$MODE" = "copy" ]; then
    cp "$f" "$target"
  else
    ln -sfn "../../.ai/agents/$fn" "$target"
  fi
  mapped=$((mapped + 1))
done

# 3) 规则 → .reasonix/rules/RULES.md（Reasonix 规则入口，指向根 AGENTS.md）
target=".reasonix/rules/RULES.md"
mkdir -p "$(dirname "$target")"
if [ "$MODE" = "copy" ]; then
  cp AGENTS.md "$target"
else
  ln -sfn "../../AGENTS.md" "$target"
fi
mapped=$((mapped + 1))

echo "OK：已映射 ${mapped} 个文件（${MODE}）：.claude/skills、.github/skills、.agents/skills、.reasonix/skills、.reasonix/agents、.reasonix/rules/RULES.md"
echo "提示：修改技能/角色请直接改 .ai/ 下权威源；修改规则直接改根目录 AGENTS.md。"
