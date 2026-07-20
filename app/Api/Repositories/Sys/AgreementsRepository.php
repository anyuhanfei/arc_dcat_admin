<?php

namespace App\Api\Repositories\Sys;

use App\Models\Sys\SysAgreements;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AgreementsRepository
{
    /**
     * 获取所有协议列表（仅 id 和 title）
     */
    public function selectAll(): Collection
    {
        return SysAgreements::select('id', 'title')->get();
    }

    /**
     * 根据 ID 获取协议详情
     *
     * @throws ModelNotFoundException
     */
    public function findById(int $id): SysAgreements
    {
        return SysAgreements::select('id', 'title', 'content')->findOrFail($id);
    }
}
