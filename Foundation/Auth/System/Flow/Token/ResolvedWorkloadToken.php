<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Token;

use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraint;
use DateTimeImmutable;

/**
 * Successfully verified workload access token state.
 */
final readonly class ResolvedWorkloadToken
{
    /**
     * @param list<string> $scopes
     */
    public function __construct(
        public string                     $subject,
        public string                     $clientId,
        public string                     $tokenId,
        public DateTimeImmutable          $expiresAt,
        public array                      $scopes = [],
        public string|null                $audience = null,
        public string|null                $issuer = null,
        public OAuthSenderConstraint|null $senderConstraint = null
    ) {}
}
