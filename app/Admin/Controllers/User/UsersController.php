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
            $form->display('id');
            $form->text('account', admin_trans_field('account'));
            $form->text('phone', admin_trans_field('phone'));
            $form->text('email', admin_trans_field('email'));
            $form->text('password', admin_trans_field('password'));
            $form->text('nickname', admin_trans_field('nickname'));
            $form->mediaSelector('avatar', admin_trans_field('avatar'));
            $form->text('signature', admin_trans_field('signature'));
            $form->text('gender', admin_trans_field('gender'));
            $form->text('birthday', admin_trans_field('birthday'));
            $form->text('status', admin_trans_field('status'));
            $form->text('phone_verified_at', admin_trans_field('phone_verified_at'));
            $form->text('email_verified_at', admin_trans_field('email_verified_at'));
            $form->text('remember_token', admin_trans_field('remember_token'));
        
            $form->display('created_at', admin_trans_field('created_at'));
            $form->display('updated_at', admin_trans_field('updated_at'));
        });
    }
}
