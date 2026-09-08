<?php

namespace App\Api\Services\Login;

use App\Api\Repositories\Login\WechatRepository;
use App\Integrations\Sms\Sms;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class WechatAccountService
{
    public function __construct(
        protected WechatRepository $repository,
    ) {}

    /**
     * 按 openid 登录（未绑定自动注册）
     *
     * @param string      $type        渠道类型：wechat_mini / wechat_mp / wechat_app
     * @param string      $openid      微信 openid
     * @param string|null $unionid     微信 unionid（可空）
     * @param string|null $sessionKey  小程序 session_key（仅 wechat_mini 有；敏感凭据，用于手机号解密 getPhone，每次登录需覆盖刷新，公众号/APP 传 null）
     * @param string|null $wxNickname  微信昵称（存 user_oauths，可空）
     * @param string|null $wxAvatar    微信头像（存 user_oauths，可空）
     */
    public function loginByOpenid(string $type, string $openid, ?string $unionid = null, ?string $sessionKey = null, ?string $wxNickname = null, ?string $wxAvatar = null): array
    {
        $oauth = $this->repository->findOauthByTypeAndOpenid($type, $openid);

        if (!$oauth) {
            $user = $this->repository->createUserForWechat($openid);
            $this->repository->createOauth([
                'user_id'     => $user->id,
                'type'        => $type,
                'openid'      => $openid,
                'unionid'     => $unionid,
                'session_key' => $sessionKey,
                'nickname'    => $wxNickname,
                'avatar'      => $wxAvatar,
            ]);
        } else {
            // 已有 oauth 记录：session_key 每次登录可能变化，非 null 时覆盖刷新（敏感凭据，用于手机号解密）
            if ($sessionKey !== null) {
                $oauth->session_key = $sessionKey;
                $this->repository->saveOauth($oauth);
            }

            $user = $oauth->user;
            // oauth 记录存在但用户被删（账号合并残留）：重建用户并回填 oauth.user_id
            if (!$user) {
                $user = $this->repository->createUserForWechat($openid);
                $oauth->user_id = $user->id;
                $this->repository->saveOauth($oauth);
            }
        }

        if ((int) $user->status !== 1) {
            throwBusinessException('账号已被禁用');
        }

        $plainTextToken = $user->createToken('api')->plainTextToken;
        $token = explode('|', $plainTextToken, 2)[1] ?? $plainTextToken;

        return [
            'token' => $token,
            'user'  => $user,
        ];
    }

    /**
     * 绑定手机号（含账号合并）
     *
     * 手机号未占用 → 绑定到当前用户；已绑定本人 → 直接返回；被其他账号占用 → 合并：
     * 当前账号的三方绑定转移到手机号账号（含业务数据转移预留），当前账号软删除。
     * 所有成功路径统一返回新 token + 用户信息（前端替换旧 token）。
     */
    public function bindPhone(User $user, string $phone, string $code): array
    {
        if (!Sms::make()->verifyCode($phone, $code)) {
            throwBusinessException('验证码错误或已过期');
        }

        $target = $this->repository->findUserByPhone($phone);

        if ($target === null) {
            // 手机号未占用：绑定到当前用户
            $user->phone = $phone;
            $this->repository->saveUser($user);
        } elseif ((int) $target->id !== (int) $user->id) {
            // 手机号被其他账号占用：合并（转移三方绑定 + 业务数据转移预留 + 软删除当前账号）
            DB::transaction(function () use ($user, $target) {
                $this->repository->transferOauths($user->id, $target->id);
                $this->transferUserData($user->id, $target->id);
                $this->repository->deleteUser($user->id);
            });
            $user = $target;
        }
        // 已绑定本人手机号：无需任何操作，直接签发新 token

        // 业务成功，销毁验证码缓存防止重放
        Sms::make()->destroyCode($phone);

        $plainTextToken = $user->createToken('api')->plainTextToken;
        $token = explode('|', $plainTextToken, 2)[1] ?? $plainTextToken;

        return [
            'token' => $token,
            'user'  => $user,
        ];
    }

    /**
     * 账号合并时，将旧账号（被合并的微信账号）的业务数据转移到新账号（手机号账号）
     *
     * 注意：此方法在合并流程中于 user_oauths 转移之后、旧账号软删除之前被调用。
     * TODO: 真实项目中在此添加具体业务表的数据转移逻辑，例如：
     *   // 订单
     *   \App\Models\Order::where('user_id', $fromUserId)->update(['user_id' => $toUserId]);
     *   // 收藏
     *   \App\Models\Favorite::where('user_id', $fromUserId)->update(['user_id' => $toUserId]);
     *   // 收货地址、积分、卡券等业务数据同理
     * 按需启用，当前留空（真实项目中自行补充）。
     */
    protected function transferUserData(int $fromUserId, int $toUserId): void
    {
        // TODO: 业务数据转移逻辑待真实项目补充
    }

}
