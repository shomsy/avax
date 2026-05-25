<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec;

use Avax\Components\Identity\Auth\System\Foundation\Values\SignedToken;

/**
 * SignToken — contract for issuing signed tokens.
 *
 * Adapted from the enterprise reference package.
 * Implemented by HmacTokenCodec and MultiKeyHmacTokenCodec.
 */
interface SignToken
{
    /**
     * @param array<string, mixed> $claims
     */
    public function sign(array $claims): SignedToken;
}
