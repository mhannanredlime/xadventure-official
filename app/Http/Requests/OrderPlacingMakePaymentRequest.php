<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderPlacingMakePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => [
                'required',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    $phoneService = new \App\Services\PhoneNumberService;
                    $phoneValidation = $phoneService->validateAndFormat($value);
                    
                    if (!$phoneValidation['valid']) {
                        $fail($phoneValidation['error']);
                    }
                }
            ],
            'customer_address' => 'nullable|string|max:500',
            'payment_method' => 'required|in:credit_card,check_payment,amarpay',
            'create_account' => 'nullable|boolean',
        ];
        
        // Add password validation based on create_account checkbox
        if ($this->boolean('create_account')) {
            $rules['password'] = 'required|string|min:8|confirmed';
            $rules['password_confirmation'] = 'required|string|min:8';
        } else {
            $rules['password'] = 'nullable|string|min:8';
            $rules['password_confirmation'] = 'nullable|string|min:8';
        }
        
        // Add credit card validation if payment method is credit_card
        if ($this->input('payment_method') === 'credit_card') {
            $rules['card_holder'] = 'required|string|max:255';
            $rules['card_number'] = 'required|string|max:19';
            $rules['expiry_date'] = 'required|string|max:5';
            $rules['cvv'] = 'required|string|max:4';
        }
        
        return $rules;
    }
    
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure create_account is cast to boolean
        $this->merge([
            'create_account' => $this->boolean('create_account'),
        ]);
    }
    
    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'Customer name is required.',
            'customer_email.required' => 'Customer email is required.',
            'customer_email.email' => 'Please enter a valid email address.',
            'customer_phone.required' => 'Customer phone number is required.',
            'payment_method.required' => 'Payment method is required.',
            'payment_method.in' => 'Please select a valid payment method.',
            'password.required' => 'Password is required when creating an account.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password_confirmation.required' => 'Please confirm your password.',
            
            // Credit card messages
            'card_holder.required' => 'Card holder name is required for credit card payment.',
            'card_number.required' => 'Card number is required for credit card payment.',
            'card_number.max' => 'Card number must not exceed 19 characters.',
            'expiry_date.required' => 'Expiry date is required for credit card payment.',
            'expiry_date.max' => 'Expiry date must be in MM/YY format.',
            'cvv.required' => 'CVV is required for credit card payment.',
            'cvv.max' => 'CVV must not exceed 4 characters.',
        ];
    }
    
    /**
     * Custom attribute names
     */
    public function attributes(): array
    {
        return [
            'customer_name' => 'customer name',
            'customer_email' => 'customer email',
            'customer_phone' => 'customer phone',
            'customer_address' => 'customer address',
            'payment_method' => 'payment method',
            'create_account' => 'create account',
            'password' => 'password',
            'password_confirmation' => 'password confirmation',
            'card_holder' => 'card holder name',
            'card_number' => 'card number',
            'expiry_date' => 'expiry date',
            'cvv' => 'CVV',
        ];
    }
    
    /**
     * Additional validation rules that require multiple fields
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional validation for expiry date format
            if ($this->input('payment_method') === 'credit_card' && $this->filled('expiry_date')) {
                if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $this->input('expiry_date'))) {
                    $validator->errors()->add('expiry_date', 'Expiry date must be in MM/YY format (e.g., 12/25).');
                }
            }
            
            // Additional validation for card number format
            if ($this->input('payment_method') === 'credit_card' && $this->filled('card_number')) {
                $cardNumber = preg_replace('/\s+/', '', $this->input('card_number'));
                if (!preg_match('/^[0-9]{13,19}$/', $cardNumber)) {
                    $validator->errors()->add('card_number', 'Card number must contain 13 to 19 digits.');
                }
            }
            
            // Additional validation for CVV format
            if ($this->input('payment_method') === 'credit_card' && $this->filled('cvv')) {
                if (!preg_match('/^[0-9]{3,4}$/', $this->input('cvv'))) {
                    $validator->errors()->add('cvv', 'CVV must contain 3 or 4 digits.');
                }
            }
        });
    }
}