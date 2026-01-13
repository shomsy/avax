<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing\Exceptions;

/**
 * Base exception class for all router-related exceptions.
 *
 * Provides common functionality and ensures consistent behavior across
 * all router exceptions. Implements RouterExceptionInterface for
 * standardized error handling.
 */
abstract class RouterException extends \RuntimeException implements RouterExceptionInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $context;

    public function __construct(
        string $message,
        int $httpStatusCode,
        array $context = [],
        bool $isRetryable = false,
        int $code = 0,
        \Throwable|null $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->context = array_merge($context, [
            'http_status_code' => $httpStatusCode,
            'is_retryable' => $isRetryable,
            'exception_class' => static::class,
            'timestamp' => microtime(true),
        ]);
    }

    /**
     * Get the HTTP status code associated with this exception.
     */
    abstract public function getHttpStatusCode() : int;

    /**
     * Get additional context information for debugging.
     *
     * @return array<string, mixed>
     */
    public function getContext() : array
    {
        return $this->context;
    }

    /**
     * Check if this exception is retryable.
     */
    public function isRetryable() : bool
    {
        return $this->context['is_retryable'] ?? false;
    }

    /**
     * Add context information to the exception.
     *
     * @param array<string, mixed> $additionalContext
     */
    public function withContext(array $additionalContext) : self
    {
        $this->context = array_merge($this->context, $additionalContext);
        return $this;
    }

    /**
     * Create a new instance with additional context.
     *
     * @param array<string, mixed> $additionalContext
     */
    public function withAdditionalContext(array $additionalContext) : self
    {
        $clone = clone $this;
        return $clone->withContext($additionalContext);
    }
}