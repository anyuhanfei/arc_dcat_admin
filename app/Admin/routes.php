<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

// 自定义表单字段注册：媒体库选择器（App\Admin\Form\Field\MediaSelector）
\Dcat\Admin\Form::extend('mediaSelector', \App\Admin\Form\Field\MediaSelector::class);

// 自定义表单字段注册：富文本编辑器（wangEditor v5，App\Admin\Form\Field\RichEditor）
\Dcat\Admin\Form::extend('richEditor', \App\Admin\Form\Field\RichEditor::class);

// 自定义表单字段注册：封装图片上传（自动上传/日期目录/重命名/缩略图/媒体库联动）
\Dcat\Admin\Form::extend('adminImage', \App\Admin\Form\Field\AdminImage::class);
\Dcat\Admin\Form::extend('adminMultipleImage', \App\Admin\Form\Field\AdminMultipleImage::class);

// 登录验证码刷新（无需登录，只带路由前缀不带中间件）
Route::prefix(config('admin.route.prefix'))
    ->withoutMiddleware(config('admin.route.middleware'))
    ->get('auth/captcha', [\App\Admin\Controllers\AuthController::class, 'getCaptcha']);

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->get('media-selector/list', [\App\Admin\Controllers\MediaSelectorController::class, 'list']);
    $router->post('media-selector/upload', [\App\Admin\Controllers\MediaSelectorController::class, 'upload']);
    $router->post('media-selector/delete', [\App\Admin\Controllers\MediaSelectorController::class, 'delete']);
    $router->post('rich-editor/upload', [\App\Admin\Controllers\RichEditorController::class, 'upload']);

    // 回收站：统一恢复 / 永久删除
    $router->post("recycled/restore", [\App\Admin\Controllers\RecycledController::class, "handle"])->name("recycled.restore");
    $router->post("recycled/forceDelete", [\App\Admin\Controllers\RecycledController::class, "handle"])->name("recycled.forceDelete");

    $router->resource('sys/banners', \App\Admin\Controllers\Sys\SysBannersController::class);
    $router->resource('sys/agreements', \App\Admin\Controllers\Sys\SysAgreementsController::class);
    $router->resource('sys/faq-categories', \App\Admin\Controllers\Sys\SysFaqCategoriesController::class);
    $router->resource('sys/faq-questions', \App\Admin\Controllers\Sys\SysFaqQuestionsController::class);
    $router->resource('sys/article-categories', \App\Admin\Controllers\Sys\SysArticleCategoriesController::class);
    $router->resource('sys/articles', \App\Admin\Controllers\Sys\SysArticlesController::class);
    $router->resource('sys/notices', \App\Admin\Controllers\Sys\SysNoticesController::class);
    $router->get('sys/notice-reads', \App\Admin\Controllers\Sys\SysNoticeReadsController::class.'@index');
    $router->resource('sys/app-versions', \App\Admin\Controllers\Sys\SysAppVersionsController::class);
    $router->resource('sys/config-categories', \App\Admin\Controllers\Sys\SysConfigCategoriesController::class);
    $router->resource('sys/configs', \App\Admin\Controllers\Sys\SysConfigsController::class);
    $router->get('sys/config-editor', [\App\Admin\Controllers\Sys\SysConfigEditorController::class, 'index']);
    $router->post('sys/config-editor', [\App\Admin\Controllers\Sys\SysConfigEditorController::class, 'store']);
    $router->get('sys/configs/{id}/fill-value', [\App\Admin\Controllers\Sys\SysConfigsController::class, 'fillValue']);
    $router->put('sys/configs/{id}/fill-value', [\App\Admin\Controllers\Sys\SysConfigsController::class, 'saveFillValue']);

    $router->resource('feedback', \App\Admin\Controllers\Feedback\FeedbackController::class);

    $router->resource('messages', \App\Admin\Controllers\Message\MessageController::class);
    $router->put('messages/{id}/recall', [\App\Admin\Controllers\Message\MessageController::class, 'recall']);

    $router->resource('user/users', \App\Admin\Controllers\User\UsersController::class);
    $router->get('user/user-wallet-records', \App\Admin\Controllers\User\UserWalletRecordsController::class.'@index');

});

// ============================================================
// 回收站 Grid macro：$grid->showRecycledButton()
// ============================================================
\Dcat\Admin\Grid::macro('showRecycledButton', function (array $config = []) {
    /** @var \Dcat\Admin\Grid $this */
    $recycled = request('recycled');

    // 获取当前模型类名
    $modelClass = get_class($this->model()->repository()->model());

    if (! $recycled) {
        // 正常模式：在筛选按钮旁加"回收站"入口，点击弹 iframe
        $url = request()->fullUrlWithQuery(['recycled' => 1]);
        $btnId = 'btn-open-recycled-' . uniqid();
        $this->tools()->append(<<<HTML
<a href="javascript:void(0)" class="btn btn-white waves-effect" id="{$btnId}">
    <i class="feather icon-trash-2"></i> 回收站
</a>
HTML
        );

        \Dcat\Admin\Admin::script(<<<JS
\$('#{$btnId}').on('click', function () {
    layer.open({
        type: 2,
        title: '回收站',
        area: ['90%', '90%'],
        content: '{$url}'
    });
});
JS
        );

        return;
    }

    // ===== 回收站模式（在 iframe 弹窗中打开）=====
    // 隐藏侧边栏、导航栏、底部，和 Dcat 添加/编辑弹窗一样
    \Dcat\Admin\Admin::style('body.sidebar-mini{margin-left:0!important} .main-menu,.header-navbar,.main-footer{display:none!important} .app-content,.content-wrapper{margin-left:0!important;padding-left:10px!important;padding-right:10px!important;width:100%!important} .content-header{display:none}');

    // 1. 查询改为只查软删除数据
    $this->model()->addQuery('onlyTrashed');

    // 2. 隐藏新建按钮
    $this->disableCreateButton();

    // 3. 替换操作列：恢复 + 销毁
    $this->actions(function (\Dcat\Admin\Grid\Displayers\Actions $actions) use ($modelClass) {
        $actions->disableDelete();
        $actions->disableEdit();

        $key = $actions->getKey();
        $restoreUrl = admin_route('recycled.restore');
        $destroyUrl = admin_route('recycled.forceDelete');
        $model = e($modelClass);

        $actions->append(<<<HTML
<a href="javascript:void(0)" class="grid-row-restore" data-url="{$restoreUrl}" data-model="{$model}" data-id="{$key}" style="color:#5cb85c;margin-right:8px;">
    <i class="feather icon-corner-up-left"></i> 恢复
</a>
<a href="javascript:void(0)" class="grid-row-destroy" data-url="{$destroyUrl}" data-model="{$model}" data-id="{$key}" style="color:#dd4b39;">
    <i class="feather icon-alert-triangle"></i> 销毁
</a>
HTML
        );
    });

    // 4. 批量操作：用 Dcat 自带的"已选择 n 项"下拉菜单，顶替默认删除按钮
    $this->batchActions(function (\Dcat\Admin\Grid\Tools\BatchActions $batch) use ($modelClass) {
        $batch->disableDelete();
        $batch->add(new \App\Admin\Actions\Recycled\BatchRestore($modelClass));
        $batch->add(new \App\Admin\Actions\Recycled\BatchDestroy($modelClass));
    });

    // 5. 顶部工具按钮：全部恢复、全部销毁
    $restoreUrl = admin_route('recycled.restore');
    $destroyUrl = admin_route('recycled.forceDelete');
    $model = e($modelClass);

    $this->tools()->append(<<<HTML
<button class="btn btn-sm btn-success" id="recycled-restore-all" data-url="{$restoreUrl}" data-model="{$model}">
    <i class="feather icon-corner-up-left"></i> 全部恢复
</button>
<button class="btn btn-sm btn-danger" id="recycled-destroy-all" data-url="{$destroyUrl}" data-model="{$model}">
    <i class="feather icon-alert-triangle"></i> 全部销毁
</button>
HTML
    );

    // 6. 注入交互 JS
    \Dcat\Admin\Admin::script(<<<JS
$(function () {
    function reloadParent() {
        try { parent.Dcat.reload(); } catch(e) {}
    }

    function doRequest(url, data, msg) {
        $.ajax({url: url, type: 'POST', data: data, success: function (res) {
            if (res.status) { Dcat.success(res.message); Dcat.reload(); reloadParent(); }
            else { Dcat.error(res.message); }
        }});
    }

    // 行恢复
    $(document).on('click', '.grid-row-restore', function () {
        var url = $(this).data('url'), model = $(this).data('model'), id = $(this).data('id');
        Dcat.confirm('确定恢复这条记录？', '', function () {
            doRequest(url, {model: model, ids: [id]});
        });
    });
    // 行销毁
    $(document).on('click', '.grid-row-destroy', function () {
        var url = $(this).data('url'), model = $(this).data('model'), id = $(this).data('id');
        Dcat.confirm('警告：销毁后数据将永久删除，无法恢复！确定销毁？', '', function () {
            doRequest(url, {model: model, ids: [id]});
        });
    });
    // 批量恢复
    $(document).on('click', '.grid-batch-restore', function () {
        var url = $(this).data('url'), model = $(this).data('model');
        var ids = Dcat.grid.selectedRows();
        if (!ids || !ids.length) { Dcat.warning('请先选择记录'); return; }
        Dcat.confirm('确定批量恢复选中的 ' + ids.length + ' 条记录？', '', function () {
            doRequest(url, {model: model, ids: ids});
        });
    });
    // 批量销毁
    $(document).on('click', '.grid-batch-destroy', function () {
        var url = $(this).data('url'), model = $(this).data('model');
        var ids = Dcat.grid.selectedRows();
        if (!ids || !ids.length) { Dcat.warning('请先选择记录'); return; }
        Dcat.confirm('警告：销毁后数据将永久删除！确定销毁选中的 ' + ids.length + ' 条记录？', '', function () {
            doRequest(url, {model: model, ids: ids});
        });
    });
    // 全部恢复
    $(document).on('click', '#recycled-restore-all', function () {
        var url = $(this).data('url'), model = $(this).data('model');
        Dcat.confirm('确定恢复回收站中的全部记录？', '', function () {
            doRequest(url, {model: model, all: true});
        });
    });
    // 全部销毁
    $(document).on('click', '#recycled-destroy-all', function () {
        var url = $(this).data('url'), model = $(this).data('model');
        Dcat.confirm('警告：将永久删除回收站中的全部记录，无法恢复！确定？', '', function () {
            doRequest(url, {model: model, all: true});
        });
    });
});
JS
    );
});
