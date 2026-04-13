<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth\ClientRegistration;

use Avax\Auth\System\Capability\OAuth\ClientRegistration\ValidationResult;

/**
 * Validates grant type compatibility for OAuth/OIDC client registration.
 */
final readonly class GrantTypeCompatibilityValidator
{
    /**
     * Validates grant type compatibility.
     *
     * @param array $clientData Client registration data
     * @return ValidationResult Validation result
     */
    public function validate(array $clientData) : ValidationResult
    {
        $grantTypes = $clientData['grant_types'] ?? [];
        $errors = [];

        if (!is_array($grantTypes)) {
            $errors[] = "Grant types must be an array";
            return new ValidationResult(isValid: false, errors: $errors);
        }

        // Valid OAuth 2.0 grant types
        $validGrantTypes = [
            'authorization_code',
            'client_credentials',
            'password',
            'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'refresh_token'
        ];

        foreach ($grantTypes as $grantType) {
            if (!is_string($grantType) || $grantType === '') {
                $errors[] = "Grant type must be a non-empty string";
                continue;
            }

            if (!in_array($grantType, $validGrantTypes, true)) {
                $errors[] = "Unsupported grant type: {$grantType}";
            }
        }

        // Check for incompatible combinations
        if (in_array('authorization_code', $grantTypes, true) && 
            in_array('client_credentials', $grantTypes, true)) {
            // This is actually valid, but we could add specific validation if needed
        }

        return new ValidationResult(
            isValid: empty($errors),
            errors: $errors
        );
    }
}