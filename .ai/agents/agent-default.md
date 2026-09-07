# Agent：兜底 Agent

**当任务不属于 API、Admin、Database 任何一类时，由本 Agent 接手处理。**

## 前置必读

- **AGENTS.md「规则 3」** — 所有编码任务派发子 Agent 执行
- 编码前先阅读项目中相似功能的现有代码，保持风格一致

## 职责

- 按常规编码流程完成需求
- 优先复用项目中已有的模式和约定
- 如果明确发现任务涉及其他 Agent 领域，编码开始前报告主会话重新选择 Agent

## 常见场景速查

### 项目配置修改

- `.env` 修改环境变量（数据库、缓存、队列等），执行后需 `php artisan config:clear`
- `config/admin.php` 修改 Dcat-Admin 配置（名称、Logo、路由前缀、上传等）
- `config/app.php` 修改时区、语言等全局设置
- 修改配置文件后需要运行 `php artisan config:clear` 清理缓存

### Artisan 命令编写

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class {命令名} extends Command
{
    protected $signature = 'command:name {param?}';
    protected $description = '命令说明';

    public function handle(): int
    {
        // 业务逻辑
        $this->info('操作成功');
        return Command::SUCCESS;
    }
}
```

### Composer 操作

```bash
composer require package/name          # 安装包
composer remove package/name           # 移除包
composer update package/name           # 更新包
composer dump-autoload                 # 刷新自动加载
```

### 前端资源调整

- 项目使用 Vite 构建前端，配置文件 `vite.config.js`
- 运行 `npm run dev` 开发模式，`npm run build` 生产构建
- 公共资源放在 `public/` 目录下

### 通用工具函数

以下函数定义在 `app/helpers.php` 中，可直接全局调用：

| 函数 | 用途 | 示例 |
|------|------|------|
| `format_paginated_datas($datas, $visible, $func)` | 格式化分页数据 | `format_paginated_datas($paginator, ['*'], fn($i) => [...])` |
| `throwBusinessException($msg, $error)` | 抛出业务异常中断程序 | `throwBusinessException('操作失败', 'NO_LOGIN')` |
| `create_captcha($number, $type)` | 生成随机码 | `create_captcha(6, 'figure')` — 6位数字验证码 |
| `comma_str_to_array($str)` | 逗号分隔字符串转数组 | `comma_str_to_array('a,b,c')` → `['a','b','c']` |
| `get_collection_field($list, $field)` | 从 Collection 中提取指定字段值数组 | `get_collection_field($users, 'id')` |
| `full_url($path, $catalogue)` | 拼接文件完整 URL | `full_url('images/1.jpg')` → `http://host/uploads/images/1.jpg` |

## 分页规范

分页接口统一使用 `format_paginated_datas()` 全局函数格式化分页数据，完整模板详见 agent-api.md（Service 分页模板）。

## 行为准则

| 规则 | 说明 |
|------|------|
| 复用现有模式 | 编写代码前先阅读项目中相似功能的代码，保持风格一致 |
| 不越界 | 编码开始前识别是否涉及其他 Agent 领域，如有则报告主会话 |
| 改配置需清缓存 | 修改配置文件后运行 `php artisan config:clear` |
