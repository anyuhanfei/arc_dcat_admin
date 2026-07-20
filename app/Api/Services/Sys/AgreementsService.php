<?php

namespace App\Api\Services\Sys;

use App\Api\Repositories\Sys\AgreementsRepository;
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
     * @throws ModelNotFoundException
     */
    public function detail(int $id): array
    {
        $agreement = $this->repository->findById($id);

        return [
            'id'      => $agreement->id,
            'title'   => $agreement->title,
            'content' => $agreement->content,
        ];
    }
}
