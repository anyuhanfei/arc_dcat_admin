<?php

namespace App\Admin\Repositories\User;

use App\Models\User\UserWalletRecords as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class UserWalletRecords extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
