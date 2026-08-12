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
            $grid->column('id')->sortable();
            $grid->column('account', '登录账号');
            $grid->column('phone', '手机号');
            $grid->column('email', '邮箱');
            $grid->column('nickname', '昵称');
            $grid->column('avatar', '头像')->display(function ($v) {
                return $v ? '<img src="'.e(full_url($v)).'" style="height:40px;border-radius:4px;">' : '';
            });
            $grid->column('gender', '性别')->using([0 => '未知', 1 => '男', 2 => '女'])->label([0 => 'default', 1 => 'primary', 2 => 'pink']);
            $grid->column('birthday', '生日');
            $grid->column('status', '状态')->using([0 => '禁用', 1 => '正常'])->label([0 => 'danger', 1 => 'success']);
            $grid->column('created_at', '注册时间')->sortable();

            $grid->model()->orderBy('created_at', 'desc');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->like('account');
                $filter->like('phone');
                $filter->like('email');
                $filter->like('nickname');
                $filter->equal('gender')->select([0 => '未知', 1 => '男', 2 => '女']);
                $filter->equal('status')->select([0 => '禁用', 1 => '正常']);
                $filter->between('created_at')->datetime();
                $filter->between('birthday')->date();
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
            $form->text('account');
            $form->text('phone');
            $form->text('email');
            $form->text('password');
            $form->text('nickname');
            $form->mediaSelector('avatar', '头像');
            $form->text('signature');
            $form->text('gender');
            $form->text('birthday');
            $form->text('status');
            $form->text('phone_verified_at');
            $form->text('email_verified_at');
            $form->text('remember_token');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
