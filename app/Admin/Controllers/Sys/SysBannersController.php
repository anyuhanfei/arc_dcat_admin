<?php

namespace App\Admin\Controllers\Sys;

use App\Admin\Repositories\Sys\SysBanners;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SysBannersController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SysBanners(), function (Grid $grid) {
            // 拖拽排序（替代手动 sort 输入）
            $grid->sortable('sort');

            // display 闭包内 $this 是行数据，调控制器方法需捕获 $self
            $self = $this;

            $grid->column('id', admin_trans_field('id'))->sortable();
            $grid->column('position', admin_trans_field('position'))->display(fn ($v) => $self->positionName($v));
            $grid->column('image', admin_trans_field('image'))->display(function ($v) {
                return $v ? '<img src="'.e(full_url($v)).'" style="height:40px;border-radius:4px;">' : '';
            });
            $grid->column('link_type', admin_trans_field('link_type'))->display(fn ($v) => $self->linkTypeName($v));
            // 关联数据按类型展示：article → 文章标题；internal → 内链名称；link/none → 原值
            $grid->column('link_value', admin_trans_field('link_value'))->display(function ($v) use ($self) {
                return $self->linkValueText($this->link_type, $v);
            });
            $grid->column('is_show', admin_trans_field('is_show'))->using(__('sys-banners.enums.is_show'))->label([0 => 'default', 1 => 'success']);
            $grid->column('updated_at', admin_trans_field('updated_at'))->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('position', admin_trans_field('position'))->select($this->positionOptions());
                $filter->equal('link_type', admin_trans_field('link_type'))->select($this->linkTypeOptions());
                $filter->equal('is_show', admin_trans_field('is_show'))->select(__('sys-banners.enums.is_show'));
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
        return Show::make($id, new SysBanners(), function (Show $show) use ($id) {
            $banner = \App\Models\Sys\SysBanners::find($id);

            $show->field('id', admin_trans_field('id'));
            $show->field('position', admin_trans_field('position'))->as(fn ($v) => $this->positionName($v));
            $show->field('image', admin_trans_field('image'))->image();
            $show->field('link_type', admin_trans_field('link_type'))->as(fn ($v) => $this->linkTypeName($v));
            $show->field('link_value', admin_trans_field('link_value'))->as(function ($v) use ($banner) {
                return $this->linkValueText($banner->link_type ?? null, $v);
            });
            $show->field('is_show', admin_trans_field('is_show'))->using(__('sys-banners.enums.is_show'));
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
        return Form::make(new SysBanners(), function (Form $form) {
            $form->display('id', admin_trans_field('id'));
            $form->select('position', admin_trans_field('position'))->options($this->positionOptions())->default('home')->required();
            $form->mediaSelector('image', admin_trans_field('image'))->required();
            // 隐藏字段：saving 注入的 link_value 需有对应字段对象，否则会被 prepareInsert/prepareUpdate 过滤
            $form->hidden('link_value');
            // link_type 动态显隐（Dcat when 级联）：选中类型才渲染对应关联字段；虚拟字段不落库
            $form->select('link_type', admin_trans_field('link_type'))
                ->options($this->linkTypeOptions())
                ->default('none')
                ->required()
                ->when('link', function (Form $form) {
                    $form->text('link_url', admin_trans_field('link_url'))
                        ->rules('required_if:link_type,link');
                })
                ->when('internal', function (Form $form) {
                    $form->select('link_internal', admin_trans_field('link_internal'))
                        ->options($this->internalLinkOptions())
                        ->rules('required_if:link_type,internal');
                })
                ->when('article', function (Form $form) {
                    $form->select('link_article', admin_trans_field('link_article'))
                        ->options(\App\Models\Sys\SysArticles::query()->orderByDesc('id')->pluck('title', 'id'))
                        ->rules('required_if:link_type,article');
                });

            $form->ignore(['link_url', 'link_internal', 'link_article']);

            $form->radio('is_show', admin_trans_field('is_show'))->options(__('sys-banners.enums.is_show'))->default(1);

            // 合并 link_value（link_url 等已 ignore，需从原始请求读取；link_value 可能为 null，用数组分支注入）
            $form->saving(function (Form $form) {
                $type = $form->input('link_type') ?: 'none';
                $value = match ($type) {
                    'link'     => request()->input('link_url'),
                    'internal' => request()->input('link_internal'),
                    'article'  => request()->input('link_article'),
                    default    => null,
                };

                $form->input(['link_value' => $value], true);
            });
        });
    }

    /**
     * 位置选项（config business.banner.positions 名称映射）
     */
    protected function positionOptions(): array
    {
        return collect(config('business.banner.positions'))->mapWithKeys(fn ($cfg, $key) => [$key => $cfg['name'][app()->getLocale()] ?? $key])->toArray();
    }

    /**
     * 关联类型选项（config business.banner.link_types 名称映射；删除某类型则表单自动不展示该项）
     */
    protected function linkTypeOptions(): array
    {
        return collect(config('business.banner.link_types'))->mapWithKeys(fn ($cfg, $key) => [$key => $cfg['name'][app()->getLocale()] ?? $key])->toArray();
    }

    /**
     * 内链选项（config business.banner.internal_links 名称映射）
     */
    protected function internalLinkOptions(): array
    {
        return collect(config('business.banner.internal_links'))->mapWithKeys(fn ($cfg, $key) => [$key => $cfg['name'][app()->getLocale()] ?? $key])->toArray();
    }

    /**
     * 位置名称（未配置返回原值）
     */
    protected function positionName($v): string
    {
        return config('business.banner.positions.'.$v.'.name.'.app()->getLocale()) ?? $v;
    }

    /**
     * 关联类型名称（未配置返回原值）
     */
    protected function linkTypeName($v): string
    {
        return config('business.banner.link_types.'.$v.'.name.'.app()->getLocale()) ?? $v;
    }

    /**
     * 关联数据展示文本：article → 文章标题；internal → 内链名称；link/none → 原值
     */
    protected function linkValueText($type, $value)
    {
        if ($value === null || $value === '') {
            return '-';
        }
        if ($type === 'article') {
            return \App\Models\Sys\SysArticles::query()->find($value)?->title ?? $value;
        }
        if ($type === 'internal') {
            return config('business.banner.internal_links.'.$value.'.name.'.app()->getLocale(), $value);
        }

        return $value;
    }
}
