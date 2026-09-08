<?php

namespace App\Models\User;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class UserWalletRecords extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'user_wallet_records';

    public $timestamps = false;

    protected $fillable = ['user_id', 'currency', 'amount', 'before_balance', 'after_balance', 'type', 'remark', 'ref1', 'ref2', 'created_at'];

    protected function casts(): array
    {
        return [
            'user_id'        => 'integer',
            'amount'         => 'decimal:2',
            'before_balance' => 'decimal:2',
            'after_balance'  => 'decimal:2',
            'created_at'     => 'datetime',
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
