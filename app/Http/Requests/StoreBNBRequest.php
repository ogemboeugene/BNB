<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class StoreBNBRequest
 * 
 * Form request for validating BNB creation data.
 * This class provides comprehensive validation rules and custom
 * error messages for creating new BNB resources.
 * 
 * @package App\Http\Requests
 */
class StoreBNBRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * For BNB creation, we require the user to be authenticated.
     * Additional role-based authorization can be added here.
     */
    public function authorize(): bool
    {
        // User must be authenticated to create a BNB
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * These rules follow the BNB business requirements:
     * - name: required, string, max 100 characters
     * - location: required, string
     * - price_per_night: required, numeric, minimum 0
     * - availability: optional, boolean (defaults to true)
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'min:2',
                'regex:/^[a-zA-Z0-9\s\-_.,&()]+$/', // Allow alphanumeric, spaces, and common punctuation
            ],
            'location' => [
                'required',
                'string',
                'max:255',
                'min:3',
            ],
            'price_per_night' => [
                'required',
                'numeric',
                'min:0',
                'max:99999.99',
                'decimal:0,2', // Allow up to 2 decimal places
            ],
            'availability' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     * 
     * Provides user-friendly error messages for each validation rule.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The BNB name is required.',
            'name.string' => 'The BNB name must be a valid string.',
            'name.max' => 'The BNB name cannot exceed 100 characters.',
            'name.min' => 'The BNB name must be at least 2 characters long.',
            'name.regex' => 'The BNB name contains invalid characters. Only letters, numbers, spaces, and common punctuation are allowed.',
            
            'location.required' => 'The location is required.',
            'location.string' => 'The location must be a valid string.',
            'location.max' => 'The location cannot exceed 255 characters.',
            'location.min' => 'The location must be at least 3 characters long.',
            
            'price_per_night.required' => 'The price per night is required.',
            'price_per_night.numeric' => 'The price per night must be a valid number.',
            'price_per_night.min' => 'The price per night cannot be negative.',
            'price_per_night.max' => 'The price per night cannot exceed $99,999.99.',
            'price_per_night.decimal' => 'The price per night can have at most 2 decimal places.',
            
            'availability.boolean' => 'The availability must be true or false.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     * 
     * Provides human-readable attribute names for error messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'BNB name',
            'location' => 'location',
            'price_per_night' => 'price per night',
            'availability' => 'availability status',
        ];
    }

    /**
     * Prepare the data for validation.
     * 
     * This method allows us to modify the input data before validation.
     * We can sanitize and normalize the data here.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Trim whitespace from string fields
            'name' => trim($this->input('name', '')),
            'location' => trim($this->input('location', '')),
            
            // Ensure availability is properly cast to boolean
            'availability' => $this->boolean('availability', true),
            
            // Normalize price to ensure it's a proper decimal
            'price_per_night' => $this->input('price_per_night') !== null 
                ? (float) $this->input('price_per_night') 
                : null,
        ]);
    }

    /**
     * Handle a failed validation attempt.
     * 
     * This method is called when validation fails and allows us to
     * customize the response format for API endpoints.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            ], Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }

    /**
     * Handle a failed authorization attempt.
     * 
     * This method is called when authorization fails.
     *
     * @throws HttpResponseException
     */
    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'You are not authorized to create a BNB.',
                'status_code' => Response::HTTP_UNAUTHORIZED,
            ], Response::HTTP_UNAUTHORIZED)
        );
    }

    /**
     * Get validated data with additional processing.
     * 
     * This method returns the validated data and can be used to
     * apply additional business logic after validation.
     *
     * @return array
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();
        
        // Ensure availability defaults to true if not provided
        if (!isset($validated['availability'])) {
            $validated['availability'] = true;
        }
        
        // Round price to 2 decimal places
        if (isset($validated['price_per_night'])) {
            $validated['price_per_night'] = round($validated['price_per_night'], 2);
        }
        
        return $validated;
    }
}
