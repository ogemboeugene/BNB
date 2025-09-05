<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class InvalidBNBDataException
 * 
 * Exception thrown when BNB data validation fails.
 * 
 * @package App\Exceptions
 */
class InvalidBNBDataException extends ApiException
{
    /**
     * InvalidBNBDataException constructor.
     * 
     * @param array $errors The validation errors
     * @param string|null $message Custom error message
     */
    public function __construct(array $errors = [], ?string $message = null)
    {
        $message = $message ?? 'Invalid BNB data provided';
        
        parent::__construct(
            $message,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'INVALID_BNB_DATA',
            ['validation_errors' => $errors]
        );
    }
}