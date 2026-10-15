<?php

namespace App\Admin\Controllers\Sys;

use App\Admin\Repositories\Sys\SysArticleCategories;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SysArticleCategoriesController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SysArticleCategories(), function (Grid $grid) {
            $grid->showRecycledButton();
            $grid->sortable('sort');
            $grid->column('id', admin_trans_field('id'))->sortable();
            $grid->column('name', admin_trans_field('name'));
            $grid->column('image', admin_trans_field('image'))->display(function ($v) {
                return $v ? '<img src="'.e(full_url($v)).'" style="height:40px;border-radius:4px;">' : '';
            });
            $grid->column('is_show', admin_trans_field('is_show'))->using(__('sys-article-categories.enums.is_show'))->label([0 => 'default', 1 => 'success']);
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->like('name', admin_trans_field('name'));
                $filter->equal('is_show', admin_trans_field('is_show'))->select(__('sys-article-categories.enums.is_show'));
        
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
        return Show::make($id, new SysArticleCategories(), function (Show $show) {
            $show->field('id', admin_trans_field('id'));
            $show->field('name', admin_trans_field('name'));
            $show->field('image', admin_trans_field('image'))->image('', 60, 60);
            $show->field('is_show', admin_trans_field('is_show'))->using(__('sys-article-categories.enums.is_show'));
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new SysArticleCategories(), function (Form $form) {
            $form->display('id');
            $form->text('name', admin_trans_field('name'))->required();
            $form->adminImage('image', admin_trans_field('image'));
            $form->radio('is_show', admin_trans_field('is_show'))->options(__('sys-article-categories.enums.is_show'))->default(1);
        });
    }
}
