<?php

declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\Foundation\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Thrown when input data fails validation rules.
 * Carries structured metadata so callers can build user-facing error responses.
 */
final class ValidationException extends RuntimeException
{
    public function __construct(
        string    $message,
        int       $code = 422,
        Throwable $throwable = null,
        private readonly array $metadata = [],
    )
    {
        parent::__construct(message: $message, code: $code, previous: $throwable);
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
