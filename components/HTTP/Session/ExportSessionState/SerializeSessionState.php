<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\ExportSessionState;

final class SerializeSessionState
{
    public function handle(array $data) : string
    {
        return serialize($data);
    }
}