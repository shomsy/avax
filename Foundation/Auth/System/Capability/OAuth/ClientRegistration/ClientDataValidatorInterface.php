<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth\ClientRegistration;

/**
 * Interface for validating OAuth/OIDC client registration data.
 */
interface ClientDataValidatorInterface
{
    /**
     * Validates client registration data.
     *
     * @param array $clientData Client registration data to validate
     * @return ValidationResult Validation result
     */
    public function validate(array $clientData) : ValidationResult;
}

/**
 * Data transfer object for validation results.
 */
final readonly class ValidationResult
{
    public function __construct(
        private bool $isValid,
        /** @var array<string> */ private array $errors = []
    ) {}

    public function isValid() : bool
    {
        return $this->isValid;
    }

    /**
     * @return array<string>
     */
    public function getErrors() : array
    {
        return $this->errors;
    }
}