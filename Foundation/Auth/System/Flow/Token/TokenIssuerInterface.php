<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Token;

use Avax\Auth\System\Capability\User\User;

/**
 * Issues signed access tokens.
 */
interface TokenIssuerInterface
{
    /**
     * @param list<string> $scopes
     */
    public function issue(
        User $user,
        \DateTimeImmutable|null $mfaVerifiedAt = null,
        string|null $clientId = null,
        array $scopes = []
    ) : IssuedToken;
}
