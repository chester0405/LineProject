<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RichMenuGroupSearchRequest extends FormRequest
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
            'keyword' => 'nullable|string',
            'sort' => 'nullable|in:asc,desc',
            'order_by' => 'nullable|string',
            'limit' => 'nullable|integer',
            'offset' => 'nullable|integer',
            'status' => 'nullable|integer',
        ];
    }
}
