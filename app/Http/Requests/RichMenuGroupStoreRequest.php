<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RichMenuGroupStoreRequest extends FormRequest
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
     */
    public function rules()
    {
        return [
            'title' => 'required|string',
            'schedule_status' => 'required|boolean:true,false',
            'release_at' => 'nullable|string',
            'removal_at' => 'nullable|string',
            'richMenus' => 'nullable',

        ];
    }

    protected function prepareForValidation()
    {
        // 前端使用 camelCase 命名，轉換成 snake_case
        $this->merge([
            'schedule_status' => $this->scheduleStatus,
            'release_at' => $this->releaseAt,
            'removal_at' => $this->removalAt,
        ]);
    }
}
