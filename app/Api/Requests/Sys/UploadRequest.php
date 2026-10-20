<?php

namespace App\Api\Requests\Sys;

use Illuminate\Foundation\Http\FormRequest;

class UploadRequest extends FormRequest
{
    /**
     * 确定用户是否有权发出此请求（登录由 auth:sanctum 中间件保证）
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 允许的扩展名来自 config('cloud.upload')，大小上限 UPLOAD_MAX_SIZE_MB
     */
    public function rules(): array
    {
        $maxKb = (int) config('cloud.upload.max_size_mb', 100) * 1024;

        return [
            'file' => [
                'required',
                'file',
                "max:{$maxKb}",
                function ($attribute, $value, $fail) {
                    $ext = strtolower((string) $value->getClientOriginalExtension() ?: $value->extension());
                    $allowed = array_merge(
                        config('cloud.upload.image_ext', []),
                        config('cloud.upload.video_ext', []),
                        config('cloud.upload.doc_ext', [])
                    );
                    if (! in_array($ext, $allowed, true)) {
                        $fail('不支持的文件类型：.' . $ext . '，允许 ' . implode('/', $allowed));
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => '请选择要上传的文件',
            'file.file'     => '上传内容不是有效文件',
            'file.max'      => '文件大小超过上限（' . config('cloud.upload.max_size_mb', 100) . 'MB）',
        ];
    }
}
