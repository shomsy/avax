<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Issuer;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record\IssuedToken;
use DateTimeImmutable;

/**
 * Contract for issuing identity tokens.
 */
interface TokenIssuerInterface
{
    /**
     * @param User                   $user
     * @param DateTimeImmutable|null $mfaVerifiedAt
     * @param bool                   $phishingResistant
     * @param string|null            $clientId
     * @param list<string>           $scopes
     * @param string|null            $refreshTokenFamilyId
     *
     * @return IssuedToken
     */
    public function issue(
        User                   $user,
        DateTimeImmutable|null $mfaVerifiedAt = null,
        bool                   $phishingResistant = false,
        string                 $clientId = null,
        array                  $scopes = [],
        string                 $refreshTokenFamilyId = null,
    ) : IssuedToken;
}
