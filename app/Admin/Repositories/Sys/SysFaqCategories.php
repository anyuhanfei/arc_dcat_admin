<?php

namespace App\Admin\Repositories\Sys;

use App\Models\Sys\SysFaqCategories as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class SysFaqCategories extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
