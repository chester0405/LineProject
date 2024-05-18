<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RichMenuStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * 驗證規則
     */
    public function rules()
    {
        return [
            'title' => 'required|string',
            'chat_bar_text' => 'required|string',
            'selected' => 'required|boolean:true,false',
            'publish_status' => 'required|string',
            'image' => 'nullable|string',
            'size' => 'nullable|array',
            'areas' => 'nullable|array',
            'areas.*.bounds' => 'nullable|array',
            'areas.*.action' => 'nullable|array',
        ];
    }

    protected function prepareForValidation()
    {
        // 前端使用 camelCase 命名，轉換成 snake_case
        $this->merge([
            'chat_bar_text' => $this->chatBarText,
            'publish_status' => $this->publishStatus,
        ]);
    }
}
