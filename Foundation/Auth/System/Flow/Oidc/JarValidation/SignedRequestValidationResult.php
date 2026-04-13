<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\JarValidation;

/**
 * Result of signed authorization request validation.
 */
final readonly class SignedRequestValidationResult
{
    private function __construct(
        public bool $valid,
        public string|null $error,
        public SignedRequest|null $request
    ) {}

    public static function valid(SignedRequest $request) : self
    {
        return new self(valid: true, error: null, request: $request);
    }

    public static function invalid(string $error) : self
    {
        return new self(valid: false, error: $error, request: null);
    }

    public function isValid() : bool
    {
        return $this->valid;
    }
}