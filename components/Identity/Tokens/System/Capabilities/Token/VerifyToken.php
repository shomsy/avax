<?php
declare(strict_types=1);
namespace Avax\Components\Identity\Tokens\System\Capabilities\Token;
final class VerifyToken { public static function execute(string $token): ?array { return \Avax\Components\Identity\Tokens\System\PublicSurface\Token::verify($token); } }
