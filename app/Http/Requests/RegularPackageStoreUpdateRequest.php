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


            'day_prices' => 'required',
            'day_prices.*' => 'required|min:1',

            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }
}
