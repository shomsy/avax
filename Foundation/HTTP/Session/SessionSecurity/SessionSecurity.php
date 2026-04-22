<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity;

use Avax\HTTP\Session\Core\Lifecycle\SessionEngine;
use Throwable;

final class SessionSecurity
{
    private SessionEngine $engine;

    public function __construct(SessionEngine $engine)
    {
        $this->engine = $engine;
    }

    public function verifyPolicy(string $action, array $context = []) : bool
    {
        $policies = $this->engine->policies();
        if ($policies === null) {
            return true;
        }

        try {
            $policies->enforce(data: $context + ['action' => $action]);
            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
