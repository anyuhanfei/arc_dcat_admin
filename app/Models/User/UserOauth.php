<?php

namespace App\Models\User;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class UserOauth extends Model
{
	use HasDateTimeFormatter;

    protected $table = 'user_oauths';

    protected $fillable = ['user_id', 'type', 'openid', 'unionid', 'nickname', 'avatar', 'raw', 'session_key'];

    protected $hidden = ['session_key'];

    protected $casts = [
        'raw' => 'array',
    ];

    /**
     * 所属用户
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
