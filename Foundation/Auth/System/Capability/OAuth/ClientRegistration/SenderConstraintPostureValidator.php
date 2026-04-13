<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth\ClientRegistration;

use Avax\Auth\System\Capability\OAuth\ClientRegistration\ValidationResult;

/**
 * Validates sender-constraint posture for OAuth/OIDC client registration.
 */
final readonly class SenderConstraintPostureValidator
{
    /**
     * Validates sender-constraint posture.
     *
     * @param array $clientData Client registration data
     * @return ValidationResult Validation result
     */
    public function validate(array $clientData) : ValidationResult
    {
        $errors = [];
        $requireDpop = $clientData['require_dpop'] ?? false;
        $requireMtls = $clientData['require_mtls'] ?? false;

        // Validate require_dpop is boolean
        if (!is_bool($requireDpop)) {
            $errors[] = "require_dpop must be a boolean";
        }

        // Validate require_mtls is boolean
        if (!is_bool($requireMtls)) {
            $errors[] = "require_mtls must be a boolean";
        }

        // Additional validation: if either is true, ensure appropriate token endpoint auth method
        if ($requireDpop || $requireMtls) {
            $tokenEndpointAuthMethod = $clientData['token_endpoint_auth_method'] ?? 'none';
            
            // For sender-constrained tokens, we typically need specific auth methods
            // This is a simplified validation - in reality this would be more complex
            if ($requireDpop && $tokenEndpointAuthMethod === 'none') {
                $errors[] = "DPoP requirement typically requires a specific token endpoint auth method";
            }
            
            if ($requireMtls && $tokenEndpointAuthMethod === 'none') {
                $errors[] = "mTLS requirement typically requires a specific token endpoint auth method";
            }
        }

        return new ValidationResult(
            isValid: empty($errors),
            errors: $errors
        );
    }
}