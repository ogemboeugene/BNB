<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class BNBNotFoundException
 * 
 * Exception thrown when a BNB resource is not found.
 * 
 * @package App\Exceptions
 */
class BNBNotFoundException extends ApiException
{
    /**
     * BNBNotFoundException constructor.
     * 
     * @param int|string|null $identifier The BNB identifier that was not found
     * @param string|null $message Custom error message
     */
    public function __construct($identifier = null, ?string $message = null)
    {
        $message = $message ?? ($identifier ? "BNB with ID '{$identifier}' not found" : 'BNB not found');
        
        parent::__construct(
            $message,
            Response::HTTP_NOT_FOUND,
            'BNB_NOT_FOUND',
            $identifier ? ['bnb_id' => $identifier] : []
        );
    }
}