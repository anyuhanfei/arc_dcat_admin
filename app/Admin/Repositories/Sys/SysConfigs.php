<?php

namespace App\Admin\Repositories\Sys;

use App\Models\Sys\SysConfigs as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class SysConfigs extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
