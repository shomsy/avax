<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\BackChannelLogout;

use Avax\Auth\System\Foundation\Clock;

/**
 * Handles back-channel logout notifications to relying parties.
 *
 * Sends logout tokens to RPs via back-channel HTTP POST for stronger security
 * compared to front-channel redirects.
 */
final readonly class BackChannelLogout
{
    public function __construct(
        private Clock $clock
    ) {}

    /**
     * Creates a logout token for sending to a relying party.
     */
    public function createLogoutToken(
        string $issuer,
        string $clientId,
        string $sessionId,
        string $subject,
        int $issuedAt,
        int $expiresAt
    ) : LogoutToken {
        return new LogoutToken(
            jwtId     : bin2hex(random_bytes(16)),
            issuer    : $issuer,
            audience  : $clientId,
            subject   : $subject,
            eventId   : bin2hex(random_bytes(16)),
            issuedAt  : $issuedAt,
            expiresAt : $expiresAt,
            sessionId : $sessionId
        );
    }

    /**
     * Validates a logout token.
     */
    public function validateLogoutToken(
        LogoutToken $token,
        string $expectedIssuer,
        string $expectedAudience,
        int $currentTime
    ) : bool {
        if ($token->issuer !== $expectedIssuer) {
            return false;
        }

        if ($token->audience !== $expectedAudience) {
            return false;
        }

        if ($token->expiresAt < $currentTime) {
            return false;
        }

        if ($token->issuedAt > $currentTime) {
            return false;
        }

        return true;
    }

    /**
     * Builds the logout token payload for JWT encoding.
     *
     * @return array<string, mixed>
     */
    public function buildTokenPayload(LogoutToken $token) : array
    {
        return [
            'jti'   => $token->jwtId,
            'iss'   => $token->issuer,
            'aud'   => $token->audience,
            'sub'   => $token->subject,
            'iat'   => $token->issuedAt,
            'exp'   => $token->expiresAt,
            'events' => [
                'http://schemas.openid.net/event/backchannel-logout' => [
                    'sid' => $token->sessionId,
                ],
            ],
        ];
    }
}