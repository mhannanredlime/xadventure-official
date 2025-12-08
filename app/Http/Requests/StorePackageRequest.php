<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Admin check via middleware
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'base_price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'vehicle_type_ids' => 'required|array',
            'vehicle_type_ids.*' => 'exists:vehicle_types,id',
            // Add other fields from package model if needed
        ];
    }
}
