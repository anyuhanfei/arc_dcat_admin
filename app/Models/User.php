<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['id', 'account', 'phone', 'email', 'password', 'nickname', 'avatar', 'signature', 'gender', 'birthday', 'status', 'phone_verified_at', 'email_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** 主键非自增（users.id 由应用层 User::generateId() 生成） */
    public $incrementing = false;

    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'gender' => 'integer',
            'status' => 'integer',
            'birthday' => 'date',
        ];
    }

    /**
     * 用户的第三方登录资料
     */
    public function userOauths()
    {
        return $this->hasMany(\App\Models\User\UserOauth::class);
    }

    /**
     * 生成随机用户 ID（6 位起）
     * 策略：从缓存水位位数开始（默认 6 位），一次生成 50 个随机数批量查重；
     * 50 个全部占用则位数 +1 继续（不封顶）；成功后将位数写入水位缓存（永久，无 TTL）
     *
     * @return int 未被占用的随机 ID
     */
    public static function generateId(): int
    {
        $digits = (int) Cache::get('user_id_digits', 6);

        while (true) {
            // 一次生成 50 个随机数并去重
            $ids = [];
            for ($i = 0; $i < 50; $i++) {
                $ids[] = mt_rand((int) (10 ** ($digits - 1)), (int) (10 ** $digits) - 1);
            }
            $ids = array_values(array_unique($ids));

            // 一次查表找出已占用的 id
            $used = static::whereIn('id', $ids)->pluck('id')->all();
            $available = array_values(array_diff($ids, $used));

            if ($available) {
                Cache::put('user_id_digits', $digits);
                return $available[0];
            }

            $digits++; // 50 个全部占用，升位继续
        }
    }
}
