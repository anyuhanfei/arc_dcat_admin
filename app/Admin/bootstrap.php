<?php

use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Form;
use Dcat\Admin\Grid\Filter;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Show;

/**
 * Dcat-admin - admin builder based on Laravel.
 * @author jqh <https://github.com/jqhph>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 *
 * extend custom field:
 * Dcat\Admin\Form::extend('php', PHPEditor::class);
 * Dcat\Admin\Grid\Column::extend('php', PHPEditor::class);
 * Dcat\Admin\Grid\Filter::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

// 顶栏右侧语言切换下拉（多语言：zh_CN / en）
\Dcat\Admin\Admin::navbar()->right(view('admin.locale-switcher'));

// 全局按钮内容垂直居中，统一系统按钮与自定义按钮的显示效果
Admin::style('.btn { display: inline-flex; align-items: center; justify-content: center; line-height: 1.4;}');

// UEditor 富文本浮层置顶：多图/视频/音乐等对话框（#edui_fixedlayer 默认 z-index:9999）
// 远低于 Dcat layer 弹窗（zIndex 起始 19891014），会被表单弹窗压住；这里提到 99999999 保证浮在最上层
Admin::style('#edui_fixedlayer, .edui-dialog, .edui-dialog-modalmask, .edui-popup { z-index: 99999999 !important; }');

// 全部 Grid 弹窗化：新增/编辑/查看在 80% 弹窗中完成（菜单管理用 Tree 组件，天然不受影响）
Grid::resolving(function (Grid $grid) {
    $grid->enableDialogCreate();                        // 新增按钮 → 弹窗
    $grid->option('dialog_form_area', ['80%', '80%']);  // 弹窗尺寸 80%
    config([
        'admin.grid.actions.view' => \App\Admin\Grid\Actions\DialogShow::class,
        'admin.grid.actions.edit' => \App\Admin\Grid\Actions\DialogEdit::class,
    ]);
});

// 全局 iframe 弹框打开函数：统一所有自定义弹框（Admin::script 自动包 Dcat.ready；window 挂载供行内 onclick 访问）
Admin::script(
    'window.openDcatFrame = function (url, title) {'
    .'var sep = url.indexOf("?") > -1 ? "&" : "?";'
    .'layer.open({ type: 2, title: title || "", content: url + sep + "mini=1", area: ["80%", "80%"], maxmin: true });'
    .'};'
);

// 弹窗内容（?_dialog_form_=1 请求）统一使用无侧边栏布局
Content::composing(function (Content $content) {
    if (request('_dialog_form_')) {
        $content->view('admin::layouts.form-content');
    }
});

// iframe 弹窗（独立页面）：mini 参数 → full 布局（无侧边栏 + 完整资源含 jQuery）
Content::composing(function (Content $content) {
    if (! request('mini')) {
        return;
    }

    $content->description('');      // 去掉详情页正文头部标题（与弹窗标题重复；full 布局下实际标题来自 Show panel，见下方 Show::composing）
    $content->full();
    Admin::style('.full-page .content { padding: 15px; }');  // mini 弹框内容边距（.full-page 仅 mini/full 页面使用）

    // 仅详情页（路由方法 show）去掉外围灰色背景；列表弹框页保留灰底白卡片
    if (request()->route() && request()->route()->getActionMethod() === 'show') {
        Admin::style('body.dcat-admin-body.full-page { background-color: #fff !important; }');
    }
});

// 弹窗详情页：去掉面板标题与操作按钮（避免标题重复/整页跳转，独立详情页不受影响）
Show::composing(function (Show $show) {
    if (! request('_dialog_form_') && ! request('mini')) {
        return;
    }

    $show->panel()->tools(function (Dcat\Admin\Show\Tools $tools) {
        $tools->disableEdit();
        $tools->disableDelete();
        $tools->disableQuickEdit();
        $tools->disableList();
    });

    $show->panel()->title('');              // 去掉面板标题（默认 trans('admin.detail')），与弹窗标题重复
    $show->panel()->with(['tools' => '']);  // 隐藏工具区（含空 header 条）；须在 tools() 禁用回调之后覆盖，避免类型约束报错
});
