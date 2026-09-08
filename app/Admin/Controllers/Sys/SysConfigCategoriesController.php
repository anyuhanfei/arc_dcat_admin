<?php

namespace App\Admin\Controllers\Sys;

use App\Admin\Repositories\Sys\SysConfigCategories;
use App\Models\Sys\SysConfigCategories as SysConfigCategoriesModel;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SysConfigCategoriesController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(new SysConfigCategories(), function (Grid $grid) {
            $grid->sortable('sort');
            $grid->column('id', admin_trans_field('id'))->sortable();
            $grid->column('name', admin_trans_field('name'));
            $grid->column('sort', admin_trans_field('sort'));

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->like('name', admin_trans_field('name'));
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
    protected function detail($id): Show
    {
        return Show::make($id, new SysConfigCategories(), function (Show $show) {
            $show->field('id', admin_trans_field('id'));
            $show->field('name', admin_trans_field('name'));
            $show->field('sort', admin_trans_field('sort'));
            $show->field('created_at', admin_trans_field('created_at'));
            $show->field('updated_at', admin_trans_field('updated_at'));
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(new SysConfigCategories(), function (Form $form) {
            $form->display('id');
            $form->text('name', admin_trans_field('name'))->required();
            admin_form_number_field($form->number('sort', admin_trans_field('sort')), '1')->default(0);
        });
    }

    /**
     * 删除分类（有关联配置项时禁止删除）
     *
     * @param int|string $id
     * @return mixed
     */
    public function destroy($id): mixed
    {
        $category = SysConfigCategoriesModel::query()->findOrFail($id);
        if ($category->configs()->exists()) {
            return (new \Dcat\Admin\Http\JsonResponse())->error(__('sys-config-categories.messages.delete_forbidden'));
        }
        return parent::destroy($id);
    }
}
