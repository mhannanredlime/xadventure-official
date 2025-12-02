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
            $decoded = json_decode($this->day_prices, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge(['day_prices' => $decoded]);
            } else {
                $this->merge(['day_prices' => []]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'packageName' => 'required|string|max:255|unique:packages,name,'.($this->package->id ?? null),
            'subTitle' => 'nullable|string|max:255',
            'packageType' => 'required',

            'details' => 'nullable|string',
            'displayStartingPrice' => 'nullable|numeric|min:0',

            'minParticipant' => 'required|integer|min:1',
            'maxParticipant' => 'required|integer|gte:minParticipant',

            'day_prices' => ['required', 'array', function ($attribute, $value, $fail) {
                // কমপক্ষে 1 day_price price থাকলে pass
                $hasPrice = false;
                foreach ($value as $dayPrice) {
                    if (isset($dayPrice['price']) && $dayPrice['price'] !== null && $dayPrice['price'] !== '') {
                        $hasPrice = true;
                        break;
                    }
                }
                if (! $hasPrice) {
                    $fail('At least one day price must be provided.');
                }
            }],
            'day_prices.*.day' => 'required|string|in:sun,mon,tue,wed,thu,fri,sat',
            'day_prices.*.price' => 'nullable|numeric|min:0', // nullable, মান দিতে হবে না

            // Images
            'images' => $this->isMethod('post') ? 'required|array' : 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }
}
