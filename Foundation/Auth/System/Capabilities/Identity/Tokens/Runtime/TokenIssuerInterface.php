<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Token;

use Avax\Auth\System\Capabilities\OAuth\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capabilities\User\User;
use DateTimeImmutable;

/**
 * Issues signed access tokens.
 */
interface TokenIssuerInterface
{
    /**
     * @param list<string> $scopes
     */
    public function issue(
        User                       $user,
        DateTimeImmutable|null     $mfaVerifiedAt = null,
        bool                       $phishingResistant = false,
        string|null                $clientId = null,
        array                      $scopes = [],
        OAuthSenderConstraint|null $senderConstraint = null
    ) : IssuedToken;
}
