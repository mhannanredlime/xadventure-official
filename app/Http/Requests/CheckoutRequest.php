<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'promo_code' => 'nullable|string|exists:promo_codes,code',
            // Date is implicitly in cart but if sent:
            // 'date' => 'required|date',
        ];
    }
}
