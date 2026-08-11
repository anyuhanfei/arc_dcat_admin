<?php

namespace App\Api\Services\Login;

use App\Api\Repositories\Login\WechatRepository;
use App\Integrations\Sms\Sms;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserOauthAccountService
{
    public function __construct(
        protected WechatRepository $repository,
    ) {}

    /**
     * 按三方 openid 登录（未绑定自动注册；通用版，供 Apple 等非微信渠道使用）
     *
     * @param string      $type          渠道类型：如 apple
     * @param string      $openid        三方用户唯一标识（Apple 为 sub）
     * @param string|null $unionid       三方 unionid（可空）
     * @param string|null $sessionKey    会话密钥（敏感凭据；非 null 时覆盖刷新，可空）
     * @param string|null $oauthNickname 三方昵称（存 user_oauths，可空）
     * @param string|null $oauthAvatar   三方头像（存 user_oauths，可空）
     * @param array|null  $raw           三方原始信息（如 Apple 的 email/is_private_email，可空）
     * @param string      $account       完整登录账号（如 apple_{sub}，自动注册时使用）
     * @param string      $nickname      默认昵称（如 苹果用户{后6位}，自动注册时使用）
     */
    public function loginByOauth(string $type, string $openid, ?string $unionid = null, ?string $sessionKey = null, ?string $oauthNickname = null, ?string $oauthAvatar = null, ?array $raw = null, string $account = '', string $nickname = ''): array
    {
        $oauth = $this->repository->findOauthByTypeAndOpenid($type, $openid);

        if (!$oauth) {
            $user = $this->repository->createOauthUser($account, $nickname);
            $this->repository->createOauth([
                'user_id'     => $user->id,
                'type'        => $type,
                'openid'      => $openid,
                'unionid'     => $unionid,
                'session_key' => $sessionKey,
                'nickname'    => $oauthNickname,
                'avatar'      => $oauthAvatar,
                'raw'         => $raw,
            ]);
        } else {
            // 已有 oauth 记录：session_key 非 null 时覆盖刷新（敏感凭据）
            if ($sessionKey !== null) {
                $oauth->session_key = $sessionKey;
                $oauth->save();
            }

            $user = $oauth->user;
            // oauth 记录存在但用户被删（账号合并残留）：重建用户并回填 oauth.user_id
            if (!$user) {
                $user = $this->repository->createOauthUser($account, $nickname);
                $oauth->user_id = $user->id;
                $oauth->save();
            }
        }

        if ((int) $user->status !== 1) {
            throwBusinessException('账号已被禁用');
        }

        $plainTextToken = $user->createToken('api')->plainTextToken;
        $token = explode('|', $plainTextToken, 2)[1] ?? $plainTextToken;

        return [
            'token' => $token,
            'user'  => $this->userInfo($user),
        ];
    }

    /**
     * 绑定手机号（含账号合并；与微信 bindPhone 逻辑一致）
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
            $user->save();
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
            'user'  => $this->userInfo($user),
        ];
    }

    /**
     * 账号合并时，将旧账号（被合并的三方账号）的业务数据转移到新账号（手机号账号）
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

    /**
     * 用户公开信息
     */
    protected function userInfo(User $user): array
    {
        return [
            'id'         => $user->id,
            'account'    => $user->account,
            'phone'      => $user->phone,
            'email'      => $user->email,
            'nickname'   => $user->nickname,
            'avatar'     => full_url($user->avatar),
            'signature'  => $user->signature,
            'gender'     => $user->gender,
            'birthday'   => $user->birthday?->toDateString(),
            'created_at' => $user->created_at?->toDateTimeString(),
        ];
    }
}
