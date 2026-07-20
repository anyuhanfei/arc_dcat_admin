# ARC-Dcat-Admin

> 基于 [Dcat-Admin](https://github.com/jqhph/dcat-admin) 二次开发的后台管理框架脚手架，预置了项目通用配置与功能模块，开箱即用，便于快速启动新项目。

本项目是 **Dcat-Admin 2.0.x** 的增强版骨架，使用维护中的 [个人 Fork 版本](https://github.com/anyuhanfei/dcat_admin) 作为底层依赖，并预先配置了品牌标识、示例仪表盘、中英文等多语言支持等通用能力，省去每个项目重复搭建后台的时间。

---

## 技术栈

| 组件 | 版本 |
| --- | --- |
| PHP | ^8.3 |
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
- **多语言就绪** — 已配置中英文等多语言支持
- **认证控制器占位** — 继承 `AuthController` 的空类，方便后续扩展登录/登出逻辑
- **Laravel 13 最新骨架** — 使用最新的 Laravel 目录结构与配置规范
- **开发脚本** — 一条命令同时启动 HTTP 服务、队列监听、日志与 Vite 热更新

---

## 安装

### 环境要求

- PHP >= 8.3
- Composer
- MySQL / MariaDB（或 SQLite）
- 扩展：`PDO`、`mbstring`、`openssl`、`tokenizer`、`json`、`xml`、`gd`（或 `imagick`）

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
```
## 目录结构说明

```
├── app/
│   ├── Admin/                        # Dcat-Admin 后台目录
│   │   ├── Controllers/
│   │   │   ├── AuthController.php    # 认证控制器（可扩展）
│   │   │   └── HomeController.php    # 仪表盘首页控制器
│   │   ├── Metrics/Examples/         # 示例指标卡片组件
│   │   │   ├── Donut.php             # 环形图
│   │   │   ├── Line.php              # 折线图
│   │   │   ├── Bar.php               # 柱状图
│   │   │   ├── RadialBar.php         # 径向条图
│   │   │   ├── Round.php             # 圆环进度
│   │   │   └── Card.php              # 统计卡片
│   │   ├── bootstrap.php             # 后台启动文件（可注册扩展）
│   │   └── routes.php                # 后台路由
│   ├── Http/Controllers/
│   │   └── Controller.php            # 基础控制器
│   ├── Models/
│   │   └── User.php                  # 前端用户模型
│   └── Providers/
│       └── AppServiceProvider.php    # 应用服务提供者
├── config/
│   └── admin.php                     # Dcat-Admin 配置（已定制）
├── database/
│   └── migrations/                   # 数据库迁移
├── public/
│   └── static/
│       ├── logo.jpeg                 # 品牌 Logo
│       └── logo2.jpeg                # 品牌 Logo（深色/简约版）
└── routes/
    ├── web.php                       # 前端路由
    └── console.php                   # Artisan 命令路由
```

---

## 配置说明

### 核心配置项（`.env`）

| 变量 | 说明 | 默认值 |
| --- | --- | --- |
| `APP_NAME` | 应用名称 | `ARC_DCAT_ADMIN` |
| `ADMIN_HTTPS` | 后台是否强制 HTTPS | `false` |
| `ADMIN_ROUTE_PREFIX` | 后台路由前缀 | `admin` |
| `DB_DATABASE` | 数据库名称 | `arc_dcat_admin` |

### 后台品牌自定义（`config/admin.php`）

- **`name`** — 后台名称，显示在登录页
- **`logo`** — 后台 Logo（支持 HTML 标签）
- **`logo-mini`** — 侧栏收起时的简版 Logo
- **`favicon`** — 浏览器标签页图标

如需替换品牌图片，请将新图片放入 `public/static/` 目录，并更新上述配置项。

---

## 自定义开发

### 添加新后台控制器

在 `app/Admin/Controllers/` 下创建控制器，并在 `app/Admin/routes.php` 中注册路由：

```php
$router->resource('my-model', 'MyModelController');
```

### 添加业务模型

在 `app/Models/` 下创建 Eloquent 模型，然后在后台控制器中使用 Dcat-Admin 的 Grid 和 Form 构建管理界面。

### 多语言

Laravel 默认语言文件位于 `lang/` 目录，Dcat-Admin 的语言包可通过发布资源后自定义。

---

## swagger
1. 生成json文件：php artisan l5-swagger:generate
2. 访问json文件：http://your-domain/docs


## 常见问题

**Q: 安装后访问后台 404？**  
A: 确保已执行 `php artisan admin:install` 且 Web 服务器 URL 重写配置正确（Nginx 需配置 `try_files` 指向 `public/index.php`）。

**Q: 如何修改后台路由前缀？**  
A: 修改 `.env` 中的 `ADMIN_ROUTE_PREFIX` 变量，例如 `ADMIN_ROUTE_PREFIX=backend`。

**Q: 如何使用 SQLite 快速测试？**  
A: 修改 `.env` 中 `DB_CONNECTION=sqlite`，并创建 `database/database.sqlite` 文件。

---

## 致谢

- [Dcat-Admin](https://github.com/jqhph/dcat-admin) — 优雅的 Laravel 后台管理框架
- [Laravel](https://laravel.com) — Web 工匠框架

---

## License

MIT
