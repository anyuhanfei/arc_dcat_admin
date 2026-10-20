<?php

namespace App\Admin\Form\Field;

use Dcat\Admin\Form\Field\MultipleImage;
use App\Admin\Form\Field\Concerns\HasMediaUpload;
use App\Integrations\Storage\StorageManager;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
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
     * autoUpload 独立上传落盘：本地走 Dcat 默认 disk，开云时走自研云驱动.
     */
    public function upload(UploadedFile $file)
    {
        if (! StorageManager::isCloud()) {
            return parent::upload($file);
        }

        $request = request();
        if (! $request->get('_id')) {
            return $this->responseErrorMessage('Missing id');
        }

        if ($errors = $this->getValidationErrors($file)) {
            return $this->responseValidationMessage($errors);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $this->name = date('Ymd_His').'_'.Str::random(8).'.'.$ext;
        $key = date('Ymd').'/'.$this->name;

        // 原图传云，缩略图只写本地 public disk（不传云）
        StorageManager::drive()->putFile($file->getRealPath(), $key);
        $this->generateLocalThumb($file, $key);

        $url = StorageManager::drive()->url($key);

        return $this->responseUploaded($this->saveFullUrl ? $url : $key, $url);
    }

    /**
     * 回显 URL：云模式相对键走云驱动拼接，绝对 URL 原样返回.
     */
    public function objectUrl($path)
    {
        if (StorageManager::isCloud()) {
            if (URL::isValidUrl($path)) {
                return $path;
            }

            return StorageManager::drive()->url($path);
        }

        return parent::objectUrl($path);
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
