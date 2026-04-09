<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Token;

use Avax\Auth\System\Capability\User\User;

/**
 * Issues signed access tokens.
 */
interface TokenIssuerInterface
{
    public function issue(User $user) : IssuedToken;
}
