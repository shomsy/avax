<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth\ClientRegistration;

use Avax\Auth\System\Capability\OAuth\ClientRegistration\ValidationResult;

/**
 * Validates PKCE requirements for OAuth/OIDC client registration.
 */
final readonly class PkceRequirementsValidator
{
    /**
     * Validates PKCE requirements.
     *
     * @param array $clientData Client registration data
     * @return ValidationResult Validation result
     */
    public function validate(array $clientData) : ValidationResult
    {
        $errors = [];
        $requirePkce = $clientData['require_pkce'] ?? false;
        $grantTypes = $clientData['grant_types'] ?? [];

        // Validate require_pkce is boolean
        if (!is_bool($requirePkce)) {
            $errors[] = "require_pkce must be a boolean";
        }

        // If require_pkce is true, ensure authorization_code grant type is present
        if ($requirePkce && !in_array('authorization_code', $grantTypes, true)) {
            $errors[] = "PKCE can only be required for authorization_code grant type";
        }

        return new ValidationResult(
            isValid: empty($errors),
            errors: $errors
        );
    }
}