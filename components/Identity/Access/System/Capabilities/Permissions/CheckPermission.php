<?php
declare(strict_types=1);
namespace Avax\Components\Identity\Access\System\Capabilities\Permissions;
final class CheckPermission { public static function execute(string $p): bool { return \Avax\Components\Identity\Access\System\PublicSurface\Access::hasPermission($p); } }
