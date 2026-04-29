<?php
declare(strict_types=1);
namespace Avax\Components\Identity\Security\System\Capabilities\Encrypt;
final class Encrypt { public static function execute(string $data): string { return \Avax\Components\Identity\Security\System\PublicSurface\Security::encrypt($data); } }
