<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\ImportSessionState;

final class UnserializeSessionState
{
    public function handle(string $data) : array
    {
        return (array) unserialize($data);
    }
}