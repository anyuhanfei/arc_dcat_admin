<?php

namespace App\Admin\Repositories\Sys;

use App\Models\Sys\SysNoticeReads as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class SysNoticeReads extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
