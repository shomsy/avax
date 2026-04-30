<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Capabilities\Encrypt;

use Avax\Components\Identity\Security\System\PublicSurface\Security;

final class Decrypt
{
    public static function execute(string $data) : string
    {
        return Security::decrypt($data);
    }
}
