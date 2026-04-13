<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\PushAuthorizationRequest;

use Avax\Auth\System\Foundation\Clock;

/**
 * Handles Pushed Authorization Requests (PAR) per OAuth 2.0 Pushed Authorization Requests.
 *
 * PAR allows clients to push authorization request parameters to the authorization server
 * before initiating the authorization flow, providing better security and larger request sizes.
 */
final readonly class PushAuthorizationRequest
{
    public function __construct(
        private Clock $clock
    ) {}

    /**
     * Creates a pushed authorization request.
     */
    public function create(
        string $clientId,
        array $requestParams,
        int $expiresIn = 600
    ) : PushedAuthRequest {
        $now = $this->clock->now()->getTimestamp();

        return new PushedAuthRequest(
            requestUri     : 'urn:ietf:params:oauth:request_uri:' . bin2hex(random_bytes(16)),
            expiresAt     : $now + $expiresIn,
            clientId      : $clientId,
            requestParams : $requestParams,
            createdAt     : $now
        );
    }

    /**
     * Validates a pushed authorization request.
     */
    public function validate(PushedAuthRequest $request, int $currentTime) : bool
    {
        if ($request->expiresAt < $currentTime) {
            return false;
        }

        if ($request->createdAt > $currentTime) {
            return false;
        }

        return true;
    }

    /**
     * Extracts essential authorization parameters from request.
     */
    public function essentialParams(PushedAuthRequest $request) : array
    {
        return [
            'response_type' => $request->requestParams['response_type'] ?? null,
            'client_id'   => $request->clientId,
            'redirect_uri' => $request->requestParams['redirect_uri'] ?? null,
            'scope'       => $request->requestParams['scope'] ?? null,
            'state'       => $request->requestParams['state'] ?? null,
        ];
    }
}