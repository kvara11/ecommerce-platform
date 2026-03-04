<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCategoryRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'description' => 'nullable|string|max:1000',
            'image_url' => 'nullable|string|max:255|url',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required',
            'name.max' => 'Category name must not exceed 255 characters',
            'slug.required' => 'Category slug is required',
            'slug.unique' => 'This slug is already in use',
            'slug.regex' => 'Slug must contain only lowercase letters, numbers, and hyphens',
            'parent_id.exists' => 'Selected parent category does not exist',
            'description.max' => 'Description must not exceed 1000 characters',
            'image_url.url' => 'Please provide a valid image URL',
            'sort_order.min' => 'Sort order must be a positive number',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name' => 'category name',
            'slug' => 'slug',
            'parent_id' => 'parent category',
            'description' => 'description',
            'image_url' => 'image URL',
            'sort_order' => 'sort order',
            'is_active' => 'active status',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}