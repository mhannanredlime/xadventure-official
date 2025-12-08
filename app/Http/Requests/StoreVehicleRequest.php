<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'registration_number' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'seo_title' => 'nullable|string|max:255', // Example
        ];
    }
}
