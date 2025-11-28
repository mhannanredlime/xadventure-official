<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegularPackage extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'packageName' => ['required', 'string', 'max:255'],
            'subTitle' => ['nullable', 'string', 'max:255'],
            'packageType' => ['required', 'numeric', 'exists:package_types,id'],
            'details' => ['nullable', 'string'],
            'displayStartingPrice' => ['required', 'numeric', 'min:50'],
            'minParticipant' => ['required', 'integer', 'min:1'],
            'maxParticipant' => ['required', 'integer', 'min:1', 'gte:minParticipant'],
            'active_days' => ['required', 'array', 'min:1'],
            'active_days.*' => ['string', 'distinct'],
            'day_prices' => ['required', 'array', 'min:1'],
            'day_prices.*' => ['numeric', 'min:0'],
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120', 'distinct'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            // Required fields
            'packageName.required' => 'The package name is required.',
            'packageType.required' => 'The package type is required.',
            'displayStartingPrice.required' => 'The display starting price is required.',
            'minParticipant.required' => 'The minimum participants field is required.',
            'maxParticipant.required' => 'The maximum participants field is required.',
            'images.required' => 'At least one image is required.',
            'images.min' => 'Please upload at least one image for the package.',
            'active_days.required' => 'Please select at least one active day.',
            'day_prices.required' => 'Please enter price for at least one day.',

            // String rules
            'packageName.string' => 'The package name must be a string.',
            'subTitle.string' => 'The subtitle must be a string.',
            'details.string' => 'The details must be a string.',
            'active_days.*.string' => 'Each active day must be a valid string.',

            // Numeric and min rules
            'packageType.numeric' => 'The package type must be valid.',
            'packageType.exists' => 'The selected package type does not exist.',
            'displayStartingPrice.numeric' => 'The display starting price must be a valid number.',
            'displayStartingPrice.min' => 'The display starting price must be at least :min.',
            'minParticipant.integer' => 'The minimum participants must be an integer.',
            'minParticipant.min' => 'The minimum participants must be at least :min.',
            'maxParticipant.integer' => 'The maximum participants must be an integer.',
            'maxParticipant.min' => 'The maximum participants must be at least :min.',
            'maxParticipant.gte' => 'The maximum participants must be greater than or equal to the minimum participants.',
            'day_prices.*.numeric' => 'Each day price must be a valid number.',
            'day_prices.*.min' => 'Each day price must be at least 0.',

            // Array rules
            'images.array' => 'The images must be an array.',
            'active_days.array' => 'Active days must be an array.',
            'day_prices.array' => 'Day prices must be an array.',

            // Image rules
            'images.*.image' => 'Each uploaded file must be a valid image.',
            'images.*.mimes' => 'Allowed image formats: jpeg, png, jpg, webp, svg.',
            'images.*.max' => 'Maximum image size allowed is 5MB per image.',
            'images.*.distinct' => 'Duplicate images are not allowed.',
        ];
    }
}
