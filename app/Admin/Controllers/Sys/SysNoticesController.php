<?php

namespace App\Admin\Controllers\Sys;

use App\Admin\Repositories\Sys\SysNotices;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SysNoticesController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SysNotices(), function (Grid $grid) {
            $grid->column('id', admin_trans_field('id'))->sortable();
            $grid->column('title', admin_trans_field('title'));
            $grid->column('image', admin_trans_field('image'))->display(fn ($v) => $v ? '<img src="'.e(full_url($v)).'" style="height:40px;border-radius:4px;">' : '');
            $grid->column('status', admin_trans_field('status'))->using(__('sys-notices.enums.status'))->label([0 => 'default', 1 => 'success']);
            $grid->column('updated_at', admin_trans_field('updated_at'))->sortable();

            // 行内操作：已读用户弹框（图标链接，对齐原版查看/编辑/删除样式；prepend 放「查看」前）
            $grid->actions(function ($actions) {
                $actions->prepend(sprintf(
                    '<a href="javascript:void(0)" onclick="openNoticeReads(%d)" title="%s"><i class="feather icon-users"></i></a>&nbsp;&nbsp;',
                    $actions->getKey(),
                    e(__('sys-notices.buttons.read_users'))
                ));
            });

            // 全局弹框函数（Admin::script 会自动包 Dcat.ready，勿嵌套 ready；显式挂 window 供行内 onclick 访问）
            Admin::script(
                'window.openNoticeReads = function (id) {'
                .'layer.open({ type: 2, title: '.json_encode(__('sys-notices.buttons.read_users'))
                .', content: '.json_encode(admin_base_path('sys/notice-reads')).' + "?notice_id=" + id'
                .', area: ["80%", "70vh"], maxmin: true });'
                .'}'
            );

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->like('title', admin_trans_field('title'));
                $filter->like('content', admin_trans_field('content'));
                $filter->equal('status', admin_trans_field('status'))->select(__('sys-notices.enums.status'));
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
        return Show::make($id, new SysNotices(), function (Show $show) {
            $show->field('id', admin_trans_field('id'));
            $show->field('title', admin_trans_field('title'));
            $show->field('image', admin_trans_field('image'))->image();
            $show->field('content', admin_trans_field('content'))->unescape();
            $show->field('status', admin_trans_field('status'))->using(__('sys-notices.enums.status'));
            $show->field('created_at', admin_trans_field('created_at'));
            $show->field('updated_at', admin_trans_field('updated_at'));
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new SysNotices(), function (Form $form) {
            $form->text('title', admin_trans_field('title'))->required();
            $form->mediaSelector('image', admin_trans_field('image'));
            $form->ueditor('content', admin_trans_field('content'));
            $form->radio('status', admin_trans_field('status'))->options(__('sys-notices.enums.status'))->default(0);
        });
    }
}
