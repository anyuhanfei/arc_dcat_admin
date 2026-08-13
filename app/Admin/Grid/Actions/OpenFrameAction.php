<?php

namespace App\Admin\Grid\Actions;

use Dcat\Admin\Grid\RowAction;

class OpenFrameAction extends RowAction
{
    /**
     * @var string|\Closure 弹框 URL；闭包签名 fn (OpenFrameAction $action) => string（可用 $action->getKey()）
     */
    protected $frameUrl;

    /**
     * @var string 弹框标题（悬浮提示 + 弹窗标题）
     */
    protected $frameTitle;

    /**
     * @var string 图标类
     */
    protected $frameIcon;

    /**
     * 通用行内弹框按钮（打开 iframe 弹窗，走全局 openDcatFrame：mini 布局无侧边栏、80% 尺寸）
     *
     * @param string|\Closure $url   弹框 URL；可传闭包 fn ($action) => ...（可用 $action->getKey()）
     * @param string          $title 弹框标题（悬浮提示）
     * @param string          $icon  图标类
     */
    public function __construct($url, $title, $icon = 'feather icon-folder')
    {
        $this->frameUrl = $url;
        $this->frameTitle = $title;
        $this->frameIcon = $icon;
    }

    public function title()
    {
        return '<i class="'.$this->frameIcon.'"></i>&nbsp;&nbsp;';
    }

    public function render()
    {
        $url = $this->frameUrl instanceof \Closure
            ? call_user_func($this->frameUrl, $this)
            : $this->frameUrl;

        $this->setHtmlAttribute([
            'data-url' => $url,
            'title'    => $this->frameTitle,
            'onclick'  => 'window.openDcatFrame(this.dataset.url, '.json_encode($this->frameTitle).')',
        ]);

        return parent::render();
    }
}
