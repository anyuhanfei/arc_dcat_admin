<?php

namespace App\Admin\Controllers\Sys;

use App\Admin\Repositories\Sys\SysArticles;
use App\Models\Sys\SysArticleCategories;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Widgets\Modal;
use Dcat\Admin\Http\Controllers\AdminController;

class SysArticlesController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SysArticles(), function (Grid $grid) {
            $grid->column('id', admin_trans_field('id'))->sortable();
            $grid->column('category_id', admin_trans_field('category_id'))->display(function ($v) {
                static $names = null;
                if ($names === null) {
                    $names = SysArticleCategories::query()->pluck('name', 'id');
                }
                return $names[$v] ?? $v;
            });
            $grid->column('title', admin_trans_field('title'));
            $grid->column('author', admin_trans_field('author'));
            $grid->column('image', admin_trans_field('image'))->display(function ($v) {
                return $v ? '<img src="'.e(full_url($v)).'" style="height:40px;border-radius:4px;">' : '';
            });
            $grid->column('status', admin_trans_field('status'))->using(__('sys-articles.enums.status'))->label([0 => 'default', 1 => 'success']);
            $grid->column('updated_at', admin_trans_field('updated_at'))->sortable();
        
            $grid->tools(function (Grid\Tools $tools) {
                // 文章分类管理弹框：宽度覆盖页面约 80%（独立 class，避免与 FAQ 弹框样式耦合）
                Admin::style('.article-cat-modal .modal-dialog { width: 80% !important; max-width: 80% !important; }');

                $tools->append(
                    Modal::make()
                        ->class('article-cat-modal', true)
                        ->title(__('sys-articles.buttons.category_manage_title'))
                        ->body(
                            '<iframe src="'.admin_base_path('sys/article-categories').'" style="width:100%;height:70vh;border:0;border-radius:4px;"></iframe>'
                        )
                        ->button('<a href="javascript:void(0)" class="btn btn-primary"><i class="feather icon-folder"></i>&nbsp;'.e(__('sys-articles.buttons.category_manage')).'</a>')
                );
            });
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal('category_id', admin_trans_field('category_id'))->select(SysArticleCategories::query()->orderBy('sort')->pluck('name', 'id'));
                $filter->like('title', admin_trans_field('title'));
                $filter->equal('status', admin_trans_field('status'))->select(__('sys-articles.enums.status'));
        
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
        return Show::make($id, new SysArticles(), function (Show $show) {
            $show->field('id', admin_trans_field('id'));
            $show->field('category_id', admin_trans_field('category_id'))->display(function ($v) {
                static $names = null;
                if ($names === null) {
                    $names = SysArticleCategories::query()->pluck('name', 'id');
                }
                return $names[$v] ?? $v;
            });
            $show->field('title', admin_trans_field('title'));
            $show->field('author', admin_trans_field('author'));
            $show->field('image', admin_trans_field('image'))->image('', 60, 60);
            $show->field('summary', admin_trans_field('summary'));
            $show->field('keywords', admin_trans_field('keywords'));
            $show->field('content', admin_trans_field('content'))->unescape();
            $show->field('status', admin_trans_field('status'))->using(__('sys-articles.enums.status'));
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
        return Form::make(new SysArticles(), function (Form $form) {
            $form->display('id');
            $form->select('category_id', admin_trans_field('category_id'))->options(SysArticleCategories::query()->orderBy('sort')->pluck('name', 'id'))->required();
            $form->text('title', admin_trans_field('title'))->required();
            $form->text('author', admin_trans_field('author'));
            $form->mediaSelector('image', admin_trans_field('image'));
            $form->textarea('summary', admin_trans_field('summary'));
            $form->tags('keywords', admin_trans_field('keywords'))
                ->saving(function ($value) {
                    // 提交：tags 数组 → 逗号分隔字符串
                    if (is_array($value)) {
                        return implode(',', array_filter(array_map('trim', $value)));
                    }
                    return $value;
                })
                ->customFormat(function ($value) {
                    // 编辑回显：数据库字符串 → tags 数组
                    if (is_array($value)) {
                        return $value;
                    }
                    return $value ? array_filter(array_map('trim', explode(',', (string) $value))) : [];
                });
            $form->ueditor('content', admin_trans_field('content'));
            $form->radio('status', admin_trans_field('status'))->options(__('sys-articles.enums.status'))->default(0);
        });
    }
}
