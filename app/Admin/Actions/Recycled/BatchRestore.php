<?php

namespace App\Admin\Actions\Recycled;

use Dcat\Admin\Grid\BatchAction;

/**
 * 批量恢复（回收站中使用）.
 */
class BatchRestore extends BatchAction
{
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
        $this->title = '批量恢复';
    }

    public function render()
    {
        $url = admin_route('recycled.restore');
        $model = e($this->modelClass);

        return <<<HTML
<a href="javascript:void(0)" class="grid-batch-restore"
    data-url="{$url}" data-model="{$model}">
    <i class="feather icon-corner-up-left"></i> 批量恢复
</a>
HTML;
    }
}
