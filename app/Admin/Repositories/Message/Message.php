<?php

namespace App\Admin\Repositories\Message;

use App\Models\User\UserMessage as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Message extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
