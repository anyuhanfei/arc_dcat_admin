<?php

namespace App\Models\User;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class UserWallet extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'user_wallets';

    protected $fillable = ['user_id', 'currency', 'balance'];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'balance' => 'decimal:2',
        ];
    }

    /**
     * 所属用户
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
