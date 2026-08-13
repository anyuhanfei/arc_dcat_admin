<?php

namespace App\Models\User;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    /** 与 API 的 App\Models\User 一致：主键非自增（id 由应用层生成），避免 INSERT 后被 lastInsertId 覆盖 */
    public $incrementing = false;

    /**
     * 与 API 的 App\Models\User 保持一致：密码自动 bcrypt 加密（后台创建的用户可直接登录）
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * users.id 非自增：创建时自动生成随机 id（与 API 的 User::generateId() 一致）
     * 模型层兜底——任何途径创建（form/repository/脚本）都不依赖表单 saving 时序
     */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->getAttribute('id'))) {
                $model->id = \App\Models\User::generateId();
            }
        });
    }
}
