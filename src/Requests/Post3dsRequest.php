<?php

namespace Nikservik\Subscriptions\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Post3dsRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'MD' => 'required',
            'PaRes' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'MD.required' => 'MD.required',
            'PaRes.required' => 'PaRes.required',
            'PaRes.string' => 'PaRes.string',
        ];
    }

}
