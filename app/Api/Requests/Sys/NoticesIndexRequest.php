<?php

namespace App\Api\Requests\Sys;

use Illuminate\Foundation\Http\FormRequest;

class NoticesIndexRequest extends FormRequest
{
    /**
     * 确定用户是否有权发出此请求
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 获取应用于请求的验证规则
     */
    public function rules(): array
    {
        return [
            'page'     => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
