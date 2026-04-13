<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\JarValidation;

use Avax\Auth\System\Foundation\Clock;

/**
 * Validates JWT-secured authorization requests (JAR).
 *
 * JAR provides request integrity and authentication by requiring
 * the authorization request parameters to be signed in a JWT.
 */
final readonly class ValidateSignedRequest
{
    public function __construct(
        private Clock $clock
    ) {}

    /**
     * Validates a signed authorization request JWT.
     *
     * @param array<string, mixed> $payload The decoded JWT payload
     */
    public function validate(
        array $payload,
        string $expectedIssuer,
        string $expectedAudience,
        int $maxAge = 600
    ) : SignedRequestValidationResult {
        $now = $this->clock->now()->getTimestamp();

        if (! isset($payload['iss'], $payload['aud'], $payload['exp'], $payload['iat'])) {
            return SignedRequestValidationResult::invalid('Missing required claims');
        }

        if ($payload['iss'] !== $expectedIssuer) {
            return SignedRequestValidationResult::invalid('Invalid issuer');
        }

        if ($payload['aud'] !== $expectedAudience) {
            return SignedRequestValidationResult::invalid('Invalid audience');
        }

        if ($payload['exp'] < $now) {
            return SignedRequestValidationResult::invalid('Request expired');
        }

        if ($payload['iat'] > $now + 60) {
            return SignedRequestValidationResult::invalid('Request issued too far in future');
        }

        if ($now - $payload['iat'] > $maxAge) {
            return SignedRequestValidationResult::invalid('Request too old');
        }

        if (! isset($payload['response_type'], $payload['client_id'])) {
            return SignedRequestValidationResult::invalid('Missing essential parameters');
        }

        return SignedRequestValidationResult::valid(
            new SignedRequest(
                responseType : $payload['response_type'],
                clientId     : $payload['client_id'],
                redirectUri  : $payload['redirect_uri'] ?? null,
                scope        : $payload['scope'] ?? '',
                state        : $payload['state'] ?? null,
                nonce        : $payload['nonce'] ?? null,
                prompt       : $payload['prompt'] ?? null,
                maxAge       : $payload['max_age'] ?? null,
                claims       : $payload['claims'] ?? null
            )
        );
    }
}