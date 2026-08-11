<?php

namespace App\Api\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
            'avatar'    => 'nullable|string',
            'nickname'  => 'nullable|string|max:50',
            'signature' => 'nullable|string|max:255',
            'gender'    => 'nullable|integer|in:1,2',   // 兼容 0/1/2 中的男(1)女(2)，修改时不允许选未知(0)
            'birthday'  => 'nullable|date_format:Y-m-d|before:today',  // 生日格式 Y-m-d，不能是未来日期
        ];
    }

    /**
     * 获取验证错误的自定义消息
     */
    public function messages(): array
    {
        return [
            'gender.in'           => '性别只能为男或女',
            'birthday.date_format' => '生日格式不正确，应为 YYYY-MM-DD',
            'birthday.before'     => '生日不能是未来日期',
            'nickname.max'        => '昵称最长50个字符',
            'signature.max'       => '签名最长255个字符',
        ];
    }
}
