<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegularPackageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // You can implement permission logic here
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'packageName' => 'required|string|max:255',
            'subTitle' => 'nullable|string|max:255',
            'packageType' => 'required', 
            'details' => 'nullable|string',
            'displayStartingPrice' => 'nullable|numeric|min:0',
            'minParticipant' => 'required|integer|min:1',
            'maxParticipant' => 'required|integer|gte:minParticipant',
            'active_days' => ['required'],
            'active_days.*' => 'in:mon,tue,wed,thu,fri,sat,sun',
            'day_prices' => 'required',
            'day_prices.*.day' => 'required|in:mon,tue,wed,thu,fri,sat,sun',
            'day_prices.*.price' => 'required|numeric|min:1',
            'images' => 'nullable|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ];
    }

    /**
     * Custom messages (optional)
     */
    public function messages(): array
    {
        return [
            'packageName.required' => 'Package name is required.',
            'packageType.in' => 'Invalid package type selected.',
            'maxParticipant.gte' => 'Max participants must be greater than or equal to min participants.',
            'active_days.*.in' => 'Active days must be a valid day of the week.',
            'images.*.image' => 'Each file must be an image.',
        ];
    }
}
