<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class BNBAccessDeniedException
 * 
 * Exception thrown when user doesn't have access to a BNB resource.
 * 
 * @package App\Exceptions
 */
class BNBAccessDeniedException extends ApiException
{
    /**
     * BNBAccessDeniedException constructor.
     * 
     * @param string $action The action that was denied
     * @param int|string|null $bnbId The BNB identifier
     * @param string|null $message Custom error message
     */
    public function __construct(string $action = 'access', $bnbId = null, ?string $message = null)
    {
        $message = $message ?? "Access denied to {$action} this BNB resource";
        
        parent::__construct(
            $message,
            Response::HTTP_FORBIDDEN,
            'BNB_ACCESS_DENIED',
            [
                'action' => $action,
                'bnb_id' => $bnbId
            ]
        );
    }
}