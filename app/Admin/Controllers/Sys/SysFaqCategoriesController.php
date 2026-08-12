<?php

namespace App\Admin\Controllers\Sys;

use App\Admin\Repositories\Sys\SysFaqCategories;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SysFaqCategoriesController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SysFaqCategories(), function (Grid $grid) {
            $grid->sortable('sort');
            $grid->column('id', admin_trans_field('id'))->sortable();
            $grid->column('name', admin_trans_field('name'));
            $grid->column('is_show', admin_trans_field('is_show'))->using(__('sys-faq-categories.enums.is_show'))->label([0 => 'danger', 1 => 'success']);
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->like('name');
        
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
        return Show::make($id, new SysFaqCategories(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('sort');
            $show->field('is_show');
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
        return Form::make(new SysFaqCategories(), function (Form $form) {
            $form->display('id');
            $form->text('name', admin_trans_field('name'))->required();
            $form->radio('is_show', admin_trans_field('is_show'))->options(__('sys-faq-categories.enums.is_show'))->default(1);
        });
    }
}
