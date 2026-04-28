<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\ImportSessionState;

final class ValidateImportedSessionState
{
    public function handle(string $data) : bool
    {
        if (empty($data)) {
            return false;
        }

        $decoded = @unserialize($data);

        return is_array($decoded);
    }
}