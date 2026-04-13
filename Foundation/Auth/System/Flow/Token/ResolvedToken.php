<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Token;

use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capability\User\User;
use DateTimeImmutable;

/**
 * Successfully verified access token state.
 */
final readonly class ResolvedToken
{
    /**
     * @param list<string> $scopes
     */
    public function __construct(
        public User                          $user,
        #[\SensitiveParameter] public string $tokenId,
        public DateTimeImmutable             $expiresAt,
        public DateTimeImmutable|null        $mfaVerifiedAt = null,
        public bool                          $phishingResistant = false,
        public string|null                   $clientId = null,
        public array                         $scopes = [],
        public OAuthSenderConstraint|null    $senderConstraint = null
    ) {}
}
