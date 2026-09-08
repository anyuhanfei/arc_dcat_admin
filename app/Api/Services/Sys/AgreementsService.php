<?php

namespace App\Api\Services\Sys;

use App\Api\Repositories\Sys\AgreementsRepository;
use App\Models\Sys\SysAgreements;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AgreementsService
{
    public function __construct(
        protected AgreementsRepository $repository,
    ) {}

    /**
     * 获取协议列表
     */
    public function list(): Collection
    {
        return $this->repository->selectAll();
    }

    /**
     * 获取协议详情
     *
     * @throws \App\Exceptions\BusinessException
     */
    public function detail(int $id): SysAgreements
    {
        try {
            return $this->repository->findById($id);
        } catch (ModelNotFoundException) {
            throwBusinessException('协议不存在');
        }
    }
}
