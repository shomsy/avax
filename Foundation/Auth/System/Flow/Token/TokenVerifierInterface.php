<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Token;

/**
 * Resolves a bearer token into authenticated user state.
 */
interface TokenVerifierInterface
{
    public function resolve(string $token) : ResolvedToken|null;
}
