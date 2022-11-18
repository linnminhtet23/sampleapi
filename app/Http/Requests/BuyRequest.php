<?php

namespace App\Http\Requests;

class BuyRequest extends FormRequest
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
            'merchant_name' => 'required',
            'whole_total' => 'required',
            'paid' => 'required',
            'credit' => 'required',
            'shop_id' => 'required',
            'single_buys' => 'required',
        ];
    }
}
