<?php

namespace App\Admin\Controllers\Sys;

use App\Admin\Repositories\Sys\SysAppVersions;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SysAppVersionsController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SysAppVersions(), function (Grid $grid) {
            // 版本号数字从大到小
            $grid->model()->orderBy('version_code', 'desc');

            // display 闭包内 $this 是行数据，调控制器方法需捕获 $self
            $self = $this;

            $grid->column('id', admin_trans_field('id'))->sortable();
            $grid->column('app_key', admin_trans_field('app_key'))->display(fn ($v) => $self->appKeyName($v));
            $grid->column('version', admin_trans_field('version'));
            $grid->column('force_update', admin_trans_field('force_update'))->using($this->enumMap('sys-app-versions.enums.force_update', [0 => '否', 1 => '是']))->label([0 => 'default', 1 => 'success']);
            $grid->column('is_show', admin_trans_field('is_show'))->using($this->enumMap('sys-app-versions.enums.is_show', [0 => '隐藏', 1 => '显示']))->label([0 => 'default', 1 => 'success']);
            $grid->column('updated_at', admin_trans_field('updated_at'))->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('app_key', admin_trans_field('app_key'))->select($this->appKeyOptions());
                $filter->equal('version_code', admin_trans_field('version_code'));
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
        return Show::make($id, new SysAppVersions(), function (Show $show) {
            $show->field('id', admin_trans_field('id'));
            // as 闭包执行时作用域会被绑定为模型类，不能调用控制器 protected 方法，此处内联名称映射
            $show->field('app_key', admin_trans_field('app_key'))->as(function ($v) {
                return config('business.app_version.apps.'.$v.'.name.'.app()->getLocale()) ?? $v;
            });
            $show->field('version', admin_trans_field('version'));
            $show->field('version_code', admin_trans_field('version_code'));
            $show->field('description', admin_trans_field('description'))->unescape();
            $show->field('wgt', admin_trans_field('wgt'))->file();
            $show->field('apk', admin_trans_field('apk'))->file();
            $show->field('ios_store_url', admin_trans_field('ios_store_url'))->link();
            $show->field('harmony_store_url', admin_trans_field('harmony_store_url'))->link();
            $show->field('force_update', admin_trans_field('force_update'))->using($this->enumMap('sys-app-versions.enums.force_update', [0 => '否', 1 => '是']));
            $show->field('is_show', admin_trans_field('is_show'))->using($this->enumMap('sys-app-versions.enums.is_show', [0 => '隐藏', 1 => '显示']));
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
        return Form::make(new SysAppVersions(), function (Form $form) {
            $form->display('id', admin_trans_field('id'));

            // 端（App）下拉；仅配置一个端时隐藏下拉并默认该唯一值
            // （select->disable() 渲染 disabled 属性，浏览器不会提交，故用 hidden 保证提交默认值）
            $apps = config('business.app_version.apps', []);
            if (count($apps) === 1) {
                $form->hidden('app_key')->default(array_key_first($apps));
            } else {
                $form->select('app_key', admin_trans_field('app_key'))->options($this->appKeyOptions())->required();
            }

            $form->text('version', admin_trans_field('version'))->required()->rules(['regex:/^\d+\.\d+\.\d+$/']);
            $form->number('version_code', admin_trans_field('version_code'))->required()->rules('integer|min:1|max:999999999');
            // wgt/apk 文件上传：封装于 admin_form_file_field（自动上传、自动重命名、不同步移除、扩展名与大小限制）
            $wgtLabel = admin_trans_field('wgt');
            $apkLabel = admin_trans_field('apk');
            admin_form_file_field($form->file('wgt', $wgtLabel), ['wgt'], 100, true);
            admin_form_file_field($form->file('apk', $apkLabel), ['apk'], 100);
            $form->url('ios_store_url', admin_trans_field('ios_store_url'));
            $form->url('harmony_store_url', admin_trans_field('harmony_store_url'));
            $form->radio('force_update', admin_trans_field('force_update'))->options(__('sys-app-versions.enums.force_update'))->default(1);
            $form->radio('is_show', admin_trans_field('is_show'))->options(__('sys-app-versions.enums.is_show'))->default(1);
            $form->richEditor('description', admin_trans_field('description'));

            $form->display('created_at', admin_trans_field('created_at'));
            $form->display('updated_at', admin_trans_field('updated_at'));
        });
    }

    /**
     * 端（App）选项（config business.app_version.apps 名称映射）
     */
    protected function appKeyOptions(): array
    {
        return collect(config('business.app_version.apps'))->mapWithKeys(fn ($cfg, $key) => [$key => $cfg['name'][app()->getLocale()] ?? $key])->toArray();
    }

    /**
     * 枚举映射数组：语言文件缺失（如 en locale 下无 sys-app-versions 语言文件，__() 返回 key 字符串）时回退默认数组
     */
    protected function enumMap(string $key, array $fallback): array
    {
        $map = __($key);

        return is_array($map) ? $map : $fallback;
    }

    /**
     * 端名称（未配置返回原值）
     */
    protected function appKeyName($v): string
    {
        return config('business.app_version.apps.'.$v.'.name.'.app()->getLocale()) ?? $v;
    }
}
