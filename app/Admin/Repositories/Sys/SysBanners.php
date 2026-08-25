<?php

namespace App\Admin\Repositories\Sys;

use App\Models\Sys\SysBanners as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class SysBanners extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
