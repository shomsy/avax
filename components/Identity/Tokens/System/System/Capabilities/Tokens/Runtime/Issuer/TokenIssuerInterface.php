<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\System\Capabilities\Tokens\Runtime\Issuer;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Tokens\System\System\Capabilities\Tokens\Runtime\Record\IssuedToken;
use DateTimeImmutable;

/**
 * Contract for issuing identity tokens.
 */
interface TokenIssuerInterface
{
    /**
     * @param list<string> $scopes
     */
    public function issue(
        User               $user,
        ?DateTimeImmutable $mfaVerifiedAt = null,
        bool               $phishingResistant = false,
        ?string            $clientId = null,
        array              $scopes = [],
        ?string            $refreshTokenFamilyId = null,
    ) : IssuedToken;
}
