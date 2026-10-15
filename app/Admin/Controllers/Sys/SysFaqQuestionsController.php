<?php

namespace App\Admin\Controllers\Sys;

use App\Admin\Repositories\Sys\SysFaqQuestions;
use App\Models\Sys\SysFaqCategories;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SysFaqQuestionsController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SysFaqQuestions(), function (Grid $grid) {
            $grid->showRecycledButton();
            $grid->sortable('sort');
            $grid->column('id', admin_trans_field('id'))->sortable();
            $grid->column('category_id', admin_trans_field('category_id'))->display(function ($v) {
                static $names = null;
                if ($names === null) {
                    $names = SysFaqCategories::query()->pluck('name', 'id');
                }
                return $names[$v] ?? $v;
            });
            $grid->column('question', admin_trans_field('question'))->limit(30);
            $grid->column('answer', admin_trans_field('answer'))->limit(30);
            $grid->column('is_show', admin_trans_field('is_show'))->using(__('sys-faq-questions.enums.is_show'))->label([0 => 'danger', 1 => 'success']);
            $grid->column('created_at', admin_trans_field('created_at'));
        
            $grid->tools(function (Grid\Tools $tools) {
                // 分类管理弹框：统一 helper（openDcatFrame iframe 弹窗 + mini 布局，文案取语言文件）
                $tools->append(admin_dialog_frame_button(
                    admin_base_path('sys/faq-categories'),
                    __('sys-faq-questions.buttons.category_manage')
                ));
            });
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal('category_id', '分类')->select(SysFaqCategories::query()->orderBy('sort')->pluck('name', 'id'));
                $filter->like('question', '问题');
        
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
        return Show::make($id, new SysFaqQuestions(), function (Show $show) {
            $show->field('id');
            $show->field('category_id');
            $show->field('question');
            $show->field('answer');
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
        return Form::make(new SysFaqQuestions(), function (Form $form) {
            $form->display('id');
            $form->select('category_id', admin_trans_field('category_id'))->options(SysFaqCategories::query()->orderBy('sort')->pluck('name', 'id'))->required();
            $form->text('question', admin_trans_field('question'))->required();
            $form->textarea('answer', admin_trans_field('answer'))->rows(5);
            $form->radio('is_show', admin_trans_field('is_show'))->options(__('sys-faq-questions.enums.is_show'))->default(1);
        });
    }
}
