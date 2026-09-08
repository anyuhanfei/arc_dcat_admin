<?php

namespace App\Api\Services\User;

use App\Api\Repositories\User\UserRepository;
use App\Models\User;

class UserService
{
    public function __construct(
        protected UserRepository $repository,
    ) {}

    /**
     * 用户公开信息
     */
    public function info(User $user): User
    {
        return $user;
    }

    /**
     * 修改用户资料（只更新提交的字段，null 不处理）
     *
     * @throws \App\Exceptions\BusinessException
     */
    public function update(User $user, array $params): User
    {
        $data = array_filter($params, fn ($v) => $v !== null);

        if (empty($data)) {
            throwBusinessException('请至少提交一个要修改的字段');
        }

        $user->fill($data);
        $this->repository->save($user);

        return $user;
    }

    /**
     * 退出登录（删除当前 token）
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /**
     * 注销账号（软删除，token 因用户被排除查询而失效）
     */
    public function cancel(User $user): void
    {
        $this->repository->delete($user);
    }
}
