<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleTypeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vehicle_types')->ignore($this->vehicleType->id),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('vehicle_types')->ignore($this->vehicleType->id),
            ],
            'details' => 'nullable|string',
            'subtitle' => 'nullable|string',
            'seating_capacity' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,bmp,svg|max:5120',
        ];
    }
}
