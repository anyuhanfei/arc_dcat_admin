# Agent：数据库迁移/填充 Agent

**负责数据库表结构变更（迁移文件）和数据填充（Seeder 文件）的生成。**

## 前置必读

- **AGENTS.md「规则 3」** — 所有编码任务派发子 Agent 执行
- **本项目的迁移文件风格**：查看 `database/migrations/` 下的现有迁移，保持风格一致
- **本项目的 Model 风格**：查看 `app/Models/` 下的现有 Model，保持风格一致

## 职责

- 分析用户需求中的表结构，生成 Laravel 迁移文件
- 按需生成 Seeder 数据填充文件
- 表结构变更时同步更新 `app/Models/` 下的对应 Model
- 遵循 Laravel 迁移规范 + 本项目已有迁移的风格

## 生成的文件

| 场景 | 路径 |
|------|------|
| 新建表 | `database/migrations/{时间戳}_create_{表名}_table.php` |
| 修改表 | `database/migrations/{时间戳}_alter_{表名}_table.php` |
| 数据填充 | `database/seeders/{表名PascalCase}Seeder.php` |
| Model 更新 | `app/Models/{模块}/{模型名}.php` |

## 迁移文件风格规范

严格按照本项目已有迁移（如 `2025_01_01_000001_create_sys_agreements_table.php`）的风格：

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 新建表用 Schema::create；修改表用 Schema::table，加字段前用 Schema::hasColumn 做幂等保护
        Schema::create('{表名}', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            // 业务字段 — 每个字段必须有 ->comment('中文说明')
            $table->string('title', 255)->default('')->comment('标题');
            $table->longText('content')->nullable()->comment('内容');
            $table->timestamps();
            $table->softDeletes();

            $table->comment('表说明');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{表名}');
    }
};
```

### 字段类型速查

| 场景 | Blueprint 方法 | 示例 |
|------|---------------|------|
| 主键自增 | `$table->increments('id')` | int auto_increment |
| 字符串 | `$table->string('col', 255)` | varchar(255) |
| 长文本 | `$table->longText('col')->nullable()` | longtext |
| 文本 | `$table->text('col')->nullable()` | text |
| 整数 | `$table->integer('col')->default(0)` | int |
| 小整数 | `$table->tinyInteger('col')->default(0)` | tinyint |
| 布尔（仅二值开/关语义字段） | `$table->boolean('col')->default(false)` | tinyint(1) |
| 金额 | `$table->decimal('col', 10, 2)->default(0)` | decimal(10,2) |
| 时间戳 | `$table->timestamp('col')->nullable()` | timestamp null |
| 外键 | `$table->foreign('user_id')->references('id')->on('users')` | 带索引的外键 |
| JSON 数组 | `$table->json('col')->nullable()` + `casts => 'array'` | 存储数组/对象（如图片 URL 列表、扩展字段），不用 longText 手动 JSON |

### 状态/枚举字段规范

> **总览**：固定枚举必须用 PHP enum 类（见下文「PHP enum 类」），数据库字段用 tinyInteger 或 string；禁止使用 Model const + STATUS_MAP 方式。后台展示统一用语言文件 enums（见 agent-admin.md 规则 3），enum 类的 `label()` 方法可作为语言文件的补充。

- 状态/枚举字段**统一用 `tinyInteger`**，示例：`$table->tinyInteger('status')->default(0)->comment('状态：0=关闭 1=启用');`
- **禁止 `$table->enum()`**：enum 加值需 ALTER 表结构，维护成本高
- 二值开/关语义字段用 `boolean` + `is_` 前缀命名（如 `is_enabled`）
- 后台与 API 均通过 Enum 类访问枚举值（Model `$casts` 自动转换 / `Enum::cases()` 生成选项 / `$enum->value` 取值），禁止在控制器/服务中硬编码数组（硬编码规范详见 agent-admin.md 规则 3 / agent-api.md 注意事项「状态/枚举字段」）

### PHP enum 类（必须）

固定枚举（状态/类型/选择方式等取值不再变化的字段）**必须用 PHP enum 类**（`app/Enums/`）：

```php
// app/Enums/MessageSelectType.php
enum MessageSelectType: int
{
    case Custom = 0;   // 自定义选择
    case All = 1;      // 全部用户

    public function label(): string
    {
        return match ($this) {
            self::Custom => '自定义选择',
            self::All => '全部用户',
        };
    }
}
```

- enum 用 `int` 或 `string` backed type，数据库字段类型与 backed type 一致（int → `tinyInteger`，string → `string`）
- Model `$casts` 声明 `'select_type' => MessageSelectType::class`，Eloquent 自动在 enum ↔ 数据库值之间转换
- 后台选项用 `array_column(MessageSelectType::cases(), 'label', 'value')` 生成
- API 返回：`'select_type' => $this->select_type->value`，如需 label 加 `'select_type_label' => $this->select_type->label()`
- **规则为权威，代码向规则看齐**：固定枚举必须用 enum 类，禁止使用 const + STATUS_MAP 方式

### 修改表模板

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('{表名}', function (Blueprint $table) {
            // 幂等保护：加字段前判断是否已存在，避免重复执行迁移报错
            if (! Schema::hasColumn('{表名}', 'new_field')) {
                $table->string('new_field', 255)->default('')->comment('新字段');
            }
            // 修改字段：$table->string('old_field', 500)->change()->comment('修改后');
            // 删除字段：$table->dropColumn('old_field');
            // 加索引：$table->index('field');
        });
    }

    public function down(): void
    {
        Schema::table('{表名}', function (Blueprint $table) {
            $table->dropColumn('new_field');
        });
    }
};
```

## Seeder 模板

```php
<?php

namespace Database\Seeders;

use App\Models\{模块}\{模型名};
use Illuminate\Database\Seeder;

class {表名PascalCase}Seeder extends Seeder
{
    public function run(): void
    {
        {模型名}::create([
            'title'   => '示例标题',
            'content' => '示例内容',
        ]);
    }
}
```

## Model 更新规范

- 新建表时 → 创建 `app/Models/{模块}/{模型名}.php`
- 修改表时 → 更新对应 Model（加字段、关联方法、获取器、scope、cast 等）
- Model 风格参照 `app/Models/Sys/SysAgreements.php`：

```php
<?php

namespace App\Models\{模块};

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class {模型名} extends Model
{
    use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = '{表名}';

    /** 可批量赋值字段 */
    protected $fillable = ['title', 'content', 'status'];

    /** 字段类型转换 */
    protected $casts = [
        'status'     => 'integer',
        'is_enabled' => 'boolean',
        'extra'      => 'array',
    ];
}
```

### Model 查询 Scope 规范

- **通用查询 scope 定义在 Model**（如 `scopeIsShow`、`scopeByPlatform`、`scopeOrdered`），Repository 只做组合调用
- scope 方法名用小驼峰，调用时 `Model::isShow()->get()`
- 业务组合查询（多表关联、复杂条件）放 Repository，不放 Model

```php
// Model 中定义通用 scope
public function scopeIsShow($query)
{
    return $query->where('is_show', 1);
}

public function scopeByPlatform($query, string $platform)
{
    return $query->where('platform', $platform);
}

// Repository 中组合调用
public function latestByPlatform(string $platform)
{
    return {模型名}::byPlatform($platform)->isShow()->orderBy('version_code', 'desc')->first();
}
```

### 复杂查询规范（UNION / 多源合并）

消息列表、订单统计等需要合并多个数据源的查询，按以下规范组织：

- **Repository 返回 QueryBuilder**：每个数据源一个 Repository 方法，返回 `EloquentBuilder`（不 `->get()`），Service 层合并
- **UNION 后统一分页**：两个 QueryBuilder 用 `->union($secondQuery)` 合并后，在外层包一层 `->orderBy('created_at', 'desc')->paginate($pagesize)`，禁止分别分页后在 PHP 层合并排序
- **无关联记录用 LEFT JOIN + COALESCE**：全部用户消息可能还没有关联记录，已读状态用 `LEFT JOIN message_users ON ...` + `COALESCE(message_users.is_read, 0)` 默认未读
- **关联条件查询用 whereRelation**：Laravel 13 用 `Model::whereRelation('relation', 'status', 1)`，比 `whereHas` 更简洁
- **复杂查询注释说明**：Repository 方法顶部注释说明查询目的、数据源、合并逻辑，便于后续维护

```php
// Repository：两个数据源各返回 QueryBuilder
public function queryUserMessages(int $userId): EloquentBuilder
{
    return Message::query()
        ->join('message_users', 'messages.id', '=', 'message_users.message_id')
        ->where('message_users.user_id', $userId);
}

public function queryAllMessages(): EloquentBuilder
{
    return Message::query()->where('select_type', MessageSelectType::All->value);
}

// Service：UNION 后统一分页
public function messageList(int $userId, int $page, int $pagesize): array
{
    $userMessages = $this->repository->queryUserMessages($userId);
    $allMessages = $this->repository->queryAllMessages();

    $paginator = $userMessages->union($allMessages)
        ->orderBy('created_at', 'desc')
        ->paginate($pagesize, ['*'], 'page', $page);

    return format_paginated_datas($paginator, ['*'], fn ($item) => [...]);
}
```

### PHP 8.4 属性钩子（访问器/修改器）

- Model 的字段访问器/修改器必须用 **PHP 8.4 属性钩子**，不写 Laravel `Attribute::make()` 或旧的 `getXxxAttribute()` / `setXxxAttribute()` 方法

```php
// PHP 8.4 属性钩子
class Order extends Model
{
    public int $amount_fen {
        get => $this->attributes['amount_fen'] ?? 0;
        set => $this->attributes['amount_fen'] = (int) $value;
    }
}
```

## Eloquent 关联方法规范

在 Model 中定义关联关系，方法名使用小驼峰：

| 关联类型 | 方法示例 | 说明 |
|---------|---------|------|
| 一对一 | `public function phone()` | `$this->hasOne(Phone::class)` |
| 一对多 | `public function posts()` | `$this->hasMany(Post::class)` |
| 反向一对多 | `public function user()` | `$this->belongsTo(User::class)` |
| 多对多 | `public function roles()` | `$this->belongsToMany(Role::class)` |
| 多态关联 | `public function relation()` | `$this->morphTo('relation')`；反向 `$this->morphMany(Comment::class, 'commentable')` |

```php
// 关联示例
public function category()
{
    return $this->belongsTo(Category::class);
}

public function tags()
{
    return $this->belongsToMany(Tag::class);
}
```

### 多态关联规范

消息跳转、评论、附件等"一个字段关联多种业务表"的场景用多态关联：

- 多态字段：`relation_type`（存短名如 `order`/`feedback`，不存完整类名）+ `relation_id`
- 在 `app/Providers/AppServiceProvider.php` 的 `boot()` 中注册 morphMap：`Relation::enforceMorphMap(['order' => Order::class, 'feedback' => Feedback::class])`
- Model 中定义：`public function relation() { return $this->morphTo('relation'); }`
- 反向关联：`public function messages() { return $this->morphMany(Message::class, 'relation'); }`
- API Resource 中根据类型条件加载关联：`'relation' => $this->whenLoaded('relation', fn() => [...])`
- `relation_owner_id`（关联信息所属用户ID）用于跳转时校验资源归属或展示对方信息，不参与多态关联本身

## Seeder 注册

创建好 Seeder 文件后，必须在 `database/seeders/DatabaseSeeder.php` 中注册：

```php
public function run(): void
{
    $this->call([
        // ...
        SysAgreementsSeeder::class,
    ]);
}
```

## 脚手架命令速查

| 命令 | 用途 |
|------|------|
| `php artisan make:migration create_xxx_table` | 生成迁移文件 |
| `php artisan make:model Models/Sys/Xxx` | 生成 Model |
| `php artisan make:seeder XxxSeeder` | 生成 Seeder |
| `php artisan migrate` | 执行迁移 |
| `php artisan db:seed --class=XxxSeeder` | 执行指定 Seeder |
| `php artisan migrate:rollback` | 回滚上一次迁移 |

## 行为准则

| 规则 | 说明 |
|------|------|
| 风格一致 | 迁移文件用匿名类、`->comment()` 中文说明、`increments('id')` 主键 |
| 每个字段必加 comment | 数据库字段必须有中文注释 |
| 修改表必须可回滚 | `down()` 方法必须正确回滚 `up()` 的变更 |
| 迁移幂等 | 修改表加字段前用 `Schema::hasColumn()` 判断，避免重复执行报错；新建表用 `Schema::hasTable()` |
| Seeder 用 Model 操作 | 不直接写 SQL，通过 Eloquent Model 插入数据 |
| Seeder 需注册 | 创建后需在 `DatabaseSeeder.php` 中 `$this->call()` |
| 同步更新 Model | 表结构变更后，必须同步更新 `app/Models/` 下的文件，并对新增/修改的 Model 运行 `php -l` 语法检查 |
| Model scope 归属 | 通用查询 scope（is_show/by_platform/排序）定义在 Model；业务组合查询放 Repository |
| 访问器用属性钩子 | Model 字段访问器/修改器必须用 PHP 8.4 属性钩子，不写 Laravel `Attribute::make()` 或旧的 `getXxxAttribute()` |
| Model 必须声明 fillable/casts | 新建 Model 必须声明 `$fillable`（可批量赋值字段）和 `$casts`（字段类型转换），禁止裸 Model |
| softDeletes 使用场景 | 用户业务数据（反馈/订单/消息/评论）默认用 softDeletes；系统配置表、字典表、日志表不用 |
| 状态机共用逻辑 | 有状态流转的模块（反馈/订单/审核），状态流转逻辑（允许流转矩阵、自动字段更新）放 Model 方法或独立 Action 类（`app/Actions/`），前后台共用，禁止在 Controller 重复实现 |

## 高级模式规范

### 延迟创建关联记录（lazy creation）

全部用户消息、公共公告等"用户阅读时才创建关联记录"的场景：

- **标记已读用 updateOrCreate / firstOrCreate**：`MessageUser::updateOrCreate(['message_id' => $id, 'user_id' => $userId], ['is_read' => 1])`
- **必须建联合唯一索引**：迁移中 `$table->unique(['message_id', 'user_id'])`，防止并发重复创建
- **批量标记已读用 upsert**：`MessageUser::upsert($data, ['message_id', 'user_id'], ['is_read'])`，禁止循环 firstOrCreate
- **全部已读用时间戳字段**：用户表加 `messages_read_at` 时间戳，标记全部已读时更新该字段；查询未读时加 `where('messages.created_at', '>', $user->messages_read_at)`，比批量创建关联记录高效

### 关联数据一致性（主表状态变更后）

主表软删除/撤回后，关联表数据处理规则：

- **软删除/撤回不删关联表**：保留已读记录、操作记录用于审计；查询时加 `where('status', '!=', 'recalled')` 或 `whereNull('deleted_at')` 过滤
- **彻底删除用外键级联或 Observer**：需要物理删除时，迁移中加 `$table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade')`，或用 Model Observer 的 `deleted` 事件同步删除关联
- **状态终态后禁止修改关联**：订单完成、反馈处理完成等终态后，关联记录（明细、日志）禁止修改，只能新增

### 图片/附件字段存储判断标准

| 场景 | 存储方式 | 说明 |
|------|---------|------|
| 单张图片/单个文件 | 字段存 URL 字符串（`string`） | 头像、封面、单附件 |
| ≤3 张图片/附件 | JSON 数组字段（`json` + `casts => 'array'`） | 反馈图片、消息图片，不需要独立管理 |
| >3 张或需独立管理 | 关联表（如 `message_attachments`） | 需要排序、单独删除、单独设置属性时 |

- JSON 数组存储时，表单用 `mediaSelector` 多值模式（需先扩展支持数组），或用自定义字段
- 关联表存储时，Model 定义 `hasMany` 关联，Repository 级联查询
