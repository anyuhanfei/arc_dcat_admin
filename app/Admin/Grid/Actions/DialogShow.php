<?php

namespace App\Admin\Grid\Actions;

class DialogShow extends OpenFrameAction
{
    /**
     * 行内查看：iframe 弹窗打开详情页（?mini=1 由 Content::composing 切无侧边栏 full 布局；openDcatFrame 由 bootstrap 全局注册）
     */
    public function __construct()
    {
        parent::__construct(
            fn ($action) => $action->parent->urlWithConstraints("{$action->resource()}/{$action->getKey()}"),
            __('admin.show'),
            'feather icon-eye'
        );
    }
}
