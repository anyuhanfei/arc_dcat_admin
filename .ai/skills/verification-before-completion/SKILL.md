---
name: verification-before-completion
description: 完成前必须运行验证命令
---
# verification-before-completion — 完成前验证

> 任务标记完成前必须运行验证。验证失败 → 修复 → 重新验证 → 才能标记完成。
>
> 执行者分工：子 Agent 完成编码时自带验证；主会话汇总后抽查关键项（php -l、route:list）。

---

## 验证命令清单

### 1. PHP 语法检查

对新创建或修改的 PHP 文件运行语法检查：

```bash
php -l app/Api/Controllers/Sys/AgreementsController.php
php -l app/Api/Services/Sys/AgreementsService.php
php -l app/Models/Sys/SysAgreements.php
```

或检查 Git 中有变更的 PHP 文件（子 Agent 通常不 git add，--cached 会查不到变更）：

```bash
git diff --name-only | grep '\.php$' | xargs -I {} php -l {}
```

### 2. Laravel 路由检查

确保 API 和 Admin 路由正确注册：

```bash
# 查看 API 路由
php artisan route:list --path=api

# 查看 Admin 相关路由
php artisan route:list --path=admin
```

### 3. API 规范检查

```bash
# 检查 API Controller 中是否有 try-catch（禁止）
grep -rn 'try\s*{' app/Api/Controllers/ | grep -v '\.md'

# 检查 Service 是否直接调用了 Model（应走 Repository）
grep -rn 'use App\\Models' app/Api/Services/ | grep -v 'Repository'
```

### 4. DB 直接操作扫描

**禁止** `DB::table()` / `DB::select()` / `DB::insert()` / `DB::update()` / `DB::delete()` 操作表数据（事务控制除外）：

```bash
# 在变更文件中扫描 DB 违规
grep -rnE 'DB::table|DB::select|DB::insert|DB::update|DB::delete' app/ | grep -vE 'startTrans|commit|rollback'
```

### 5. 配置缓存（生产环境）

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 测试

项目使用 PHPUnit（tests/ 已有 Feature/Unit 目录，phpunit/phpunit ^12）。
涉及 Bug 修复的场景补一条回归测试（`php artisan test --filter=...`）；新增功能时按需补充测试。

---

## 手动自检清单

- [ ] PHP 语法无错误
- [ ] 路由注册正确（`php artisan route:list`）
- [ ] API Controller 无 `try-catch`
- [ ] Service 未直接引用 Model（应通过 Repository）
- [ ] 无 `DB::` 直接操作数据（事务除外）
- [ ] 无调试日志（`var_dump`、`echo`、`dd`、`ray()`、`dump()`）
- [ ] 无硬编码凭证或敏感信息
- [ ] 配置/路由/视图缓存已清除
