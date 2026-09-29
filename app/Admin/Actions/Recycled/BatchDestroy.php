<?php

namespace App\Admin\Actions\Recycled;

use Dcat\Admin\Grid\BatchAction;

/**
 * 批量销毁（回收站中使用）.
 */
class BatchDestroy extends BatchAction
{
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
        $this->title = '批量销毁';
    }

    public function render()
    {
        $url = admin_route('recycled.forceDelete');
        $model = e($this->modelClass);

        return <<<HTML
<a href="javascript:void(0)" class="grid-batch-destroy"
    data-url="{$url}" data-model="{$model}">
    <i class="feather icon-alert-triangle"></i> 批量销毁
</a>
HTML;
    }
}
