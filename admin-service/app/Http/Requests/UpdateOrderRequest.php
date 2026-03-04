<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
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
            'status_id' => ['required', 'exists:order_statuses,id'],
            'payment_status_id' => ['nullable', 'exists:payment_statuses,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'shipping_address' => ['nullable', 'string'],
            'billing_address' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status_id.required' => 'Order status is required.',
            'status_id.exists' => 'The selected status is invalid.',
            'payment_status_id.exists' => 'The selected payment status is invalid.',
        ];
    }
}
