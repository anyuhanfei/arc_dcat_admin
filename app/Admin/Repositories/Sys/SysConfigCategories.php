<?php

namespace App\Admin\Repositories\Sys;

use App\Models\Sys\SysConfigCategories as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class SysConfigCategories extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
