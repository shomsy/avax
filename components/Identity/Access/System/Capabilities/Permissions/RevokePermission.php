<?php
declare(strict_types=1);
namespace Avax\Components\Identity\Access\System\Capabilities\Permissions;
final class RevokePermission { public static function execute(string $p): void { \Avax\Components\Identity\Access\System\PublicSurface\Access::revoke($p); } }
