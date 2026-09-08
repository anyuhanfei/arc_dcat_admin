<?php

namespace App\Api\Repositories\Login;

use App\Models\User;
use App\Models\User\UserOauth;

class WechatRepository
{
    /**
     * 按渠道类型 + openid 查询三方绑定记录
     */
    public function findOauthByTypeAndOpenid(string $type, string $openid): ?UserOauth
    {
        return UserOauth::where('type', $type)->where('openid', $openid)->first();
    }

    /**
     * 创建三方绑定记录
     *
     * 注：session_key 为敏感凭据（用于手机号解密）且不在 UserOauth 的 fillable 中，
     * 此处用 forceCreate 绕过批量赋值保护（数据由应用层构造，非用户输入）。
     */
    public function createOauth(array $data): UserOauth
    {
        return UserOauth::forceCreate($data);
    }

    /**
     * 创建微信登录用户（id 由 User::generateId() 生成；account = wx_{openid}，password 不设 = null）
     * refresh() 重新加载数据库默认值（如 status=1），避免新模型 status 为 null 被误判禁用
     */
    public function createUserForWechat(string $openid): User
    {
        return User::create([
            'id'       => User::generateId(),
            'account'  => 'wx_' . $openid,
            'nickname' => '微信用户' . mb_substr($openid, -6),
        ])->refresh();
    }

    /**
     * 创建三方登录用户（通用；account/nickname 由调用方组装，id 由 User::generateId() 生成，password 不设 = null）
     * refresh() 重新加载数据库默认值（如 status=1），避免新模型 status 为 null 被误判禁用
     */
    public function createOauthUser(string $account, string $nickname): User
    {
        return User::create([
            'id'       => User::generateId(),
            'account'  => $account,
            'nickname' => $nickname,
        ])->refresh();
    }

    /**
     * 按手机号查询用户
     */
    public function findUserByPhone(string $phone): ?User
    {
        return User::where('phone', $phone)->first();
    }

    /**
     * 将用户的所有三方绑定记录转移到另一用户（账号合并）
     */
    public function transferOauths(int $fromUserId, int $toUserId): void
    {
        UserOauth::where('user_id', $fromUserId)->update(['user_id' => $toUserId]);
    }

    /**
     * 软删除用户（账号合并后清理；SoftDeletes 生效，记录保留在库中仅标记 deleted_at）
     */
    public function deleteUser(int $userId): void
    {
        User::where('id', $userId)->delete();
    }

    /**
     * 保存三方绑定记录（新增/更新）
     */
    public function saveOauth(UserOauth $oauth): bool
    {
        return $oauth->save();
    }

    /**
     * 保存用户（新增/更新）
     */
    public function saveUser(User $user): bool
    {
        return $user->save();
    }
}
