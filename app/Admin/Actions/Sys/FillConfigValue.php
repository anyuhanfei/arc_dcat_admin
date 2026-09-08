<?php

namespace App\Admin\Actions\Sys;

use Dcat\Admin\Grid\RowAction;

class FillConfigValue extends RowAction
{
    /**
     * 按钮标题
     *
     * @return string
     */
    public function title(): string
    {
        return __('sys-configs.buttons.fill_value');
    }

    /**
     * 按钮 HTML（弹框打开填写值页面）
     *
     * @return string
     */
    public function html(): string
    {
        return admin_dialog_frame_button(
            admin_base_path('sys/configs/' . $this->getKey() . '/fill-value'),
            $this->title(),
            'feather icon-edit',
            ''
        );
    }
}
