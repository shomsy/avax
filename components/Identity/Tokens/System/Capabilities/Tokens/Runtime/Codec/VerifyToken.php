<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec;

use Avax\Components\Identity\Auth\System\Foundation\Values\SignedToken;

/**
 * VerifyToken — contract for verifying signed tokens.
 *
 * Adapted from the enterprise reference package.
 * Implemented by HmacTokenCodec and MultiKeyHmacTokenCodec.
 */
interface VerifyToken
{
    /**
     * @return array<string, mixed>|null
     */
    public function verify(SignedToken $token): array|null;
}
