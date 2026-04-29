<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Security;

final class SessionIdValidator
{
    public function isValid(string $id): bool
    {
        return preg_match('/^[a-zA-Z0-9,-]{32,128}$/', $id) === 1;
    }
}
