<?php

namespace App\Api\Requests\Feedback;

use Illuminate\Foundation\Http\FormRequest;

class FeedbackDetailRequest extends FormRequest
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
            'id' => ['required', 'integer'],
        ];
    }
}
