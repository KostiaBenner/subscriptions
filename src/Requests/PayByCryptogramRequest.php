<?php

namespace Nikservik\Subscriptions\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayByCryptogramRequest extends FormRequest
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
            'tariff' => 'required|exists:tariffs,id',
            'name' => 'required|string',
            'crypt' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'tariff.required' => 'tariff.required',
            'tariff.exists' => 'tariff.exists',
            'name.required' => 'name.required',
            'name.string' => 'name.string',
            'crypt.required' => 'crypt.required',
            'crypt.string' => 'crypt.string',
        ];
    }

}
