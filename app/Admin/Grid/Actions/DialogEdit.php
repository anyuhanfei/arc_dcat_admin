<?php

namespace App\Admin\Grid\Actions;

use Dcat\Admin\Form;
use Dcat\Admin\Grid\RowAction;

class DialogEdit extends RowAction
{
    /**
     * 行内编辑：在弹窗中打开编辑表单
     */
    public function title()
    {
        return $this->title ?: '<i class="feather icon-edit-1"></i> '.__('admin.edit').' &nbsp;&nbsp;';
    }

    public function render()
    {
        [$width, $height] = $this->parent->option('dialog_form_area');

        Form::dialog(__('admin.edit'))
            ->click(".{$this->getElementClass()}")
            ->dimensions($width, $height)
            ->forceRefresh()
            ->success('Dcat.reload()');

        $this->setHtmlAttribute([
            'data-url' => $this->parent->getEditUrl($this->getKey()),
        ]);

        return parent::render();
    }
}
