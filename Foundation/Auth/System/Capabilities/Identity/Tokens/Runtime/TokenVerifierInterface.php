<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Tokens\Runtime;

/**
 * Resolves a bearer token into authenticated user state.
 */
interface TokenVerifierInterface
{
    public function resolve(string $token) : ResolvedToken|null;
}
