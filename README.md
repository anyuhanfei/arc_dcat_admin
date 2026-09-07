# ARC-Dcat-Admin

> 基于 [Dcat-Admin](https://github.com/jqhph/dcat-admin) 二次开发的后台管理框架脚手架，预置了项目通用配置与功能模块，开箱即用，便于快速启动新项目。

本项目是 **Dcat-Admin 2.0.x** 的增强版骨架，使用维护中的 [个人 Fork 版本](https://github.com/anyuhanfei/dcat_admin) 作为底层依赖，并预先配置了品牌标识、示例仪表盘、中英文等多语言支持、API 五层架构、AI 协作规则体系等通用能力，省去每个项目重复搭建后台的时间。

---

## 技术栈

| 组件 | 版本 |
| --- | --- |
| PHP | ^8.4 |
| Laravel | ^13.8 |
| Dcat-Admin | 2.0.x-dev (fork) |
| 数据库 | MySQL / SQLite（默认 MySQL） |
| 前端构建 | Vite + Laravel Vite 插件 |
| 队列 | database 驱动 |
| 缓存 | database 驱动 |

---

## 特性

- **开箱即用的仪表盘** — 预置 6 种示例指标卡片（折线图、柱状图、环形图、径向条图等），可直接复用或替换
- **自定义 Fork 维护** — 基于 [anyuhanfei/dcat_admin](https://github.com/anyuhanfei/dcat_admin) 持续跟进修复与改进
- **多语言就绪** — 已配置 zh_CN / en / zh_TW 多语言支持，模块语言文件自动生成
- **API 五层架构** — Controller / Service / Repository / Resource / Request 分层，统一 POST JSON 参数 + `success()` 响应格式
- **AI 协作规则体系** — 根目录 `AGENTS.md` 定义编码规范与协作流程，`.ai/` 目录管理技能与编码角色，支持多 AI 工具（Cursor / Claude Code / Reasonix 等）
- **认证控制器占位** — 继承 `AuthController` 的空类，方便后续扩展登录/登出逻辑
- **Laravel 13 最新骨架** — 使用最新的 Laravel 目录结构与配置规范
- **开发脚本** — 一条命令同时启动 HTTP 服务、队列监听、日志与 Vite 热更新

---

## 环境要求

- PHP >= 8.4
- Composer
- MySQL / MariaDB（或 SQLite）
- 扩展：`PDO`、`mbstring`、`openssl`、`tokenizer`、`json`、`xml`、`gd`（或 `imagick`）

---

## 安装

### 步骤

```bash
# 1. 克隆项目
git clone https://github.com/anyuhanfei/arc_dcat_admin.git
cd arc_dcat_admin

# 2. 复制环境配置
cp .env.example .env

# 3. 修改 .env 文件中的数据库连接信息及应用名称
#    数据库默认库名：arc_dcat_admin
#    APP_NAME、ADMIN_HTTPS 等按需调整

# 4. 安装 PHP 依赖
composer install

# 5. 生成应用密钥
php artisan key:generate

# 6. 发布 Dcat-Admin 静态资源
php artisan admin:publish

# 7. 安装 Dcat-Admin（创建数据库表并写入初始数据）
php artisan admin:install

# 8. 生成 AI 工具链接层（将 .ai/ 权威源映射到 .claude/.github/.agents/.reasonix）
./sync_ai_config.sh
```

---

## 目录结构说明

```
├── app/
│   ├── Admin/                        # Dcat-Admin 后台目录
│   │   ├── Controllers/             # 后台控制器（Grid/Form/Show）
│   │   │   ├── AuthController.php   # 认证控制器（可扩展）
│   │   │   └── HomeController.php   # 仪表盘首页控制器
│   │   ├── Repositories/            # 后台 Repository
│   │   ├── Form/Field/              # 自定义表单字段（如 MediaSelector）
│   │   ├── Actions/                 # 自定义动作按钮（RowAction/BatchAction）
│   │   ├── Metrics/Examples/        # 示例指标卡片组件
│   │   ├── bootstrap.php             # 后台启动文件（可注册扩展）
│   │   └── routes.php                # 后台路由
│   ├── Api/                          # API 层（五层架构）
│   │   ├── Controllers/{模块}/      # 控制器：收参→验证→调 Service→返回
│   │   ├── Services/{模块}/         # 业务层：业务逻辑，调 Repository
│   │   ├── Repositories/{模块}/     # 数据层：单表 CRUD + 多表联合查询
│   │   ├── Resources/{模块}/        # 输出层：字段转换、隐藏内部字段
│   │   └── Requests/{模块}/         # 验证层：参数验证（每个方法一个文件）
│   ├── Models/{模块}/                # 模型（API + Admin 共用）
│   ├── Enums/                        # PHP enum 类（状态/类型等固定枚举）
│   ├── Actions/{模块}/               # 独立业务 Action 类（状态机流转等，前后台共用）
│   ├── Events/                       # 事件类（事件解耦）
│   ├── Listeners/                    # 事件监听者（事件解耦）
│   ├── Jobs/                         # 队列任务（耗时操作）
│   ├── Services/                     # 跨模块服务（如 MessageService 消息中心）
│   ├── Integrations/{三方名}/        # 三方接口对接（短信/支付/OAuth 等）
│   ├── Messages/Templates/           # 消息内容模板类
│   ├── Console/Commands/             # Artisan 自定义命令
│   ├── Http/Controllers/             # 基础控制器
│   ├── Exceptions/                   # 异常类（BusinessException 等）
│   └── Providers/                    # 应用服务提供者
├── config/
│   └── admin.php                     # Dcat-Admin 配置（已定制）
├── database/
│   ├── migrations/                   # 数据库迁移
│   └── seeders/                      # 数据填充
├── resources/
│   └── lang/{locale}/{模块}.php      # 国际化语言文件（zh_CN / en 双语同步）
├── public/
│   └── static/
│       ├── logo.jpeg                 # Logo
│       └── logo2.jpeg                # Logo（深色/简约版）
├── routes/
│   ├── api.php                       # API 路由
│   ├── web.php                       # 前端路由
│   └── console.php                   # Artisan 命令路由
├── .ai/                              # AI 协作配置（技能 + 编码角色，权威源）
├── AGENTS.md                         # AI 协作规则（跨工具通用层）
├── CLAUDE.md                         # Claude Code 入口（@import AGENTS.md）
└── sync_ai_config.sh                 # AI 配置同步脚本（生成工具链接层）
```

> 其余目录（`bootstrap/`、`storage/`、`vendor/`、`tests/` 等）按 Laravel 框架标准目录使用。

---

## 配置说明

### 核心配置项（`.env`）

| 变量 | 说明 | 默认值 |
| --- | --- | --- |
| `APP_NAME` | 应用名称 | `ARC_DCAT_ADMIN` |
| `ADMIN_HTTPS` | 后台是否强制 HTTPS | `false` |
| `ADMIN_ROUTE_PREFIX` | 后台路由前缀 | `admin` |
| `DB_DATABASE` | 数据库名称 | `arc_dcat_admin` |

### 后台LOGO自定义（`config/admin.php`）

- **`name`** — 后台名称，显示在登录页
- **`logo`** — 后台 Logo（支持 HTML 标签）
- **`logo-mini`** — 侧栏收起时的简版 Logo
- **`favicon`** — 浏览器标签页图标

如需替换品牌图片，请将新图片放入 `public/static/` 目录，并更新上述配置项。

---

## AI 协作开发

本项目内置完整的 AI 协作规则体系，支持 Cursor、Claude Code、Reasonix、OpenAI Codex 等多种 AI 工具：

- **`AGENTS.md`** — 跨工具通用规则入口（编码规范、目录结构、命名规则、PHP 8.4/Laravel 13 约束、协作流程）
- **`.ai/skills/`** — 项目技能（编码索引、需求追问、子 Agent 编排、调试、验证等 8 个技能）
- **`.ai/agents/`** — 编码角色规范（API / Admin / Database / 三方对接 / 兜底，含代码模板）
- **`sync_ai_config.sh`** — 同步脚本，将 `.ai/` 权威源映射到各工具发现路径（`.claude/`、`.github/`、`.agents/`、`.reasonix/`）

新增技能或角色后，运行 `./sync_ai_config.sh` 同步到工具链接层。

---

## 自定义开发

### 添加新后台模块

在 `app/Admin/Controllers/` 下创建控制器、`app/Models/` 下创建模型、`app/Admin/Repositories/` 下创建仓库，然后在 `app/Admin/routes.php` 中注册路由：

```php
$router->resource('my-model', 'MyModelController');
```

### 添加新 API 接口

按 API 五层架构创建文件（详见 `AGENTS.md` 第五章与 `.ai/agents/agent-api.md`）：

1. `app/Api/Requests/{模块}/{方法名}Request.php` — 参数验证
2. `app/Api/Repositories/{模块}/{功能}Repository.php` — 数据查询
3. `app/Api/Services/{模块}/{功能}Service.php` — 业务逻辑
4. `app/Api/Resources/{模块}/{功能}Resource.php` — 输出转换
5. `app/Api/Controllers/{模块}/{功能}Controller.php` — 控制器
6. 在 `routes/api.php` 中注册路由

### 添加菜单及权限

在权限管理-菜单管理中，填写菜单标题等信息。（菜单标题需要填写英文，多单词使用 `-` 隔开）

在权限管理-规则管理中，填写规则标识、规则名称、http方法、http路径等信息，例如：

```
名称：协议配置管理；标识：sys-agreements
     名称：列表；标识：sys-agreements.list；http方法：GET；http路径：admin/sys/agreements
     名称：查看；标识：sys-agreements.view；http方法：GET；http路径：admin/sys/agreements/*
     名称：新增；标识：sys-agreements.create；http方法：GET、POST；http路径：admin/sys/agreements、admin/sys/agreements/create
     名称：编辑；标识：sys-agreements.edit；http方法：GET、PUT；http路径：admin/sys/agreements/*、admin/sys/agreements/*/edit
     名称：删除；标识：sys-agreements.delete；http方法：DELETE；http路径：admin/sys/agreements/*
```

### 多语言

多语言文件在 `resources/lang/` 目录下，按语言分目录（`zh_CN/`、`en/`、`zh_TW/`），每个模块一个 PHP 文件。

当使用代码生成器创建控制器、模型、仓库时，会自动生成该模块的多语言文件。

在模块对应的语言文件的 `labels` 中，添加菜单标题同名的 key，并设置对应语言的文本。（为了页面面包屑导航的文本翻译）

在 `menu.php` 中，添加菜单标题同名的 key，并设置对应语言的文本。（为了左侧边栏导航的文本翻译）

---

## Swagger API 文档

1. 生成 JSON 文件：`php artisan l5-swagger:generate`
2. 访问文档：`http://your-domain/docs`

API 方法需使用 Swagger 注解（`@OA\Post` / `@OA\Response` 等 OpenApi 注解）标注，详见 `.ai/agents/agent-api.md`。

---

## 常见问题

**Q: 安装后访问后台 404？**
A: 确保已执行 `php artisan admin:install` 且 Web 服务器 URL 重写配置正确（Nginx 需配置 `try_files` 指向 `public/index.php`）。

**Q: 如何修改后台路由前缀？**
A: 修改 `.env` 中的 `ADMIN_ROUTE_PREFIX` 变量，例如 `ADMIN_ROUTE_PREFIX=backend`。

**Q: 如何使用 SQLite 快速测试？**
A: 修改 `.env` 中 `DB_CONNECTION=sqlite`，并创建 `database/database.sqlite` 文件。

**Q: 上传文件大小受限？**
A: 需同时配置代码层（Dcat WebUploader `fileSingleSizeLimit`）、Nginx 层（`client_max_body_size`）和 PHP 层（`upload_max_filesize` / `post_max_size`），详见 `tmp/upload_size_limit_record_20260825.md`。

---

## 致谢

- [Dcat-Admin](https://github.com/jqhph/dcat-admin) — 优雅的 Laravel 后台管理框架
- [Laravel](https://laravel.com) — Web 工匠框架

---

## License

MIT
