<?php

namespace App\Api\Services\Login;

use App\Api\Repositories\Login\PasswordRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordService
{
    public function __construct(
        protected PasswordRepository $repository,
    ) {}

    /**
     * 注册
     */
    public function register(string $account, string $password): array
    {
        $nickname = '用户' . mb_substr($account, -4);

        $user = $this->repository->createUser([
            'account'  => $account,
            'password' => $password,
            'nickname' => $nickname,
        ]);

        return $this->userInfo($user);
    }

    /**
     * 登录
     */
    public function login(string $account, string $password): array
    {
        $user = $this->repository->findByLogin($account);

        if (!$user || !Hash::check($password, $user->password)) {
            throwBusinessException('账号或密码错误');
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
