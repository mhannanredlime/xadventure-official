<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AtvUtvPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'packageName' => 'required|string|max:255',
            'subTitle' => 'nullable|string|max:255',
            'vehicleType' => 'required|exists:vehicle_types,id',
            'details' => 'nullable|string',
            'day_prices' => ['required', 'array', function ($attribute, $value, $fail) {
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
            'day_prices.*.rider_type_id' => 'required|exists:rider_types,id',
            'day_prices.*.price' => 'nullable|numeric|min:0',
            'day_prices.*.type' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    protected function prepareForValidation()
    {
        $dayPrices = $this->input('day_prices');

        if (is_string($dayPrices)) {
            $decoded = json_decode($dayPrices, true);
            $dayPrices = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        $filtered = [];

        foreach ($dayPrices as $p) {
            // skip invalid entries
            if (empty($p['day']) || empty($p['rider_type_id']) || $p['price'] === null || $p['price'] === '') {
                continue;
            }

            $dayLower = strtolower($p['day']);

            $filtered[] = [
                'day' => $dayLower,
                'rider_type_id' => $p['rider_type_id'],
                'price' => (float) $p['price'], 
                'type' => in_array($dayLower, ['fri', 'sat']) ? 'weekend' : 'weekday',
            ];
        }

        $this->merge([
            'day_prices' => array_values($filtered),
        ]);
    }
}
