<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\Token;

use Avax\Components\Identity\Tokens\System\PublicSurface\Token;

final class VerifyToken
{
    public static function execute(string $token) : ?array
    {
        return Token::verify($token);
    }
}
