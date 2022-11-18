<?php

namespace App\Http\Requests;

class SaleRequest extends FormRequest
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
            'customer_name' => 'required',
            'purchase_total' => 'required',
            'sale_record_total' => 'required',
            'extra_charges' => 'required',
            'whole_total' => 'required',
            'paid' => 'required',
            'credit' => 'required',
            'shop_id' => 'required',
            'single_sales' => 'required',
        ];
    }
}
