<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\System\Capabilities\Tokens\Runtime\Codec;

use SensitiveParameter;

/**
 * Contract for encoding and decoding tokens.
 */
interface TokenCodecInterface
{
    /**
     * @param array<string, mixed> $claims
     */
    public function encode(array $claims) : string;

    /**
     * @return array<string, mixed>|null
     */
    public function decode(#[SensitiveParameter] string $token) : ?array;
}
