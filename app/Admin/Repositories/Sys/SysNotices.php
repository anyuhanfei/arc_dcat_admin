<?php

namespace App\Admin\Repositories\Sys;

use App\Models\Sys\SysNotices as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class SysNotices extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
