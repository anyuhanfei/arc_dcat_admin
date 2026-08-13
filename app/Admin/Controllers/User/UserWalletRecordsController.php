<?php

namespace App\Admin\Controllers\User;

use App\Admin\Repositories\User\UserWalletRecords;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;

class UserWalletRecordsController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new UserWalletRecords(), function (Grid $grid) {
            // 流水只读：无新增、无行内操作、无勾选、无批量
            $grid->disableCreateButton();
            $grid->disableActions();
            $grid->disableRowSelector();
            $grid->disableBatchActions();

            // 币种 tab 筛选
            $grid->selector(function (Grid\Tools\Selector $selector) {
                $selector->select(
                    'currency',
                    __('user-wallet-records.fields.currency'),
                    collect(config('wallet.currencies'))->mapWithKeys(fn ($c, $k) => [$k => wallet_currency_name($k)])->toArray()
                );
            });

            $grid->column('id', __('user-wallet-records.fields.id'))->sortable();
            // 关联列 user 返回用户模型，名片展示（空用户由 helper 兜底显示已注销）
            $grid->column('user', __('user-wallet-records.fields.user'))->display(fn ($user) => admin_user_card($user));
            $grid->column('currency', __('user-wallet-records.fields.currency'))->display(fn ($v) => wallet_currency_name($v));
            $grid->column('type', __('user-wallet-records.fields.type'))->display(fn ($v) => wallet_record_type_name($v));
            $grid->column('amount', __('user-wallet-records.fields.amount'))->display(function ($v) {
                // 正绿负红
                return $v > 0 ? '<span class="text-success">+'.$v.'</span>' : '<span class="text-danger">'.$v.'</span>';
            });
            $grid->column('before_balance', __('user-wallet-records.fields.before_balance'));
            $grid->column('after_balance', __('user-wallet-records.fields.after_balance'));
            $grid->column('remark', __('user-wallet-records.fields.remark'))->display(fn ($v) => $v ?: '-');
            $grid->column('created_at', __('user-wallet-records.fields.created_at'))->sortable();

            $grid->model()->orderBy('created_at', 'desc');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('user_id', __('user-wallet-records.fields.user'));
                $filter->equal('type', __('user-wallet-records.fields.type'));
            });
        });
    }
}
