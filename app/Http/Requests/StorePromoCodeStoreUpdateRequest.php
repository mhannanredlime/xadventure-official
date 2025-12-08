<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePromoCodeStoreUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $promoCode = $this->route('promo_code');
        $promoCodeId = $promoCode?->id ?? null;

        return [
            'code' => 'required|string|max:50|unique:promo_codes,code,' . $promoCodeId,
            'applies_to' => 'required|in:all,package,vehicle_type',
            'package_id' => 'nullable|required_if:applies_to,package|exists:packages,id',
            'vehicle_type_id' => 'nullable|required_if:applies_to,vehicle_type|exists:vehicle_types,id',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'min_spend' => 'nullable|numeric|min:0',
            'usage_limit_total' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'required|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'status' => 'required|in:active,inactive,expired',
            'remarks' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Promo code is required.',
            'code.unique' => 'This promo code already exists.',
            'applies_to.required' => 'Please select what this promo applies to.',
            'package_id.required_if' => 'Please select a package.',
            'vehicle_type_id.required_if' => 'Please select a vehicle type.',
            'discount_type.required' => 'Please select discount type.',
            'discount_value.required' => 'Discount value is required.',
            'discount_value.min' => 'Discount value must be at least 0.',
            'usage_limit_per_user.required' => 'Usage limit per user is required.',
            'ends_at.after_or_equal' => 'End date must be after or equal to start date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Convert empty strings to null for nullable fields
        $this->merge([
            'package_id' => $this->package_id ?: null,
            'vehicle_type_id' => $this->vehicle_type_id ?: null,
            'max_discount' => $this->max_discount ?: null,
            'min_spend' => $this->min_spend ?: null,
            'usage_limit_total' => $this->usage_limit_total ?: null,
            'starts_at' => $this->starts_at ?: null,
            'ends_at' => $this->ends_at ?: null,
            'remarks' => $this->remarks ?: null,
        ]);
    }
}
