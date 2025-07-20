<?php

declare(strict_types=1);

namespace Gemini\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Custom exception for validation errors.
 */
class ValidationException extends RuntimeException
{
    /**
     * Constructor for the ValidationException.
     */
    public function __construct(
        string                 $message,
        int                    $code = 422,
        Throwable|null         $previous = null,
        private readonly array $metadata = []
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Retrieves metadata related to the validation error.
     */
    public function getMetadata() : array
    {
        return $this->metadata;
    }

    /**
     * Converts the exception into a detailed array representation.
     */
    public function toArray() : array
    {
        return [
            'message' => $this->getMessage(),
            'code'    => $this->getCode(),
            'errors'  => $this->getErrors(),
        ];
    }

    /**
     * Retrieves the validation errors from metadata.
     */
    public function getErrors() : array
    {
        return $this->metadata['errors'] ?? $this->metadata;
    }
}
