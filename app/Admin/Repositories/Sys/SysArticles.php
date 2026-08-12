<?php

namespace App\Admin\Repositories\Sys;

use App\Models\Sys\SysArticles as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class SysArticles extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
