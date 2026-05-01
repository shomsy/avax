<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Verify;

use SensitiveParameter;

/**
 * Contract for verifying tokens.
 */
interface TokenVerifierInterface
{
    public function verify(#[SensitiveParameter] string $token): bool;
}
