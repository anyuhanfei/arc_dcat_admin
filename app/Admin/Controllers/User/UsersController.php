<?php

namespace App\Admin\Controllers\User;

use App\Admin\Repositories\User\Users;
use App\Admin\Form\Field\MediaSelector;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class UsersController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Users(), function (Grid $grid) {
            $grid->column('id', admin_trans_field('id'))->sortable();
            $grid->column('account', admin_trans_field('account'));
            $grid->column('phone', admin_trans_field('phone'));
            $grid->column('email', admin_trans_field('email'));
            $grid->column('nickname', admin_trans_field('nickname'));
            $grid->column('avatar', admin_trans_field('avatar'))->display(function ($v) {
                return $v ? '<img src="'.e(full_url($v)).'" style="height:40px;border-radius:4px;">' : '';
            });
            $grid->column('gender', admin_trans_field('gender'))->using(__('users.enums.gender'))->label([0 => 'default', 1 => 'primary', 2 => 'pink']);
            $grid->column('birthday', admin_trans_field('birthday'));
            $grid->column('status', admin_trans_field('status'))->using(__('users.enums.status'))->label([0 => 'danger', 1 => 'success']);
            $grid->column('created_at', admin_trans_field('created_at'))->sortable();

            $grid->model()->orderBy('created_at', 'desc');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->like('account', admin_trans_field('account'));
                $filter->like('phone', admin_trans_field('phone'));
                $filter->like('email', admin_trans_field('email'));
                $filter->like('nickname', admin_trans_field('nickname'));
                $filter->equal('gender', admin_trans_field('gender'))->select(__('users.enums.gender'));
                $filter->equal('status', admin_trans_field('status'))->select(__('users.enums.status'));
                $filter->between('created_at', admin_trans_field('created_at'))->datetime();
                $filter->between('birthday', admin_trans_field('birthday'))->date();
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new Users(), function (Show $show) {
            $show->field('id');
            $show->field('account');
            $show->field('phone');
            $show->field('email');
            $show->field('password');
            $show->field('nickname');
            $show->field('avatar');
            $show->field('signature');
            $show->field('gender');
            $show->field('birthday');
            $show->field('status');
            $show->field('phone_verified_at');
            $show->field('email_verified_at');
            $show->field('remember_token');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Users(), function (Form $form) {
            if ($form->isCreating()) {
                // 后台添加用户：使用与 API 一致的随机 id 生成（users.id 非自增）
                // 注：saving 回调里需写 $form->input() 注入 inputs（$form->model() 属性修改不会进入 updates 落库）
                $form->saving(function (Form $form) {
                    $form->input('id', \App\Models\User::generateId());
                    // users.phone/email 有唯一索引：空字符串转 null（NULL 不参与唯一约束），避免与历史 '' 记录冲突
                    // 注：input($key, null) 会被当作 getter，必须用数组分支注入 null 值
                    $form->input([
                        'phone' => $form->input('phone') ?: null,
                        'email' => $form->input('email') ?: null,
                    ], true);
                });

                $this->buildUserInfoFields($form);
            } else {
                $form->tab(__('users.tabs.user_info'), function (Form $form) {
                    $this->buildUserInfoFields($form);
                });
                $form->tab(__('users.tabs.wallet'), function (Form $form) {
                    $this->buildWalletFields($form);
                });

                // 钱包金额变更处理（编辑提交时）
                // 注：Dcat 的 saving 回调经 Event 派发时会把 $this 重绑定为 Form 的 Model，
                //     闭包内不能用 $this 调控制器方法——捕获 Controller 引用（$self）绕过绑定
                $self = $this;
                $form->saving(function (Form $form) use ($self) {
                    $self->handleWalletChange($form);
                });
            }
        });
    }

    /**
     * 用户基本信息字段（新增/编辑共用）
     */
    protected function buildUserInfoFields(Form $form)
    {
        $form->text('account', admin_trans_field('account'))->required();
        $form->text('phone', admin_trans_field('phone'));
        $form->text('email', admin_trans_field('email'));
        $form->password('password', admin_trans_field('password'));
        $form->text('nickname', admin_trans_field('nickname'));
        $form->mediaSelector('avatar', admin_trans_field('avatar'));
        $form->text('signature', admin_trans_field('signature'));
        $form->radio('gender', admin_trans_field('gender'))->options(__('users.enums.gender'))->default(0);
        $form->date('birthday', admin_trans_field('birthday'));
        $form->radio('status', admin_trans_field('status'))->options(__('users.enums.status'))->default(1);
    }

    /**
     * 钱包 tab 字段（编辑页）：每个币种展示当前余额 + 增减金额 + 说明
     */
    protected function buildWalletFields(Form $form)
    {
        foreach (config('wallet.currencies') as $currency => $cfg) {
            $name = wallet_currency_name($currency);
            $balance = \App\Models\User\UserWallet::where('user_id', $form->model()->id)
                ->where('currency', $currency)
                ->value('balance');

            $form->html('<div class="alert alert-info mb-1">'.$name.' 当前余额：<b>'.($balance ?? '0.00').'</b></div>');
            $form->decimal("wallet_amount_{$currency}", $name.' 增减金额')->default(0);
            $form->text("wallet_remark_{$currency}", $name.' 说明');
            $form->divider();
        }

        // 钱包虚拟字段（增减金额/说明）不写入用户表
        $form->ignore(collect(config('wallet.currencies'))->flatMap(fn ($cfg, $c) => ["wallet_amount_{$c}", "wallet_remark_{$c}"])->all());
    }

    /**
     * 钱包金额变更处理（编辑保存时）：事务内更新余额并写流水（type=admin）
     *
     * @throws \App\Exceptions\BusinessException 金额非 0 但说明为空
     */
    protected function handleWalletChange(Form $form)
    {
        $userId = $form->model()->id;
        if (! $userId) {
            return;
        }

        $changes = [];
        foreach (config('wallet.currencies') as $currency => $cfg) {
            $name = wallet_currency_name($currency);
            // 注：钱包虚拟字段已被 $form->ignore() 排除，且 Form::prepare() 在 saving 回调前就移除了它们——
            //     必须从原始请求读取（ignore 只影响 $form->inputs，不影响 request）
            $amount = request()->input("wallet_amount_{$currency}");
            $remark = request()->input("wallet_remark_{$currency}");

            if (! is_numeric($amount) || (float) $amount == 0) {
                continue;
            }
            if (trim((string) $remark) === '') {
                throwBusinessException($name.' 金额变更必须填写说明');
            }

            $changes[] = [
                'currency' => $currency,
                'amount'   => (string) $amount,
                'remark'   => trim($remark),
            ];
        }

        if (! $changes) {
            return;
        }

        \DB::transaction(function () use ($userId, $changes) {
            foreach ($changes as $c) {
                $wallet = \App\Models\User\UserWallet::where('user_id', $userId)
                    ->where('currency', $c['currency'])
                    ->lockForUpdate()
                    ->first();

                if (! $wallet) {
                    $wallet = new \App\Models\User\UserWallet();
                    $wallet->user_id = $userId;
                    $wallet->currency = $c['currency'];
                    $wallet->balance = 0;
                    $wallet->save();
                }

                $before = $wallet->balance;
                $after = bcadd((string) $before, $c['amount'], 2);
                $wallet->balance = $after;
                $wallet->save();

                // 写流水（UserWalletRecords $timestamps=false，必须手动 created_at；模型无 fillable，逐属性赋值）
                $record = new \App\Models\User\UserWalletRecords();
                $record->user_id = $userId;
                $record->currency = $c['currency'];
                $record->amount = $c['amount'];
                $record->before_balance = $before;
                $record->after_balance = $after;
                $record->type = 'admin';
                $record->remark = $c['remark'];
                $record->ref1 = null;
                $record->ref2 = null;
                $record->created_at = now();
                $record->save();
            }
        });
    }
}
