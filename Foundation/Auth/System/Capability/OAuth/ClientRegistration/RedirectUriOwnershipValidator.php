<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth\ClientRegistration;

use Avax\Auth\System\Capability\OAuth\ClientRegistration\ValidationResult;

/**
 * Validates redirect URI ownership for OAuth/OIDC client registration.
 */
final readonly class RedirectUriOwnershipValidator
{
    /**
     * Validates redirect URI ownership.
     *
     * @param array $clientData Client registration data
     * @return ValidationResult Validation result
     */
    public function validate(array $clientData) : ValidationResult
    {
        $redirectUris = $clientData['redirect_uris'] ?? [];
        $errors = [];

        foreach ($redirectUris as $uri) {
            // In a real implementation, this would verify ownership of the URI
            // For now, we'll do basic validation
            if (!is_string($uri) || $uri === '') {
                $errors[] = "Redirect URI must be a non-empty string: {$uri}";
                continue;
            }

            // Basic URI format validation
            if (!filter_var($uri, FILTER_VALIDATE_URL)) {
                $errors[] = "Invalid redirect URI format: {$uri}";
                continue;
            }

            // Additional ownership checks would go here
            // For example, verifying domain ownership, etc.
        }

        return new ValidationResult(
            isValid: empty($errors),
            errors: $errors
        );
    }
}