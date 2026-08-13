<?php

namespace App\Models\User;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class UserWalletRecords extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'user_wallet_records';
    public $timestamps = false;

    /**
     * 所属用户
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

}
