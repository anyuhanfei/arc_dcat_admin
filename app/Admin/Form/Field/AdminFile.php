<?php

namespace App\Admin\Form\Field;

use Dcat\Admin\Form\Field\File;
use App\Integrations\Storage\StorageManager;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * 文件上传字段（封装 Dcat File）.
 *
 * 本地走 Dcat 默认 disk；开启云存储时 autoUpload 落盘改走自研云驱动.
 * 使用：$form->adminFile('wgt', '标签')
 */
class AdminFile extends File
{
    /**
     * autoUpload 独立上传落盘：本地走 Dcat 默认 disk，开云时走自研云驱动.
     */
    public function upload(UploadedFile $file)
    {
        // 本地存储：维持 Dcat 默认落盘逻辑
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

        StorageManager::drive()->putFile($file->getRealPath(), $key);

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
}
