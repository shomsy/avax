<?php

declare(strict_types=1);

namespace Avax\Components\Session\System\Flows\ReadSessionValue;

use Avax\Components\Session\System\Capabilities\Storage\SessionScope;

final class ResolveSessionKey
{
    public function resolve(string $key): string
    {
        return $key;
    }
}