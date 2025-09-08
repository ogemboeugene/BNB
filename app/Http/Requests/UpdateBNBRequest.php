<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UpdateBNBRequest
 * 
 * Form request for validating BNB update data.
 * This class provides comprehensive validation rules for updating
 * existing BNB resources with partial update support.
 * 
 * @package App\Http\Requests
 */
class UpdateBNBRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * For BNB updates, we require the user to be authenticated.
     * Additional role-based authorization can be added here.
     */
    public function authorize(): bool
    {
        // User must be authenticated to update a BNB
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * These rules are similar to creation but with 'sometimes' prefix
     * to allow partial updates (PATCH behavior).
     * 
     * Rules:
     * - name: optional when updating, string, max 100 characters
     * - location: optional when updating, string
     * - price_per_night: optional when updating, numeric, minimum 0
     * - availability: optional, boolean
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                'min:2',
                'regex:/^[a-zA-Z0-9\s\-_.,&()]+$/', // Allow alphanumeric, spaces, and common punctuation
            ],
            'location' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'min:3',
            ],
            'price_per_night' => [
                'sometimes',
                'required',
                'numeric',
                'min:0',
                'max:99999.99',
                'decimal:0,2', // Allow up to 2 decimal places
            ],
            'availability' => [
                'sometimes',
                'boolean',
            ],
            'image' => [
                'sometimes',
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp,gif',
                'max:10240', // 10MB in kilobytes
                'dimensions:min_width=200,min_height=200,max_width=4000,max_height=4000',
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
            'name.required' => 'The BNB name cannot be empty when provided.',
            'name.string' => 'The BNB name must be a valid string.',
            'name.max' => 'The BNB name cannot exceed 100 characters.',
            'name.min' => 'The BNB name must be at least 2 characters long.',
            'name.regex' => 'The BNB name contains invalid characters. Only letters, numbers, spaces, and common punctuation are allowed.',
            
            'location.required' => 'The location cannot be empty when provided.',
            'location.string' => 'The location must be a valid string.',
            'location.max' => 'The location cannot exceed 255 characters.',
            'location.min' => 'The location must be at least 3 characters long.',
            
            'price_per_night.required' => 'The price per night cannot be empty when provided.',
            'price_per_night.numeric' => 'The price per night must be a valid number.',
            'price_per_night.min' => 'The price per night cannot be negative.',
            'price_per_night.max' => 'The price per night cannot exceed $99,999.99.',
            'price_per_night.decimal' => 'The price per night can have at most 2 decimal places.',
            
            'availability.boolean' => 'The availability must be true or false.',
            
            'image.file' => 'The image must be a valid file.',
            'image.image' => 'The image must be a valid image file.',
            'image.mimes' => 'The image must be a file of type: jpeg, jpg, png, webp, gif.',
            'image.max' => 'The image size cannot exceed 10MB.',
            'image.dimensions' => 'The image dimensions must be between 200x200 and 4000x4000 pixels.',
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
            'image' => 'image',
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
        // Only modify fields that are present in the request
        $modifications = [];
        
        if ($this->has('name')) {
            $modifications['name'] = trim($this->input('name'));
        }
        
        if ($this->has('location')) {
            $modifications['location'] = trim($this->input('location'));
        }
        
        if ($this->has('availability')) {
            $modifications['availability'] = $this->boolean('availability');
        }
        
        if ($this->has('price_per_night')) {
            $modifications['price_per_night'] = $this->input('price_per_night') !== null 
                ? (float) $this->input('price_per_night') 
                : null;
        }
        
        if (!empty($modifications)) {
            $this->merge($modifications);
        }
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
                'message' => 'You are not authorized to update this BNB.',
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
        
        // Round price to 2 decimal places if provided
        if (isset($validated['price_per_night'])) {
            $validated['price_per_night'] = round($validated['price_per_night'], 2);
        }
        
        return $validated;
    }

    /**
     * Check if the request contains any updateable fields.
     * 
     * @return bool
     */
    public function hasUpdatableFields(): bool
    {
        return $this->hasAny(['name', 'location', 'price_per_night', 'availability']);
    }
}
