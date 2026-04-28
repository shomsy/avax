<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\RegenerateSessionId;

final class RegenerateSessionId
{
    private string $currentId = '';

    public function __construct() {}

    public function handle() : string
    {
        $this->currentId = uniqid(prefix: 'session_', more_entropy: true);

        return $this->currentId;
    }

    public function current() : string
    {
        return $this->currentId;
    }
}
