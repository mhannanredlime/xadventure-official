<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AtvUtvPackageRequest extends FormRequest
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
            'packageName' => 'required|string|max:255',
            'subTitle' => 'nullable|string|max:255',
            'vehicleType' => 'required|exists:vehicle_types,id',
            'details' => 'nullable|string',
            'day_prices' => 'required|json',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
