# Agent：后台管理 Agent

**负责 Dcat-Admin 后台 CRUD 的完整生成（控制器 + Repository + Grid/Form/Show）。**

> **技术栈固定**：Dcat Admin 组件约定（mediaSelector、ueditor、语言文件 enums）为模板固定标准。

## 前置必读

- **AGENTS.md「规则 3」** — 所有编码任务派发子 Agent 执行
- **[agent-api.md](agent-api.md)** — API 层结构（了解整体模块划分，后台与 API 共用 Model）
- **[agent-database.md](agent-database.md)** — 数据库规范（Model 缺失时调此 Agent）

### 后台编码规则（本 Agent 专属）

- 后台使用 Dcat-Admin 内置组件（`AdminController`、`EloquentRepository`、`Grid`、`Form`、`Show`）
- Model 复用 `app/Models/`，不为 Admin 创建独立 Model
- 禁止 `DB::` 直接查询，所有数据操作通过 Repository/Model
- 枚举值定义用 PHP enum 类（`app/Enums/`，见 agent-database.md「PHP enum 类」）；后台展示文本与表单选项统一取语言文件 enums（`__('模块.enums.字段')`），禁止在 Controller 硬编码说明数组（详见「后台管理功能规则」规则 3）

## 职责

- 根据数据表结构生成 Dcat-Admin 后台管理模块
- 使用 Dcat-Admin 内置组件（Grid、Form、Show、Filter）构建管理界面
- 处理关联查询、搜索条件、状态枚举等常见模式
- 路由注册到 `app/Admin/routes.php`

## 生成的文件

| 层 | 路径 | 说明 |
|---|------|------|
| Controller | `app/Admin/Controllers/{模块}/{功能}Controller.php` | 继承 `AdminController` |
| Repository | `app/Admin/Repositories/{模块}/{功能}.php` | 继承 `EloquentRepository`，指向 Model |
| Route | `app/Admin/routes.php` | 注册资源路由 |

## 代码模板

### Controller 模板

```php
<?php

namespace App\Admin\Controllers\{模块};

use App\Admin\Repositories\{模块}\{功能};
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class {功能}Controller extends AdminController
{
    /**
     * Grid 表格
     */
    protected function grid(): Grid
    {
        return Grid::make(new {功能}(), function (Grid $grid) {
            $grid->column('id', admin_trans_field('id'))->sortable();
            $grid->column('title', admin_trans_field('title'));
            $grid->column('image', admin_trans_field('image'))->image('', 60, 60);
            $grid->column('status', admin_trans_field('status'))->using(__('{模块}.enums.status'))->dot([0 => 'default', 1 => 'success']);
            $grid->column('created_at', admin_trans_field('created_at'));
            $grid->column('updated_at', admin_trans_field('updated_at'))->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('title', admin_trans_field('title'));
                $filter->equal('status', admin_trans_field('status'))->select(__('{模块}.enums.status'));
                $filter->between('created_at', admin_trans_field('created_at'))->datetime();
            });
        });
    }

    /**
     * Show 详情
     */
    protected function detail($id): Show
    {
        return Show::make($id, new {功能}(), function (Show $show) {
            $show->field('id', admin_trans_field('id'));
            $show->field('title', admin_trans_field('title'));
            $show->field('image', admin_trans_field('image'))->image();
            $show->field('content', admin_trans_field('content'))->unescape();
            $show->field('status', admin_trans_field('status'))->using(__('{模块}.enums.status'));
            $show->field('created_at', admin_trans_field('created_at'));
            $show->field('updated_at', admin_trans_field('updated_at'));
        });
    }

    /**
     * Form 表单
     */
    protected function form(): Form
    {
        return Form::make(new {功能}(), function (Form $form) {
            $form->text('title', admin_trans_field('title'))->required();
            $form->mediaSelector('image', admin_trans_field('image'));
            $form->ueditor('content', admin_trans_field('content'));
            $form->select('status', admin_trans_field('status'))->options(__('{模块}.enums.status'))->default(1);
            $form->datetime('publish_at', admin_trans_field('publish_at'));
        });
    }
}
```

### Repository 模板

```php
<?php

namespace App\Admin\Repositories\{模块};

use App\Models\{模块}\{模型名};
use Dcat\Admin\Repositories\EloquentRepository;

class {功能} extends EloquentRepository
{
    protected $eloquentClass = {模型名}::class;
}
```

### 路由注册

在 `app/Admin/routes.php` 中添加：

```php
$router->resource('{模块}/{资源名}', App\Admin\Controllers\{模块}\{功能}Controller::class);
```

## Dcat-Admin 常用字段组件速查

| 场景 | Form 方法 | Grid 显示 | 说明 |
|------|-----------|----------|------|
| 文本输入 | `$form->text('field')` | `$grid->column('field')` | 默认文本 |
| 长文本 | `$form->textarea('field')` | — | 多行文本 |
| 富文本 | `$form->ueditor('field')` | `$grid->column('content')->limit(50)` | 百度 UEditor 插件（weiaibaicai/ueditor），见规则 2 |
| 图片 | `$form->mediaSelector('field')` | `$grid->column('image')->image('', 60, 60)` | 媒体库选择（规则 1），禁止 image() 上传组件 |
| 文件 | `$form->mediaSelector('field')` | `$grid->column('file')` | 媒体库选择（规则 1），禁止 file() 上传组件 |
| 开关 | `$form->switch('status')->default(1)` | `$grid->column('status')->switch()` | 仅限布尔字段（0/1 开/关语义），多值枚举用下拉 |
| 下拉 | `$form->select('field')->options(__('{模块}.enums.field'))` | `$grid->column('field')->using(__('{模块}.enums.field'))` | 枚举选择，说明文本取语言文件（规则 3） |
| 多选 | `$form->multipleSelect('tags')->options([...])` | `$grid->column('tags')->label()` | 多选标签 |
| 日期 | `$form->date('field')` | `$grid->column('created_at')->sortable()` | 日期选择 |
| 日期时间 | `$form->datetime('field')` | `$grid->column('created_at')->sortable()` | 日期+时间 |
| 数字 | `$form->number('sort')->default(0)` | `$grid->column('sort')->sortable()->editable()` | 数字输入 |
| 颜色 | `$form->color('color')->default('#5b8ff9')` | `$grid->column('color')` | 颜色选择器 |

## 远程数据选择器规范

选择用户、商品、订单等数据量大（>100 条）的关联数据，用远程数据选择器，禁止一次性加载全部选项：

| 场景 | 组件 | 说明 |
|------|------|------|
| 单选（选一个用户/商品） | `$form->select('user_id')->options(...)` + ajax 远程搜索 | 配置 `->ajax('/api/admin/users/search')` 接口，输入关键词时远程加载 |
| 多选（选多个用户） | `$form->multipleSelect('user_ids')->options(...)` + ajax | 同上，支持多选 |
| 表格选择（需要看更多列信息） | `$form->selectTable('user_id')` | Dcat Admin 弹窗表格选择器，展示多列（用户名/手机号/注册时间），支持搜索和分页 |

- **选择器搜索接口**：在 `app/Admin/Controllers/` 下创建搜索方法（如 `UserController@search`），返回 `['id' => xxx, 'text' => '用户名(手机号)']` 格式
- **多选 ID 不存主表**：选择多个用户发送消息时，用户 ID 不存消息表 JSON 字段，直接创建 `sys_message_users` 关联记录（见 agent-database.md「延迟创建关联记录」）
- **选择器展示列**：selectTable 展示列在 Controller 中定义（Grid 列配置），搜索字段用 `name`/`phone` 等高频字段

### Grid 自定义计算列规范

Grid 中需要根据关联数据或业务逻辑计算展示值的列（如消息发送范围、订单商品数、用户累计消费），用 `display()` 闭包：

```php
// 消息发送范围：单用户显示用户名，多用户显示"已发送 X 人"，全部显示"全部用户"
$grid->column('send_scope', admin_trans_field('send_scope'))->display(function () {
    if ($this->select_type === MessageSelectType::All->value) {
        return '<span class="label label-primary">全部用户</span>';
    }
    $count = $this->messageUsers->count();
    if ($count === 1) {
        return $this->messageUsers->first()->user->nickname ?? '未知用户';
    }
    return "已发送 {$count} 人";
});
```

- **闭包内用 `$this->` 访问当前行 Model**：`$this->relation` 访问关联，`$this->field` 访问字段
- **避免 N+1**：Repository 中用 `with('relation')` 预加载关联数据，Grid display 闭包内不再查询
- **复杂计算放 Model 访问器**：如果计算逻辑在多处复用（如订单状态文本），在 Model 中定义访问器（PHP 8.4 属性钩子），Grid 直接 `$grid->column('status_text')`
- **display 闭包只做展示，不做写入**：禁止在 display 闭包内修改数据库或调用外部 API

## 后台常用函数速查

以下函数定义在 `app/helpers.php` 中，生成后台代码时可直接使用：

### 用户头像与信息展示

在 Grid 的 user_id 字段中使用 `display()` 调用：

```php
$grid->column('user_id')->display(fn ($v) => admin_grid_user_field($this->user));
```

### 富文本内容弹窗

```php
$grid->column('content')->display(fn ($v) => admin_grid_content($this->content));
```

### 媒体选择表单

```php
$form->mediaSelector('images', admin_trans_field('images'))->pipe(fn ($v) => admin_form_media_selector_field($v, 5, ['image']));
```

### 数字输入限制

```php
$form->text('price', admin_trans_field('price'))->pipe(fn ($v) => admin_form_number_field($v, '0.01'));
```

## 存储/上传规范

- 媒体文件字段表单统一走 `$form->mediaSelector()`（媒体库选择，规则 1），落盘位置与磁盘按 `config/admin.php` 实际配置（Dcat 默认 uploads 目录）
- 展示时用 `full_url($path)` 拼接完整 URL（该函数定义在 `app/helpers.php`，跨环境自动适配域名）
- 媒体字段的类型/大小限制由媒体库选择界面统一处理（规则 1）；非媒体字段（文本/数字等）如需校验仍可用 `rules()`，示例：`$form->text('field')->rules('max:255')`

## 富文本 XSS 提示

- `$form->ueditor()` 内容在 Grid/前端展示时注意转义或白名单过滤，避免存储型 XSS

## 自定义动作按钮（Dcat Admin Action）

状态流转、批量操作、审核等非标准 CRUD 动作，用 Dcat Admin 自定义 Action 类实现，不写在 Controller 的 grid() 回调里。

### 适用场景
- 状态流转（开始处理/处理完成/搁置/恢复）
- 批量操作（批量审核/批量删除/批量导出）
- 审核动作（通过/驳回）
- 其他触发业务逻辑的按钮

### Action 类模板

```php
<?php

namespace App\Admin\Actions\{模块};

use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Illuminate\Http\Request;

class {动作名} extends RowAction
{
    protected $title = '{按钮文本}';

    public function handle(Request $request): Response
    {
        // $this->getKey() 获取行 ID
        $id = $this->getKey();

        // 执行业务逻辑（调用 Model 方法或独立 Action 类）
        // ...

        return $this->response()->success('操作成功')->refresh();
    }

    public function confirm(): string
    {
        return '确认执行此操作？'; // 需要确认时返回提示文本，不需要确认则删除此方法
    }
}
```

### Grid 中注册

```php
$grid->actions(function (Grid\Displayers\Actions $actions) {
    $actions->append(new {动作名}());
});
```

### 规范
- Action 类只负责接收请求 + 调用业务逻辑 + 返回响应，不写复杂业务代码
- 业务逻辑（状态流转、数据更新）调用 Model 方法或独立 Action 类（与 API 共用，见 agent-database.md 行为准则「状态机共用逻辑」）
- 危险操作（删除/状态终态变更）必须加 `confirm()` 确认
- 批量操作用 `BatchAction` 基类，单行操作用 `RowAction` 基类

## 后台管理功能规则

### 规则 1：媒体文件字段

- **适用**：字段为图片、多图片、音频、视频、文件、多文件等媒体类型
- **表单**：统一用媒体选择字段 `$form->mediaSelector('字段', admin_trans_field('字段'))`（基于媒体库的自定义字段，实现见 `app/Admin/Form/Field/MediaSelector.php`），**禁止** `$form->text()` 手填路径、**禁止**原生 `$form->image()/file()` 上传组件散落使用

```php
$form->mediaSelector('avatar', admin_trans_field('avatar'));
```

- **列表**：图片字段展示缩略图（参考用户管理头像列写法）：

```php
$grid->column('avatar', admin_trans_field('avatar'))->display(fn ($v) => $v ? '<img src="'.e(full_url($v)).'" style="height:40px;border-radius:4px;">' : '');
```

- **注**：MediaSelector 当前为单值字段（存路径字符串）；多图片/多文件等多值需求需先扩展 MediaSelector 支持数组存储，按模块实际设计后再使用

### 规则 2：富文本字段

- **适用**：content 类长文本富文本字段
- **表单**：统一用百度 UEditor 插件 `$form->ueditor('字段', admin_trans_field('字段'))`（weiaibaicai/ueditor 已注册），**禁止** `$form->textarea()` 或 `$form->text()` 承载富文本

```php
$form->ueditor('content', admin_trans_field('content'));
```

- **详情/Show**：富文本字段必须 `->unescape()`，否则 HTML 被转义：

```php
$show->field('content')->unescape();
```

- **列表**：富文本字段不整段展示，用 `->limit(30)` 截断或隐藏

### 规则 3：枚举字段

- **适用**：语言文件 enums 中有映射说明的字段（状态/类型/性别等）
- 枚举说明**统一从语言文件引用** `__('模块.enums.字段')`，**禁止**在 Controller 硬编码说明数组
- **列表**：展示说明文本，不得直接展示原始枚举数字：

```php
$grid->column('gender', admin_trans_field('gender'))->using(__('users.enums.gender'))->label([0 => 'default', 1 => 'primary', 2 => 'pink']);
```

- **表单**：选项少用 `$form->radio('字段', admin_trans_field('字段'))->options(__('模块.enums.字段'))->default(...)`，选项多用 `$form->select(...)->options(...)`；**禁止** `$form->text()` 承载枚举字段

```php
$form->radio('is_show', admin_trans_field('is_show'))->options(__('sys-faq-questions.enums.is_show'))->default(1);
```

- **筛选**：

```php
$filter->equal('status', admin_trans_field('status'))->select(__('users.enums.status'));
```

- 与 agent-database.md「状态/枚举字段规范」呼应：数据库层 `tinyInteger` + PHP enum 类定义枚举值，但**后台展示/选项统一取语言文件 enums**（说明文本随语言切换），不直接用 `Enum::cases()` 硬编码中文

### 规则 4：国际化

- **支持语言**：zh_CN（简体中文）与 en（英文）两套**同步维护**；zh_TW（繁体）暂不同步、不新增
- **语言文件**：`resources/lang/{locale}/{模块}.php`，三键结构：
  - `fields`：字段名
  - `enums`：枚举说明
  - `labels`：模块标题
- 字段标签一律 `admin_trans_field('字段')`；枚举说明一律 `__('模块.enums.字段')`；模块标题取语言文件 `labels` 键
- **禁止**在 Controller/View/JS 中硬编码界面文案（中文或英文都禁止；如 FAQ 现有 filter 里的 `'分类'`、`'问题'` 即违反项，不复制该写法）
- 新增模块/字段/枚举时，必须同时补 zh_CN 与 en 两套语言文件，缺一不可

以上 4 条为后台模块开发时的字段与文案标准，违反视为缺陷（现有模块中不符合处后续迭代修正）。

## 行为准则

| 规则 | 说明 |
|------|------|
| Controller 继承 `AdminController` | 不要继承其他基类 |
| Repository 继承 `EloquentRepository` | 只需声明 `$eloquentClass` |
| Model 复用 `app/Models/` | 不要为 Admin 创建独立 Model |
| 不使用 `DB::` 直接查询 | 所有数据操作通过 Repository/Model |
| 路由注册到 `app/Admin/routes.php` | 不要写到 `routes/web.php` 中 |
| 新增模块后需添加菜单 | 创建完成后提示用户在后台菜单管理中添加 |
| 发现 Model 缺失 | 先创建迁移和 Model，参考 `app/Models/Sys/SysAgreements.php` 风格 |
