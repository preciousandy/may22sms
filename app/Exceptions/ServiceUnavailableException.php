<?php

namespace App\Exceptions;

use RuntimeException;

class ServiceUnavailableException extends RuntimeException
{
    public string $serviceKey;

    public function __construct(string $serviceKey, string $message = 'This service is temporarily unavailable. Please try again later.', int $code = 403, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->serviceKey = $serviceKey;
    }
}
