<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth\ClientRegistration;

use Avax\Auth\System\Capability\OAuth\ClientRegistration\ValidationResult;

/**
 * Validates client posture (confidential vs public) for OAuth/OIDC client registration.
 */
final readonly class ClientPostureValidator
{
    /**
     * Validates client posture.
     *
     * @param array $clientData Client registration data
     * @return ValidationResult Validation result
     */
    public function validate(array $clientData) : ValidationResult
    {
        $errors = [];
        $tokenEndpointAuthMethod = $clientData['token_endpoint_auth_method'] ?? 'none';

        // Validate token_endpoint_auth_method
        $validAuthMethods = [
            'none',
            'client_secret_basic',
            'client_secret_post',
            'client_secret_jwt',
            'private_key_jwt',
            'tls_client_auth',
            'self_signed_tls_client_auth'
        ];

        if (!is_string($tokenEndpointAuthMethod) || $tokenEndpointAuthMethod === '') {
            $errors[] = "token_endpoint_auth_method must be a non-empty string";
        } elseif (!in_array($tokenEndpointAuthMethod, $validAuthMethods, true)) {
            $errors[] = "Unsupported token endpoint authentication method: {$tokenEndpointAuthMethod}";
        }

        return new ValidationResult(
            isValid: empty($errors),
            errors: $errors
        );
    }
}