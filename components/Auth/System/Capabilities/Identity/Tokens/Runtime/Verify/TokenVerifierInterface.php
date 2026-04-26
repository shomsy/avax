<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\Identity\Tokens\Runtime\Verify;

use SensitiveParameter;

/**
 * Contract for verifying tokens.
 */
interface TokenVerifierInterface
{
    public function verify(#[SensitiveParameter] string $token) : bool;
}
