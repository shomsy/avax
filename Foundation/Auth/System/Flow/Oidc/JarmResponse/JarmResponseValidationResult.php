<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\JarmResponse;

/**
 * Result of JARM response validation.
 */
final readonly class JarmResponseValidationResult
{
    private function __construct(
        public bool $valid,
        public bool $isError,
        public bool $isSuccess,
        public string|null $error,
        public string|null $errorDescription,
        public string|null $state,
        public string|null $redirectUri,
        public array|null $tokens
    ) {}

    public static function success(array $tokens) : self
    {
        return new self(
            valid: true,
            isError: false,
            isSuccess: true,
            error: null,
            errorDescription: null,
            state: null,
            redirectUri: null,
            tokens: $tokens
        );
    }

    public static function error(
        string $error,
        string|null $errorDescription,
        string|null $state,
        string|null $redirectUri
    ) : self {
        return new self(
            valid: true,
            isError: true,
            isSuccess: false,
            error: $error,
            errorDescription: $errorDescription,
            state: $state,
            redirectUri: $redirectUri,
            tokens: null
        );
    }

    public static function invalid(string $reason) : self
    {
        return new self(
            valid: false,
            isError: false,
            isSuccess: false,
            error: 'invalid_response',
            errorDescription: $reason,
            state: null,
            redirectUri: null,
            tokens: null
        );
    }
}