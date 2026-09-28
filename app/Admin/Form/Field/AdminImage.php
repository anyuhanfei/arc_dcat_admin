<?php

namespace App\Admin\Form\Field;

use Dcat\Admin\Form\Field\Image;
use App\Admin\Form\Field\Concerns\HasMediaUpload;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * 单图上传字段（封装 Dcat Admin Image）.
 *
 * 使用：$form->adminImage('field', '标签')->required();
 */
class AdminImage extends Image
{
    use HasMediaUpload;

    protected $view = 'admin::form.admin-image';

    public function __construct($column, $arguments = [])
    {
        parent::__construct($column, $arguments);

        $this->setupMediaUploadDefaults();

        // 单图最多上传 1 张
        $this->options['fileNumLimit'] = 1;
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
