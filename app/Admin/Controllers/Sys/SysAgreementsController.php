<?php

namespace App\Admin\Controllers\Sys;

use App\Admin\Repositories\Sys\SysAgreements;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SysAgreementsController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SysAgreements(), function (Grid $grid) {
            $grid->column('id', admin_trans_field('id'))->sortable();
            $grid->column('title', admin_trans_field('title'));
            $grid->column('updated_at', admin_trans_field('updated_at'))->sortable();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->like('title', admin_trans_field('title'));
            });
            $grid->disableBatchActions();
            $grid->disableRowSelector();
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
        return Show::make($id, new SysAgreements(), function (Show $show) {
            $show->field('id');
            $show->field('title');
            $show->field('content')->unescape();
            $show->field('updated_at');
            $show->disableDeleteButton();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new SysAgreements(), function (Form $form) {
            $form->display('id');
            $form->text('title', admin_trans_field('title'));
            $form->ueditor('content', admin_trans_field('content'));
            $form->disableDeleteButton();
        });
    }
}
