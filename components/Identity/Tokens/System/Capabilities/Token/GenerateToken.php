<?php
declare(strict_types=1);
namespace Avax\Components\Identity\Tokens\System\Capabilities\Token;
final class GenerateToken { public static function execute(array $claims = []): string { return \Avax\Components\Identity\Tokens\System\PublicSurface\Token::generate($claims); } }
