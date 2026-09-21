<?php

namespace App\Api\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class MessageDetailRequest extends FormRequest
{
    /**
     * 确定用户是否有权发出此请求
     *
     * 注意：id 来自路由参数 {id}，不在请求体中验证
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
        return [];
    }
}
