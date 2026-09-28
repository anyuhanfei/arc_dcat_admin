<?php

namespace App\Admin\Form\Field;

use Dcat\Admin\Form\Field\MultipleImage;
use App\Admin\Form\Field\Concerns\HasMediaUpload;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * 多图上传字段（封装 Dcat Admin MultipleImage）.
 *
 * 使用：$form->adminMultipleImage('field', '标签')->limit(5)->sortable();
 */
class AdminMultipleImage extends MultipleImage
{
    use HasMediaUpload;

    protected $view = 'admin::form.admin-image';

    public function __construct($column, $arguments = [])
    {
        parent::__construct($column, $arguments);

        $this->setupMediaUploadDefaults();

        // 多图模式：媒体库多选
        $this->mediaMultiple(true);

        // 默认可排序
        $this->sortable();
    }

    /**
     * 上传后生成 thumb 前缀缩略图.
     */
    protected function prepareFile(UploadedFile $file)
    {
        parent::prepareFile($file);

        $this->generateThumb($file);
    }

    /**
     * 渲染时注入媒体库 JS.
     */
    public function render()
    {
        $this->renderMediaScript();

        return parent::render();
    }
}
