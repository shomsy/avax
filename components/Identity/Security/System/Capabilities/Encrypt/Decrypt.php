<?php
declare(strict_types=1);
namespace Avax\Components\Identity\Security\System\Capabilities\Encrypt;
final class Decrypt { public static function execute(string $data): string { return \Avax\Components\Identity\Security\System\PublicSurface\Security::decrypt($data); } }
