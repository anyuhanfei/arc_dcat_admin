<?php

namespace App\Admin\Repositories\User;

use App\Models\User\Users as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Users extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
