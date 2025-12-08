<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Admin middleware handles auth
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'name' => 'required|string|max:255|unique:vehicles,name',
            'details' => 'nullable|string',
            'op_start_date' => 'nullable|date_format:Y-m-d',
            'is_active' => 'boolean', // Checkbox handling often needs prepareForValidation if not simple boolean
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,bmp,svg|max:5120',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('is_active')) {
             $this->merge(['is_active' => $this->is_active === 'on' || $this->is_active === '1' || $this->is_active === true]);
        } else {
             $this->merge(['is_active' => false]);
        }
    }
}
