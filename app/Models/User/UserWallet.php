<?php

namespace App\Models\User;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class UserWallet extends Model
{
	use HasDateTimeFormatter;

    protected $table = 'user_wallets';

    /**
     * 所属用户
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
