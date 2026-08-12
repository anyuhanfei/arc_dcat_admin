<?php

namespace App\Admin\Repositories\Sys;

use App\Models\Sys\SysFaqQuestions as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class SysFaqQuestions extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
