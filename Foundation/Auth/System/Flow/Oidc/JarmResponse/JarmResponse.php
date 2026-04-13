<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\JarmResponse;

use Avax\Auth\System\Foundation\Clock;

/**
 * Handles JWT Secured Authorization Response Mode (JARM).
 *
 * JARM allows authorization server to respond with authorization responses
 * (including tokens and errors) as signed JWTs instead of URL-encoded parameters.
 */
final readonly class JarmResponse
{
    public function __construct(
        private Clock $clock
    ) {}

    /**
     * Creates a successful authorization response JWT payload.
     *
     * @param array<string, mixed> $tokens The authorization tokens
     * @return array<string, mixed>
     */
    public function createSuccessResponse(
        string $issuer,
        string $clientId,
        array $tokens,
        int $expiresIn = 3600
    ) : array {
        $now = $this->clock->now()->getTimestamp();

        return [
            'iss'   => $issuer,
            'aud'   => $clientId,
            'iat'   => $now,
            'exp'   => $now + $expiresIn,
            'response' => $tokens,
        ];
    }

    /**
     * Creates an error authorization response JWT payload.
     *
     * @return array<string, mixed>
     */
    public function createErrorResponse(
        string $issuer,
        string $clientId,
        string $error,
        string $errorDescription = null,
        string $state = null,
        string $redirectUri = null
    ) : array {
        $now = $this->clock->now()->getTimestamp();

        $response = [
            'error' => $error,
        ];

        if ($errorDescription !== null) {
            $response['error_description'] = $errorDescription;
        }

        if ($state !== null) {
            $response['state'] = $state;
        }

        if ($redirectUri !== null) {
            $response['redirect_uri'] = $redirectUri;
        }

        return [
            'iss'   => $issuer,
            'aud'   => $clientId,
            'iat'   => $now,
            'exp'   => $now + 60,
            'response' => $response,
        ];
    }

    /**
     * Validates an authorization response JWT.
     */
    public function validate(array $payload, int $maxAge = 600) : JarmResponseValidationResult
    {
        $now = $this->clock->now()->getTimestamp();

        if (! isset($payload['iss'], $payload['aud'], $payload['exp'], $payload['response'])) {
            return JarmResponseValidationResult::invalid('Missing required claims');
        }

        if ($payload['exp'] < $now) {
            return JarmResponseValidationResult::invalid('Response expired');
        }

        if ($now - $payload['iat'] > $maxAge) {
            return JarmResponseValidationResult::invalid('Response too old');
        }

        $response = $payload['response'];

        if (isset($response['error'])) {
            return JarmResponseValidationResult::error(
                $response['error'],
                $response['error_description'] ?? null,
                $response['state'] ?? null,
                $response['redirect_uri'] ?? null
            );
        }

        return JarmResponseValidationResult::success($response);
    }
}