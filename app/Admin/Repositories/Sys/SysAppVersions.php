<?php

namespace App\Admin\Repositories\Sys;

use App\Models\Sys\SysAppVersions as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class SysAppVersions extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
