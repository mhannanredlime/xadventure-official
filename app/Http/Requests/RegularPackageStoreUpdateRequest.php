<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegularPackageStoreUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has('active_days') && is_string($this->active_days)) {
            $this->merge([
                'active_days' => json_decode($this->active_days, true) ?? [],
            ]);
        }

        if ($this->has('day_prices') && is_string($this->day_prices)) {
            $this->merge([
                'day_prices' => json_decode($this->day_prices, true) ?? [],
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'packageName' => 'required|string|max:255|unique:packages,name,' . ($this->package->id ?? null),
            'subTitle' => 'nullable|string|max:255',
            'packageType' => 'required',

            'details' => 'nullable|string',
            'displayStartingPrice' => 'nullable|numeric|min:0',

            'minParticipant' => 'required|integer|min:1',
            'maxParticipant' => 'required|integer|gte:minParticipant',

            'active_days' => 'required|array',
            'active_days.*' => 'in:mon,tue,wed,thu,fri,sat,sun',

            'day_prices' => 'required|array',
            'day_prices.*' => 'required|numeric|min:1',

            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }
}
